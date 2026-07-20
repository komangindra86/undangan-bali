<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationComment extends Model
{
    protected $fillable = ['invitation_id', 'user_id', 'body', 'deleted_at'];

    protected function casts(): array
    {
        return ['deleted_at' => 'datetime'];
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
