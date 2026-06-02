<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftPayoutAccount extends Model
{
    protected $fillable = [
        'user_id',
        'bank_code',
        'bank_name',
        'account_number',
        'account_holder_name',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(GiftPayoutRequest::class, 'payout_account_id');
    }
}
