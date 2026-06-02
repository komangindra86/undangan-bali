<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWeddingGiftSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'receiver_name' => ['nullable', 'required_if:is_active,true,1', 'string', 'max:255'],
            'receiver_note' => ['nullable', 'string', 'max:500'],
            'minimum_amount' => ['required', 'integer', 'min:10000', 'max:100000000'],
            'show_amount_public' => ['required', 'boolean'],
            'allow_message' => ['required', 'boolean'],
        ];
    }
}
