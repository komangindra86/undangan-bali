<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationView extends Model
{
    public $timestamps = false;

    protected $fillable = ['invitation_id', 'ip_address', 'user_agent', 'viewed_at'];

    protected function casts(): array
    {
        return ['viewed_at' => 'datetime'];
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
