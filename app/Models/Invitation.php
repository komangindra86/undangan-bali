<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public const RETENTION_EXEMPT_SLUG_PREFIXES = ['preview-', 'demo-'];

    public const TYPES = ['wedding', 'birthday', 'megedong', 'pitung_dina', 'tigang_sasih', 'otonan'];

    public const EVENT_TYPES = [
        'wedding' => ['Pawiwahan', 'Resepsi'],
        'birthday' => ['Ulang Tahun'],
        'megedong' => ['Megedong-gedongan'],
        'pitung_dina' => ['Abulan Pitung Dina'],
        'tigang_sasih' => ['Tigang Sasih'],
        'otonan' => ['Otonan'],
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

    public const PHOTO_COLUMNS = ['groom_photo', 'bride_photo', 'celebrant_photo', 'child_photo'];

    public const MUSIC_RIGHTS_TERMS_VERSION = '2026-09-01';

    protected $appends = ['public_url'];

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
            'music_rights_accepted_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'gallery_photos' => 'array',
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

    /**
     * "Abulan Pitung Dina" for child ceremonies, null for other types.
     */
    public function getCeremonyTitleAttribute(): ?string
    {
        return self::CHILD_CEREMONIES[$this->invitation_type]['title'] ?? null;
    }

    /**
     * "42 Hari" for fixed ceremonies; "Otonan Pertama" or "Otonan ke-3" for numbered ones, null when no number is given.
     */
    public function getCeremonyNoteAttribute(): ?string
    {
        if (! $this->isNumberedCeremony()) {
            return self::CHILD_CEREMONIES[$this->invitation_type]['note'] ?? null;
        }

        return match (true) {
            ! $this->ceremony_number => null,
            $this->ceremony_number === 1 => $this->ceremony_title.' Pertama',
            default => $this->ceremony_title.' ke-'.$this->ceremony_number,
        };
    }

    public function getCeremonyMeaningAttribute(): ?string
    {
        return self::CHILD_CEREMONIES[$this->invitation_type]['meaning'] ?? null;
    }

    public function getCeremonyEmblemAttribute(): ?string
    {
        return self::CHILD_CEREMONIES[$this->invitation_type]['emblem'] ?? null;
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
        if (! $this->isChildCeremony()) {
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

    public function getDisplayNameAttribute(): string
    {
        return match (true) {
            $this->isBirthday() => $this->celebrant_nickname ?: 'Yang berulang tahun',
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
            $this->isMegedong(), $this->isChildCeremony() => 'Tanda Kasih',
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
            $this->isChildCeremony() => 'Upacara '.$this->ceremony_title,
            default => 'The Wedding of',
        };
    }

    public function getGiftMessagePlaceholderAttribute(): string
    {
        return match (true) {
            $this->isBirthday() => 'Doa dan ucapan ulang tahun',
            $this->isMegedong() => 'Doa untuk ibu dan calon buah hati',
            $this->isChildCeremony() => 'Doa untuk si buah hati',
            default => 'Doa dan ucapan untuk mempelai',
        };
    }

    /**
     * Every file this invitation uploaded: photos, gallery and custom music.
     */
    public function uploadedMediaPaths(): array
    {
        return array_values(array_filter([
            ...array_map(fn (string $column) => $this->{$column}, self::PHOTO_COLUMNS),
            $this->music_file,
            ...($this->gallery_photos ?? []),
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

    public function canReceiveGiftPayments(): bool
    {
        // Production gifts need an owner who can request a payout; demo pages stay display-only.
        return ! app()->environment('production')
            || ($this->user_id !== null && ! $this->isRetentionExempt());
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
