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
            ->with(['template', 'music'])
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

        Validator::make($invitation->toArray(), [
            'template_id' => ['required', 'exists:invitation_templates,id'],
            'groom_full_name' => ['required', 'string'],
            'groom_nickname' => ['required', 'string'],
            'bride_full_name' => ['required', 'string'],
            'bride_nickname' => ['required', 'string'],
            'event_type' => ['required', Rule::in(['Pawiwahan', 'Resepsi'])],
            'event_date' => ['required', 'date'],
            'start_time' => ['required'],
            'venue_name' => ['required', 'string'],
            'venue_address' => ['required', 'string'],
        ], [], [
            'groom_full_name' => 'nama lengkap mempelai pria',
            'groom_nickname' => 'nama panggilan mempelai pria',
            'bride_full_name' => 'nama lengkap mempelai wanita',
            'bride_nickname' => 'nama panggilan mempelai wanita',
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
            'share_text' => 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di acara pernikahan kami. Buka undangan: '.route('invitations.public', $invitation->slug),
        ]);
    }

    public function destroy(Request $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);

        foreach (['groom_photo', 'bride_photo', 'music_file'] as $file) {
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
        $data = $request->safe()->except(['groom_photo', 'bride_photo', 'gallery_photos', 'gallery_photos_changed', 'music_file', 'gift_data']);
        $data['status'] = 'draft';
        if ($invitation && $invitation->status === 'published') {
            $data['published_at'] = null;
        }
        $data['music_type'] = $data['music_type'] ?? 'none';

        foreach (['groom_photo', 'bride_photo'] as $file) {
            if ($request->hasFile($file)) {
                if ($invitation && $invitation->{$file}) {
                    Storage::disk('public')->delete($invitation->{$file});
                }
                $data[$file] = $request->file($file)->store('invitations/photos', 'public');
            }
        }

        if ($request->boolean('gallery_photos_changed')) {
            foreach ($invitation?->gallery_photos ?? [] as $file) {
                Storage::disk('public')->delete($file);
            }
            $data['gallery_photos'] = $request->hasFile('gallery_photos')
                ? collect($request->file('gallery_photos'))
                    ->map(fn ($file) => $file->store('invitations/gallery', 'public'))
                    ->values()
                    ->all()
                : null;
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
        $base = Str::slug('undangan '.$invitation->groom_nickname.' '.$invitation->bride_nickname);
        $slug = $base;

        while (Invitation::where('slug', $slug)->where('id', '!=', $invitation->id)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(5));
        }

        return $slug;
    }
}
