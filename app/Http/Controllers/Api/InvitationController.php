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
        abort_if($invitation->status === 'archived', 422, 'Undangan yang sudah diarsipkan tidak dapat diubah.');
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

        $personFields = match (true) {
            $invitation->isBirthday() => ['celebrant_full_name' => 80, 'celebrant_nickname' => 18],
            // With several families each participant names their own parents instead.
            $invitation->isMepandes() && $invitation->isMultiFamily() => [],
            default => ['groom_full_name' => 80, 'groom_nickname' => 18, 'bride_full_name' => 80, 'bride_nickname' => 18],
        };
        if ($invitation->requiresChildName()) {
            $personFields += ['child_full_name' => 80, 'child_nickname' => 18];
        }
        // Megedong-gedongan stores the expectant father in groom_* and the mother in bride_*.
        [$groomLabel, $brideLabel] = match (true) {
            $invitation->isMegedong() => ['calon ayah', 'calon ibu'],
            $invitation->isChildCeremony(), $invitation->isMepandes() => ['ayah', 'ibu'],
            default => ['mempelai pria', 'mempelai wanita'],
        };
        $personRules = [];
        foreach ($personFields as $field => $max) {
            $personRules[$field] = ['required', 'string', 'max:'.$max, 'regex:/^[\pL\s.\'-]+$/u'];
        }
        Validator::make($invitation->toArray(), [
            ...$personRules,
            ...($invitation->isMepandes() ? $this->mepandesPublishRules($invitation) : []),
            'template_id' => ['required', Rule::exists('invitation_templates', 'id')->where('is_active', true)->where('invitation_type', $invitation->invitation_type)],
            'event_type' => ['required', Rule::in(Invitation::EVENT_TYPES[$invitation->invitation_type] ?? Invitation::EVENT_TYPES['wedding'])],
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
            'groom_full_name' => 'nama lengkap '.$groomLabel,
            'groom_nickname' => 'nama panggilan '.$groomLabel,
            'bride_full_name' => 'nama lengkap '.$brideLabel,
            'bride_nickname' => 'nama panggilan '.$brideLabel,
            'child_full_name' => 'nama lengkap buah hati',
            'child_nickname' => 'nama panggilan buah hati',
            'host_name' => 'nama pengundang',
            'participants' => 'daftar yang mepandes',
            'participants.*.full_name' => 'nama lengkap yang mepandes',
            'participants.*.nickname' => 'nama panggilan yang mepandes',
            'participants.*.father_name' => 'nama ayah',
            'participants.*.mother_name' => 'nama ibu',
            'event_type' => 'jenis acara',
            'event_date' => 'tanggal acara',
            'start_time' => 'jam mulai',
            'venue_name' => 'nama tempat',
            'venue_address' => 'alamat lengkap',
        ])->validate();

        DB::transaction(function () use ($invitation) {
            // Editing a live invitation resets it to draft, so only a missing slug marks the first publish.
            $firstPublish = ! $invitation->slug;

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
            'share_text' => 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di '.$invitation->occasion_phrase.'. Buka undangan: '.route('invitations.public', $invitation->slug),
        ]);
    }

    public function destroy(Request $request, Invitation $invitation): JsonResponse
    {
        $this->ensureOwner($request, $invitation);
        abort_if(
            $invitation->weddingGifts()->whereIn('transaction_status', ['pending', 'paid', 'refunded'])->exists()
                || $invitation->payoutRequests()->exists(),
            422,
            'Undangan yang memiliki transaksi gift tidak dapat dihapus.'
        );

        $files = array_filter([
            ...$invitation->uploadedMediaPaths(),
            ...$invitation->moments()->pluck('photo_path')->all(),
        ]);

        $invitation->delete();
        Storage::disk('public')->delete($files);

        return response()->json(['message' => 'Undangan berhasil dihapus.']);
    }

    private function ensureOwner(Request $request, Invitation $invitation): void
    {
        abort_unless($invitation->user_id === $request->user()->id, 404);
    }

    private function draftAttributes(StoreInvitationRequest $request, ?Invitation $invitation = null): array
    {
        $data = $request->safe()->except([...Invitation::PHOTO_COLUMNS, 'gallery_photos', 'gallery_existing_paths', 'gallery_photos_changed', 'participant_photos', 'participant_photos_changed', 'music_file', 'music_rights_confirmed', 'gift_data']);
        $data['status'] = 'draft';
        if ($invitation && $invitation->status === 'published') {
            $data['published_at'] = null;
        }
        $data['music_type'] = $data['music_type'] ?? 'none';
        if ($data['music_type'] === 'upload' && $request->boolean('music_rights_confirmed')) {
            $data['music_rights_accepted_at'] = now();
            $data['music_rights_terms_version'] = Invitation::MUSIC_RIGHTS_TERMS_VERSION;
        }

        if ($data['invitation_type'] === 'mepandes') {
            $data['family_mode'] = $data['family_mode'] ?? 'single';
            $data['participants'] = $this->participants($request, $invitation, $data['participants'] ?? []);
        }

        $photoFields = match (true) {
            $data['invitation_type'] === 'birthday' => ['celebrant_photo'],
            $data['invitation_type'] === 'mepandes' => [],
            isset(Invitation::CHILD_CEREMONIES[$data['invitation_type']]) => ['child_photo'],
            default => ['groom_photo', 'bride_photo'],
        };
        foreach ($photoFields as $file) {
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

    /**
     * The mepandes participants as stored: their details plus the photo each one keeps or uploads.
     * Photos are only sent when they changed; otherwise each participant keeps the photo at the same position.
     */
    private function participants(StoreInvitationRequest $request, ?Invitation $invitation, array $rows): array
    {
        $current = $invitation?->participant_list ?? [];
        $currentPhotos = array_filter(array_column($current, 'photo'));
        $photosChanged = $request->boolean('participant_photos_changed');
        $participants = [];

        foreach ($rows as $key => $row) {
            $photo = match (true) {
                ! $photosChanged => $current[count($participants)]['photo'] ?? null,
                $request->hasFile('participant_photos.'.$key) => $request->file('participant_photos.'.$key)->store('invitations/photos', 'public'),
                in_array($row['photo_path'] ?? null, $currentPhotos, true) => $row['photo_path'],
                default => null,
            };

            $participants[] = [
                'full_name' => $row['full_name'] ?? null,
                'nickname' => $row['nickname'] ?? null,
                'gender' => $row['gender'] ?? null,
                'age' => isset($row['age']) ? (int) $row['age'] : null,
                'father_name' => $row['father_name'] ?? null,
                'mother_name' => $row['mother_name'] ?? null,
                'photo' => $photo,
            ];
        }

        Storage::disk('public')->delete(array_values(array_diff($currentPhotos, array_filter(array_column($participants, 'photo')))));

        return $participants;
    }

    private function syncGiftSetting(StoreInvitationRequest $request, Invitation $invitation): void
    {
        $giftData = $request->validated('gift_data');

        if (! is_array($giftData)) {
            return;
        }

        $invitation->giftSetting()->updateOrCreate([], [
            ...$giftData,
            'fee_type' => 'percent',
            'fee_value' => config('wedding_gift.payout_fee_percent'),
        ]);
    }

    /**
     * A mepandes needs at least one named participant; with several families also the host and each one's parents.
     */
    private function mepandesPublishRules(Invitation $invitation): array
    {
        $name = ['required', 'string', 'max:80', 'regex:/^[\pL\s.\'-]+$/u'];
        $rules = [
            'participants' => ['required', 'array', 'min:1', 'max:'.Invitation::MAX_PARTICIPANTS],
            'participants.*.full_name' => $name,
            'participants.*.nickname' => ['required', 'string', 'max:18', 'regex:/^[\pL\s.\'-]+$/u'],
        ];

        return $invitation->isMultiFamily() ? $rules + [
            'host_name' => ['required', 'string', 'max:80', 'not_regex:/[<>]/'],
            'participants.*.father_name' => $name,
            'participants.*.mother_name' => $name,
        ] : $rules;
    }

    private function uniqueSlug(Invitation $invitation): string
    {
        $base = Str::slug(match (true) {
            $invitation->isBirthday() => 'ulang tahun '.$invitation->celebrant_nickname,
            $invitation->isMegedong() => 'megedong gedongan '.$invitation->bride_nickname.' '.$invitation->groom_nickname,
            // A numbered ceremony carries its number, so each otonan of the same child gets a readable link.
            $invitation->isMepandes() => 'mepandes '.$invitation->display_name,
            $invitation->isChildCeremony() => ($invitation->isNumberedCeremony() && $invitation->ceremony_note ? $invitation->ceremony_note : $invitation->ceremony_title).' '.$invitation->display_name,
            default => 'undangan '.$invitation->groom_nickname.' '.$invitation->bride_nickname,
        });
        $slug = $base;

        while (Invitation::where('slug', $slug)->where('id', '!=', $invitation->id)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(5));
        }

        return $slug;
    }
}
