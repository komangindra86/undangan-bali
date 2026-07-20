<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public const RETENTION_EXEMPT_SLUG_PREFIXES = ['preview-', 'demo-'];

    protected $appends = ['public_url'];

    protected $fillable = [
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
        'published_at',
        'archived_at',
        'media_deleted_at',
        'is_hidden_from_feed',
        'moment_caption',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'media_deleted_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'gallery_photos' => 'array',
            'is_hidden_from_feed' => 'boolean',
        ];
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
