<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDeletionRequest extends Model
{
    protected $fillable = ['email', 'note', 'ip_address', 'status', 'handled_at'];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    /** The live account with the requested email, if there is one. */
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
