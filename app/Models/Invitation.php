<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Invitation extends Model
{
    public const RETENTION_EXEMPT_SLUG_PREFIXES = ['preview-', 'demo-'];

    public const TYPES = ['wedding', 'birthday', 'megedong', 'pitung_dina', 'tigang_sasih', 'otonan', 'mepandes'];

    public const EVENT_TYPES = [
        'wedding' => ['Pawiwahan', 'Resepsi'],
        'birthday' => ['Ulang Tahun'],
        'megedong' => ['Megedong-gedongan'],
        'pitung_dina' => ['Abulan Pitung Dina'],
        'tigang_sasih' => ['Tigang Sasih'],
        'otonan' => ['Otonan'],
        'mepandes' => ['Mepandes'],
    ];

    /**
     * Ceremonies held for a baby or child. The father uses groom_*, the mother bride_* and the child child_*.
     * Adding a ceremony here (plus TYPES, EVENT_TYPES and its templates) is enough for the backend.
     *
     * child_named: the child already has a name by this ceremony, so the name is required and becomes the title.
     * age_days: the child's age at the ceremony, used for the template preview's sample birth date.
     * numbered: the ceremony repeats, so ceremony_number may say which one it is and replaces the fixed note.
     * meaning: one line explaining the ceremony, shown on the page beside the ceremony's emblem.
     * emblem: the ceremony's own symbol, a view under upacara-anak/ornaments named emblem-<value>.
     */
    public const CHILD_CEREMONIES = [
        'pitung_dina' => [
            'title' => 'Abulan Pitung Dina', 'note' => '42 Hari', 'child_named' => false, 'age_days' => 42, 'numbered' => false,
            'meaning' => 'Upacara penyucian bagi ibu dan buah hati saat si kecil berumur 42 hari, yaitu satu bulan Bali ditambah tujuh hari.',
            'emblem' => 'tirta',
        ],
        'tigang_sasih' => [
            'title' => 'Tigang Sasih', 'note' => '3 Bulanan', 'child_named' => true, 'age_days' => 105, 'numbered' => false,
            'meaning' => 'Upacara saat buah hati berumur tiga bulan Bali (105 hari), ketika si kecil untuk pertama kali menapak Ibu Pertiwi.',
            'emblem' => 'tapak',
        ],
        'otonan' => [
            'title' => 'Otonan', 'note' => null, 'child_named' => true, 'age_days' => 210, 'numbered' => true,
            'meaning' => 'Peringatan hari kelahiran menurut kalender pawukon Bali, yang datang setiap 210 hari.',
            'emblem' => 'tridatu',
        ],
    ];

    /**
     * Mepandes (potong gigi) is held for one or several people, listed in `participants`: each has full_name,
     * nickname, gender, age and photo. With family_mode "single" they share the parents in groom_* and bride_*;
     * with "multi" each participant carries father_name and mother_name, and host_name says who invites.
     */
    public const MEPANDES = [
        'title' => 'Mepandes', 'note' => 'Potong Gigi',
        'meaning' => 'Upacara potong gigi sebagai tanda beranjak dewasa, lambang mengendalikan enam musuh dalam diri (sad ripu).',
        'emblem' => 'sadripu',
    ];

    public const FAMILY_MODES = ['single', 'multi'];

    public const MAX_PARTICIPANTS = 10;

    public const PHOTO_COLUMNS = ['groom_photo', 'bride_photo', 'celebrant_photo', 'child_photo'];

    public const MUSIC_RIGHTS_TERMS_VERSION = '2026-09-01';

    protected $appends = ['public_url'];

    // The admin's takedown note is internal; the owner only learns that the invitation was taken down.
    protected $hidden = ['takedown_note', 'order_details', 'monitor_token'];

    protected $fillable = [
        'invitation_type',
        'celebrant_full_name',
        'celebrant_nickname',
        'celebrant_age',
        'celebrant_photo',
        'host_name',
        'event_title',
        'dress_code',
        'pregnancy_age',
        'child_order',
        'child_full_name',
        'child_nickname',
        'child_gender',
        'child_birth_date',
        'child_photo',
        'ceremony_number',
        'family_mode',
        'participants',
        'feed_consent_at',
        'user_id',
        'template_id',
        'music_id',
        'slug',
        'status',
        'groom_full_name',
        'groom_nickname',
        'groom_father_name',
        'groom_mother_name',
        'groom_child_order',
        'groom_photo',
        'bride_full_name',
        'bride_nickname',
        'bride_father_name',
        'bride_mother_name',
        'bride_child_order',
        'bride_photo',
        'gallery_photos',
        'opening_quote',
        'event_type',
        'event_date',
        'start_time',
        'end_time',
        'venue_name',
        'venue_address',
        'latitude',
        'longitude',
        'google_maps_url',
        'music_type',
        'music_file',
        'music_rights_accepted_at',
        'music_rights_terms_version',
        'published_at',
        'archived_at',
        'media_deleted_at',
        'is_hidden_from_feed',
        'moment_caption',
    ];

    protected function casts(): array
    {
        return [
            // Serialized as a plain day: an ISO timestamp is converted to UTC and lands on the previous day in WITA.
            'event_date' => 'date:Y-m-d',
            'child_birth_date' => 'date:Y-m-d',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'media_deleted_at' => 'datetime',
            'taken_down_at' => 'datetime',
            'feed_blocked_at' => 'datetime',
            'order_details' => 'array',
            'music_rights_accepted_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'gallery_photos' => 'array',
            'participants' => 'array',
            'is_hidden_from_feed' => 'boolean',
            'celebrant_age' => 'integer',
            'ceremony_number' => 'integer',
            'feed_consent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invitation $invitation) {
            if ($invitation->isBirthday()) {
                $invitation->is_hidden_from_feed = true;
                $invitation->feed_consent_at = null;
            }
        });
    }

    public function isBirthday(): bool
    {
        return $this->invitation_type === 'birthday';
    }

    public function isMegedong(): bool
    {
        return $this->invitation_type === 'megedong';
    }

    public function isChildCeremony(): bool
    {
        return isset(self::CHILD_CEREMONIES[$this->invitation_type]);
    }

    public function isMepandes(): bool
    {
        return $this->invitation_type === 'mepandes';
    }

    public function isMultiFamily(): bool
    {
        return $this->family_mode === 'multi';
    }

    /**
     * Title, note, meaning and emblem of the ceremony, for the types rendered by the ceremony pages.
     */
    private function ceremony(): ?array
    {
        return $this->isMepandes() ? self::MEPANDES : (self::CHILD_CEREMONIES[$this->invitation_type] ?? null);
    }

    /**
     * "Abulan Pitung Dina" or "Mepandes"; null for types that are not a ceremony.
     */
    public function getCeremonyTitleAttribute(): ?string
    {
        return $this->ceremony()['title'] ?? null;
    }

    /**
     * "42 Hari" for fixed ceremonies; "Otonan Pertama" or "Otonan ke-3" for numbered ones, null when no number is given.
     */
    public function getCeremonyNoteAttribute(): ?string
    {
        if (! $this->isNumberedCeremony()) {
            return $this->ceremony()['note'] ?? null;
        }

        return match (true) {
            ! $this->ceremony_number => null,
            $this->ceremony_number === 1 => $this->ceremony_title.' Pertama',
            default => $this->ceremony_title.' ke-'.$this->ceremony_number,
        };
    }

    public function getCeremonyMeaningAttribute(): ?string
    {
        return $this->ceremony()['meaning'] ?? null;
    }

    public function getCeremonyEmblemAttribute(): ?string
    {
        return $this->ceremony()['emblem'] ?? null;
    }

    /**
     * True for ceremonies that repeat through life (otonan), where ceremony_number applies.
     */
    public function isNumberedCeremony(): bool
    {
        return (bool) (self::CHILD_CEREMONIES[$this->invitation_type]['numbered'] ?? false);
    }

    /**
     * The ceremony inside a sentence: "abulan pitung dina (42 hari)", "otonan ke-3" or just "otonan".
     */
    public function getCeremonyPhraseAttribute(): ?string
    {
        if (! $this->ceremony()) {
            return null;
        }

        return mb_strtolower(match (true) {
            $this->isNumberedCeremony() => $this->ceremony_note ?: $this->ceremony_title,
            default => $this->ceremony_title.' ('.$this->ceremony_note.')',
        });
    }

    /**
     * True for ceremonies where the child is expected to have a name by then (tigang sasih onwards).
     */
    public function requiresChildName(): bool
    {
        return (bool) (self::CHILD_CEREMONIES[$this->invitation_type]['child_named'] ?? false);
    }

    private function titledByChildName(): bool
    {
        return $this->requiresChildName() && filled($this->child_nickname);
    }

    /**
     * "Putri dari Wira & Ayu": shown under the title when the title is the child's own name.
     */
    public function getChildRelationAttribute(): ?string
    {
        if ($this->isMepandes()) {
            return $this->participantRelation();
        }

        if (! $this->titledByChildName()) {
            return null;
        }

        return ($this->child_gender ? $this->child_label : 'Buah hati').' dari '.($this->groom_nickname ?: 'Ayah').' & '.($this->bride_nickname ?: 'Ibu');
    }

    /**
     * How the child is referred to when the name is left out: Putra, Putri or Buah Hati.
     */
    public function getChildLabelAttribute(): string
    {
        return ['putra' => 'Putra', 'putri' => 'Putri'][$this->child_gender] ?? 'Buah Hati';
    }

    /**
     * The people a mepandes is held for, in the order they were entered.
     */
    public function getParticipantListAttribute(): array
    {
        return array_values($this->participants ?? []);
    }

    /**
     * Their nicknames as a title: "Kirana", "Kirana & Bagus", "Kirana, Bagus & Dewi", then "Kirana, Bagus & 3 lainnya".
     */
    private function participantNames(): string
    {
        $names = array_values(array_filter(array_column($this->participant_list, 'nickname')));

        return match (true) {
            $names === [] => 'Yang Mepandes',
            count($names) === 1 => $names[0],
            count($names) <= 3 => implode(', ', array_slice($names, 0, -1)).' & '.end($names),
            default => $names[0].', '.$names[1].' & '.(count($names) - 2).' lainnya',
        };
    }

    /**
     * Under a mepandes title: "Putra-putri dari Wira & Ayu" for one family, the host's name for several.
     */
    private function participantRelation(): ?string
    {
        if ($this->isMultiFamily()) {
            return $this->host_name;
        }

        $genders = array_unique(array_column($this->participant_list, 'gender'));
        $label = match (true) {
            $genders === ['putra'] => 'Putra',
            $genders === ['putri'] => 'Putri',
            count($this->participant_list) > 1 => 'Putra-putri',
            default => 'Ananda',
        };

        return $label.' dari '.($this->groom_nickname ?: 'Ayah').' & '.($this->bride_nickname ?: 'Ibu');
    }

    /**
     * The participants with their photos removed, for when an archived invitation's media is cleaned up.
     */
    public function participantsWithoutPhotos(): ?array
    {
        return $this->participants === null
            ? null
            : array_map(fn (array $participant) => ['photo' => null] + $participant, $this->participant_list);
    }

    public function getDisplayNameAttribute(): string
    {
        return match (true) {
            $this->isBirthday() => $this->celebrant_nickname ?: 'Yang berulang tahun',
            $this->isMepandes() => $this->participantNames(),
            $this->titledByChildName() => $this->child_nickname,
            // Without a child name the title names the parents: "Putra Wira & Ayu".
            $this->isChildCeremony() => $this->child_label.' '.($this->groom_nickname ?: 'Ayah').' & '.($this->bride_nickname ?: 'Ibu'),
            // The ceremony centres on the expectant mother, so her name leads.
            $this->isMegedong() => ($this->bride_nickname ?: 'Calon Ibu').' & '.($this->groom_nickname ?: 'Calon Ayah'),
            default => ($this->groom_nickname ?: 'Mempelai').' & '.($this->bride_nickname ?: 'Pasangan'),
        };
    }

    public function getGiftLabelAttribute(): string
    {
        return match (true) {
            $this->isBirthday() => 'Kado Digital',
            $this->isMegedong(), $this->isChildCeremony(), $this->isMepandes() => 'Tanda Kasih',
            default => 'Wedding Gift',
        };
    }

    /**
     * How the occasion reads inside "kami mengundang untuk hadir di ...".
     */
    public function getOccasionPhraseAttribute(): string
    {
        return match (true) {
            $this->isBirthday() => 'perayaan ulang tahun '.$this->display_name,
            $this->isMegedong() => 'upacara megedong-gedongan kami',
            $this->isMepandes() => 'upacara '.$this->ceremony_phrase.' '.$this->display_name,
            $this->isChildCeremony() => 'upacara '.$this->ceremony_phrase.' buah hati kami'
                .($this->titledByChildName() ? ', '.$this->child_nickname : ''),
            default => 'acara pernikahan kami',
        };
    }

    /**
     * Small line above the names on the opening cover.
     */
    public function getCoverLabelAttribute(): string
    {
        return match (true) {
            $this->isBirthday() => 'Selamat datang di perayaan',
            $this->isMegedong() => 'Upacara Megedong-gedongan',
            $this->isChildCeremony(), $this->isMepandes() => 'Upacara '.$this->ceremony_title,
            default => 'The Wedding of',
        };
    }

    public function getGiftMessagePlaceholderAttribute(): string
    {
        return match (true) {
            $this->isBirthday() => 'Doa dan ucapan ulang tahun',
            $this->isMegedong() => 'Doa untuk ibu dan calon buah hati',
            $this->isChildCeremony() => 'Doa untuk si buah hati',
            $this->isMepandes() => 'Doa untuk yang mepandes',
            default => 'Doa dan ucapan untuk mempelai',
        };
    }

    /** Gift money came in or was requested for it, so its records must stay: such an invitation is never deleted. */
    public function hasGiftTransactions(): bool
    {
        return $this->weddingGifts()->whereIn('transaction_status', ['pending', 'paid', 'refunded'])->exists()
            || $this->payoutRequests()->exists();
    }

    /** Deletes the invitation for good, together with every file it uploaded. */
    public function deleteWithMedia(): void
    {
        $files = array_filter([
            ...$this->uploadedMediaPaths(),
            ...$this->moments()->pluck('photo_path')->all(),
        ]);

        $this->delete();
        Storage::disk('public')->delete($files);
    }

    /**
     * Every file this invitation uploaded: photos, gallery, participants' photos and custom music.
     */
    public function uploadedMediaPaths(): array
    {
        return array_values(array_filter([
            ...array_map(fn (string $column) => $this->{$column}, self::PHOTO_COLUMNS),
            $this->music_file,
            ...($this->gallery_photos ?? []),
            ...array_column($this->participant_list, 'photo'),
        ]));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(InvitationTemplate::class, 'template_id');
    }

    public function music()
    {
        return $this->belongsTo(Music::class);
    }

    public function views()
    {
        return $this->hasMany(InvitationView::class);
    }

    public function giftSetting()
    {
        return $this->hasOne(WeddingGiftSetting::class);
    }

    public function weddingGifts()
    {
        return $this->hasMany(WeddingGift::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(GiftPayoutRequest::class);
    }

    public function moments()
    {
        return $this->hasMany(InvitationMoment::class);
    }

    public function invitationRequests()
    {
        return $this->hasMany(InvitationRequest::class);
    }

    public function reports()
    {
        return $this->hasMany(ContentReport::class);
    }

    public function reactions()
    {
        return $this->hasMany(InvitationReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(InvitationComment::class);
    }

    public function socialNotifications()
    {
        return $this->hasMany(SocialNotification::class);
    }

    public function scopeWithoutRetentionExemptions($query)
    {
        foreach (self::RETENTION_EXEMPT_SLUG_PREFIXES as $prefix) {
            $query->where(function ($query) use ($prefix) {
                $query->whereNull('slug')
                    ->orWhere('slug', 'not like', $prefix.'%');
            });
        }

        return $query;
    }

    /** A readable public link, made unique with a short suffix when the plain one is taken. */
    public function uniqueSlug(): string
    {
        $base = Str::slug(match (true) {
            $this->isBirthday() => 'ulang tahun '.$this->celebrant_nickname,
            $this->isMegedong() => 'megedong gedongan '.$this->bride_nickname.' '.$this->groom_nickname,
            // A numbered ceremony carries its number, so each otonan of the same child gets a readable link.
            $this->isMepandes() => 'mepandes '.$this->display_name,
            $this->isChildCeremony() => ($this->isNumberedCeremony() && $this->ceremony_note ? $this->ceremony_note : $this->ceremony_title).' '.$this->display_name,
            default => 'undangan '.$this->groom_nickname.' '.$this->bride_nickname,
        });
        $slug = $base;

        while (self::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(5));
        }

        return $slug;
    }

    public function canReceiveGiftPayments(): bool
    {
        if ($this->isTakenDown()) {
            return false;
        }

        // Production gifts need an owner who can request a payout; demo pages stay display-only.
        return ! app()->environment('production')
            || ($this->user_id !== null && ! $this->isRetentionExempt());
    }

    /**
     * An admin took the invitation down (a fake or abusive one): its public page, gift page and feed entry are
     * off, and the owner cannot publish it again. Only an admin sets or clears this, so it is not fillable.
     */
    public function isTakenDown(): bool
    {
        return $this->taken_down_at !== null;
    }

    public function takeDown(string $note): void
    {
        $this->forceFill(['taken_down_at' => now(), 'takedown_note' => $note])->save();
    }

    public function isBlockedFromFeed(): bool
    {
        return $this->feed_blocked_at !== null || $this->isTakenDown();
    }

    public function scopeAllowedInFeed($query)
    {
        return $query->whereNull('taken_down_at')->whereNull('feed_blocked_at');
    }

    public function isRetentionExempt(): bool
    {
        foreach (self::RETENTION_EXEMPT_SLUG_PREFIXES as $prefix) {
            if (str_starts_with((string) $this->slug, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function getPublicUrlAttribute(): ?string
    {
        return in_array($this->status, ['published', 'archived'], true) && $this->slug
            ? route('invitations.public', $this->slug)
            : null;
    }
}
