<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationTemplate extends Model
{
    protected $appends = ['preview_url'];

    protected $fillable = [
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

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'template_id');
    }

    public function getPreviewUrlAttribute(): string
    {
        return route('templates.preview', $this->slug);
    }
}
