<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationMoment extends Model
{
    protected $fillable = [
        'invitation_id',
        'title',
        'body',
        'photo_path',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
