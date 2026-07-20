<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationReaction extends Model
{
    protected $fillable = ['invitation_id', 'user_id', 'type'];

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
