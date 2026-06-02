<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftPayoutItem extends Model
{
    protected $fillable = [
        'payout_request_id',
        'wedding_gift_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function payoutRequest()
    {
        return $this->belongsTo(GiftPayoutRequest::class, 'payout_request_id');
    }

    public function weddingGift()
    {
        return $this->belongsTo(WeddingGift::class);
    }
}
