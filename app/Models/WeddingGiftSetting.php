<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingGiftSetting extends Model
{
    protected $fillable = [
        'invitation_id',
        'is_active',
        'receiver_name',
        'receiver_note',
        'fee_type',
        'fee_value',
        'minimum_amount',
        'show_amount_public',
        'allow_message',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'fee_value' => 'decimal:2',
            'minimum_amount' => 'integer',
            'show_amount_public' => 'boolean',
            'allow_message' => 'boolean',
        ];
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
