<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftPayoutRequest extends Model
{
    public const RESERVED_STATUSES = ['pending', 'approved', 'processing', 'paid'];

    protected $fillable = [
        'user_id',
        'invitation_id',
        'payout_account_id',
        'bank_code',
        'bank_name',
        'account_number',
        'account_holder_name',
        'amount',
        'status',
        'admin_note',
        'transfer_reference',
        'requested_at',
        'processed_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function payoutAccount()
    {
        return $this->belongsTo(GiftPayoutAccount::class, 'payout_account_id');
    }

    public function items()
    {
        return $this->hasMany(GiftPayoutItem::class, 'payout_request_id');
    }
}
