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
            'guest_name' => ['required', 'string', 'max:80', 'not_regex:/[<>]/'],
            'guest_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\s-]+$/'],
            'gift_amount' => ['required', 'integer', 'min:1', 'max:100000000'],
            'message' => ['nullable', 'string', 'max:300', 'not_regex:/[<>]/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'guest_name' => 'nama tamu',
            'guest_phone' => 'nomor HP',
            'gift_amount' => 'nominal gift',
            'message' => 'ucapan',
        ];
    }

    public function messages(): array
    {
        return [
            '*.not_regex' => ':attribute tidak boleh mengandung karakter < atau >.',
            'guest_phone.regex' => 'nomor HP hanya boleh berisi angka, spasi, tanda +, atau tanda hubung.',
        ];
    }
}
