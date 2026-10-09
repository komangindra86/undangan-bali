<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public const RETENTION_EXEMPT_SLUG_PREFIXES = ['preview-', 'demo-'];

    public const TYPES = ['wedding', 'birthday', 'megedong', 'pitung_dina'];

    public const EVENT_TYPES = [
        'wedding' => ['Pawiwahan', 'Resepsi'],
        'birthday' => ['Ulang Tahun'],
        'megedong' => ['Megedong-gedongan'],
        'pitung_dina' => ['Abulan Pitung Dina'],
    ];

    /**
     * Ceremonies held for a baby or child. The father uses groom_*, the mother bride_* and the child child_*.
     * Adding a ceremony here (plus TYPES, EVENT_TYPES and its templates) is enough for the backend.
     */
    public const CHILD_CEREMONIES = [
        'pitung_dina' => ['title' => 'Abulan Pitung Dina', 'note' => '42 Hari'],
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

    public function getCeremonyNoteAttribute(): ?string
    {
        return self::CHILD_CEREMONIES[$this->invitation_type]['note'] ?? null;
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
            // The child's name is optional, so the title names the parents: "Putra Wira & Ayu".
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
            $this->isChildCeremony() => 'upacara '.mb_strtolower($this->ceremony_title.' ('.$this->ceremony_note.')').' buah hati kami',
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
