<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialNotification extends Model
{
    protected $fillable = ['user_id', 'invitation_id', 'type', 'data', 'read_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
