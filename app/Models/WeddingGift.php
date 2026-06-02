<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingGift extends Model
{
    protected $fillable = [
        'invitation_id',
        'guest_name',
        'guest_phone',
        'message',
        'gift_amount',
        'service_fee',
        'total_amount',
        'order_id',
        'midtrans_transaction_id',
        'payment_type',
        'qr_string',
        'qr_image_url',
        'transaction_status',
        'fraud_status',
        'paid_at',
        'expired_at',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'gift_amount' => 'integer',
            'service_fee' => 'integer',
            'total_amount' => 'integer',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function fee()
    {
        return $this->hasOne(WeddingGiftFee::class);
    }

    public function payoutItems()
    {
        return $this->hasMany(GiftPayoutItem::class);
    }
}
