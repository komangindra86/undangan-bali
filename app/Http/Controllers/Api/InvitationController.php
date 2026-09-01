<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvitationRequest;
use App\Models\Invitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invitations = $request->user()->invitations()
            ->with(['template', 'music', 'giftSetting'])
            ->latest()
            ->paginate(15);

        return response()->json($invitations);
    }

    public function store(StoreInvitationRequest $request): JsonResponse
    {
        $invitation = $request->user()->invitations()->create(
            $this->draftAttributes($request)
        );
        $this->syncGiftSetting($request, $invitation);

        return response()->json([
            'message' => 'Draft berhasil disimpan.',
            'data' => $invitation->load(['template', 'music', 'giftSetting']),
        ], 201);
    }

    public function syncLocalDraft(StoreInvitationRequest $request): JsonResponse
    {
        $invitation = $request->user()->invitations()->create(
            $this->draftAttributes($request)
        );
        $this->syncGiftSetting($request, $invitation);

        return response()->json([
            'message' => 'Draft lokal berhasil disinkronkan.',
            'data' => $invitation->load(['template', 'music', 'giftSetting']),
        ], 201);
    }

    public function show(Request $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);

        return response()->json(['data' => $invitation->load(['template', 'music', 'giftSetting'])]);
    }

    public function update(StoreInvitationRequest $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);
        $invitation->update($this->draftAttributes($request, $invitation));
        $this->syncGiftSetting($request, $invitation);

        return response()->json([
            'message' => 'Draft berhasil diperbarui.',
            'data' => $invitation->fresh()->load(['template', 'music', 'giftSetting']),
        ]);
    }

    public function publish(Request $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);

        $personFields = $invitation->isBirthday()
            ? ['celebrant_full_name' => 80, 'celebrant_nickname' => 18]
            : ['groom_full_name' => 80, 'groom_nickname' => 18, 'bride_full_name' => 80, 'bride_nickname' => 18];
        $personRules = [];
        foreach ($personFields as $field => $max) {
            $personRules[$field] = ['required', 'string', 'max:'.$max, 'regex:/^[\pL\s.\'-]+$/u'];
        }
        Validator::make($invitation->toArray(), [
            ...$personRules,
            'template_id' => ['required', Rule::exists('invitation_templates', 'id')->where('is_active', true)->where('invitation_type', $invitation->invitation_type)],
            'event_type' => ['required', Rule::in($invitation->isBirthday() ? ['Ulang Tahun'] : ['Pawiwahan', 'Resepsi'])],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required'],
            'venue_name' => ['required', 'string', 'max:120', 'not_regex:/[<>]/'],
            'venue_address' => ['required', 'string', 'max:1000', 'not_regex:/[<>]/'],
        ], [
            '*.regex' => ':attribute memiliki format yang tidak valid.',
            '*.not_regex' => ':attribute tidak boleh mengandung karakter < atau >.',
            'event_date.after_or_equal' => 'tanggal acara tidak boleh sebelum hari ini.',
        ], [
            'celebrant_full_name' => 'nama lengkap yang berulang tahun',
            'celebrant_nickname' => 'nama panggilan yang berulang tahun',
            'groom_full_name' => 'nama lengkap mempelai pria',
            'groom_nickname' => 'nama panggilan mempelai pria',
            'bride_full_name' => 'nama lengkap mempelai wanita',
            'bride_nickname' => 'nama panggilan mempelai wanita',
            'event_type' => 'jenis acara',
            'event_date' => 'tanggal acara',
            'start_time' => 'jam mulai',
            'venue_name' => 'nama tempat',
            'venue_address' => 'alamat lengkap',
        ])->validate();

        DB::transaction(function () use ($invitation) {
            $firstPublish = $invitation->status !== 'published';

            $invitation->update([
                'slug' => $invitation->slug ?: $this->uniqueSlug($invitation),
                'status' => 'published',
                'published_at' => $invitation->published_at ?: now(),
            ]);

            if ($firstPublish) {
                $invitation->template()->increment('usage_count');
            }
        });

        $invitation->refresh();

        return response()->json([
            'message' => 'Undangan berhasil dipublish.',
            'data' => $invitation->load(['template', 'music', 'giftSetting']),
            'public_url' => route('invitations.public', $invitation->slug),
            'share_text' => 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di '.($invitation->isBirthday() ? 'perayaan ulang tahun '.$invitation->display_name : 'acara pernikahan kami').'. Buka undangan: '.route('invitations.public', $invitation->slug),
        ]);
    }

    public function destroy(Request $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);

        foreach (['groom_photo', 'bride_photo', 'celebrant_photo', 'music_file'] as $file) {
            if ($invitation->{$file}) {
                Storage::disk('public')->delete($invitation->{$file});
            }
        }
        foreach ($invitation->gallery_photos ?? [] as $file) {
            Storage::disk('public')->delete($file);
        }

        $invitation->delete();

        return response()->json(['message' => 'Undangan berhasil dihapus.']);
    }

    private function ensureOwner(Request $request, Invitation $invitation): void
    {
        abort_unless($invitation->user_id === $request->user()->id, 404);
    }

    private function draftAttributes(StoreInvitationRequest $request, ?Invitation $invitation = null): array
    {
        $data = $request->safe()->except(['groom_photo', 'bride_photo', 'celebrant_photo', 'gallery_photos', 'gallery_existing_paths', 'gallery_photos_changed', 'music_file', 'music_rights_confirmed', 'gift_data']);
        $data['status'] = 'draft';
        if ($invitation && $invitation->status === 'published') {
            $data['published_at'] = null;
        }
        $data['music_type'] = $data['music_type'] ?? 'none';
        if ($data['music_type'] === 'upload' && $request->boolean('music_rights_confirmed')) {
            $data['music_rights_accepted_at'] = now();
            $data['music_rights_terms_version'] = Invitation::MUSIC_RIGHTS_TERMS_VERSION;
        }

        foreach (($data['invitation_type'] === 'birthday' ? ['celebrant_photo'] : ['groom_photo', 'bride_photo']) as $file) {
            if ($request->hasFile($file)) {
                if ($invitation && $invitation->{$file}) {
                    Storage::disk('public')->delete($invitation->{$file});
                }
                $data[$file] = $request->file($file)->store('invitations/photos', 'public');
            }
        }

        if ($request->boolean('gallery_photos_changed')) {
            $currentGallery = $invitation?->gallery_photos ?? [];
            $retainedGallery = collect($request->input('gallery_existing_paths', []))
                ->filter(fn ($path) => in_array($path, $currentGallery, true))
                ->unique()
                ->values();

            foreach (array_diff($currentGallery, $retainedGallery->all()) as $file) {
                Storage::disk('public')->delete($file);
            }

            $newGallery = $request->hasFile('gallery_photos')
                ? collect($request->file('gallery_photos'))->map(fn ($file) => $file->store('invitations/gallery', 'public'))
                : collect();

            $gallery = $retainedGallery->concat($newGallery)->take(6)->values()->all();
            $data['gallery_photos'] = $gallery ?: null;
        }

        if ($request->hasFile('music_file')) {
            if ($invitation && $invitation->music_file) {
                Storage::disk('public')->delete($invitation->music_file);
            }
            $data['music_type'] = 'upload';
            $data['music_file'] = $request->file('music_file')->store('invitations/musics', 'public');
            $data['music_id'] = null;
        }

        if (($data['music_type'] ?? null) !== 'upload' && $invitation?->music_file) {
            Storage::disk('public')->delete($invitation->music_file);
            $data['music_file'] = null;
        }

        if ($data['music_type'] !== 'default') {
            $data['music_id'] = null;
        }

        return $data;
    }

    private function syncGiftSetting(StoreInvitationRequest $request, Invitation $invitation): void
    {
        $giftData = $request->validated('gift_data');

        if (! is_array($giftData)) {
            return;
        }

        $invitation->giftSetting()->updateOrCreate([], [
            ...$giftData,
            'fee_type' => config('wedding_gift.fee.type'),
            'fee_value' => config('wedding_gift.fee.value'),
        ]);
    }

    private function uniqueSlug(Invitation $invitation): string
    {
        $base = Str::slug($invitation->isBirthday()
            ? 'ulang tahun '.$invitation->celebrant_nickname
            : 'undangan '.$invitation->groom_nickname.' '.$invitation->bride_nickname);
        $slug = $base;

        while (Invitation::where('slug', $slug)->where('id', '!=', $invitation->id)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(5));
        }

        return $slug;
    }
}
