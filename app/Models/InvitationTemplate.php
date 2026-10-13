<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationTemplate extends Model
{
    protected $appends = ['preview_url'];

    protected $fillable = [
        'invitation_type',
        'name',
        'slug',
        'thumbnail',
        'preview_image',
        'blade_view',
        'is_active',
        'is_premium',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
        ];
    }

    /**
     * Premium templates are kept for custom orders: they stay out of everyone's catalogue except the accounts
     * an admin allowed. Their preview link works for anyone, so a design can be shown to a customer first.
     */
    public function scopeAvailableTo($query, ?User $user)
    {
        return $user?->can_use_premium_templates ? $query : $query->where('is_premium', false);
    }

    public function isAvailableTo(?User $user): bool
    {
        return ! $this->is_premium || (bool) $user?->can_use_premium_templates;
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'template_id');
    }

    public function getPreviewUrlAttribute(): string
    {
        return route('templates.preview', $this->slug);
    }
}
