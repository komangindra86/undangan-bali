<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingGiftFee extends Model
{
    protected $fillable = [
        'wedding_gift_id',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function weddingGift()
    {
        return $this->belongsTo(WeddingGift::class);
    }
}
