<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'device_name',
        'app_version',
        'last_message_id',
        'last_error',
        'last_seen_at',
        'disabled_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
