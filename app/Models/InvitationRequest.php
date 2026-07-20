<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationRequest extends Model
{
    protected $fillable = [
        'invitation_id',
        'requester_user_id',
        'requester_name',
        'requester_whatsapp',
        'status',
        'shared_at',
    ];

    protected function casts(): array
    {
        return ['shared_at' => 'datetime'];
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }
}
