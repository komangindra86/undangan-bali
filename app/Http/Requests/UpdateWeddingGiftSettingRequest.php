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
            'receiver_name' => ['nullable', 'required_if:is_active,true,1', 'string', 'max:80', 'not_regex:/[<>]/'],
            'receiver_note' => ['nullable', 'string', 'max:300', 'not_regex:/[<>]/'],
            'minimum_amount' => ['required', 'integer', 'min:10000', 'max:100000000'],
            'show_amount_public' => ['required', 'boolean'],
            'allow_message' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'receiver_name' => 'nama penerima Wedding Gift',
            'receiver_note' => 'catatan Wedding Gift',
            'minimum_amount' => 'minimum nominal Wedding Gift',
        ];
    }

    public function messages(): array
    {
        return [
            '*.not_regex' => ':attribute tidak boleh mengandung karakter < atau >.',
        ];
    }
}
