<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $table = 'musics';

    protected $appends = ['audio_url', 'preview_url'];

    protected $hidden = ['license_evidence_path', 'audio_sha256', 'preview_sha256'];

    protected $fillable = [
        'title', 'file_path', 'is_active', 'catalog_key', 'artist', 'artist_url',
        'categories', 'source_url', 'license_code', 'license_url', 'attribution',
        'modifications', 'preview_file_path', 'duration_seconds', 'file_bytes',
        'audio_sha256', 'preview_sha256', 'license_evidence_path', 'license_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'categories' => 'array',
            'duration_seconds' => 'integer',
            'file_bytes' => 'integer',
            'license_verified_at' => 'datetime',
        ];
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function getAudioUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }

    public function getPreviewUrlAttribute(): string
    {
        return asset('storage/'.($this->preview_file_path ?: $this->file_path));
    }
}
