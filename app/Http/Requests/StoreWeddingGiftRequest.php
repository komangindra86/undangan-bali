<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeddingGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:30'],
            'gift_amount' => ['required', 'integer', 'min:1', 'max:100000000'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }
}
