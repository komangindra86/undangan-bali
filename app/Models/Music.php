<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $table = 'musics';

    protected $appends = ['audio_url'];

    protected $fillable = ['title', 'file_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function getAudioUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }
}
