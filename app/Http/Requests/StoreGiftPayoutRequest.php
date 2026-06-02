<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGiftPayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payout_account_id' => ['required', 'integer', 'exists:gift_payout_accounts,id'],
            'amount' => ['required', 'integer', 'min:'.config('wedding_gift.payout_minimum_amount')],
        ];
    }
}
