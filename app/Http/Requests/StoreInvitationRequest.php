<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->replace($this->trimStrings($this->all()));

        $nested = [];
        $groups = ['groom_data', 'bride_data', 'event_data', 'location_data', 'music_data'];

        foreach ($groups as $group) {
            if (is_array($this->input($group))) {
                $nested = array_merge($nested, $this->input($group));
            }
        }

        $selectedTemplate = $this->input('selected_template');
        if (is_array($selectedTemplate)) {
            $nested['template_id'] = $selectedTemplate['id'] ?? null;
        } elseif ($selectedTemplate) {
            $nested['template_id'] = $selectedTemplate;
        }

        if (is_array($this->input('gift_data'))) {
            $giftData = $this->input('gift_data');
            foreach (['is_active', 'show_amount_public', 'allow_message'] as $field) {
                if (array_key_exists($field, $giftData)) {
                    $giftData[$field] = $this->normalizeBoolean($giftData[$field]);
                }
            }
            $this->merge(['gift_data' => $giftData]);
        }

        $this->merge(array_filter($nested, fn ($value) => $value !== null));
    }

    private function normalizeBoolean(mixed $value): mixed
    {
        if (is_bool($value) || $value === 1 || $value === 0) {
            return $value;
        }

        if (is_string($value)) {
            return match (strtolower($value)) {
                'true' => 1,
                'false' => 0,
                default => $value,
            };
        }

        return $value;
    }

    private function trimStrings(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->trimStrings($item), $value);
        }

        return is_string($value) ? trim(preg_replace('/\s+/', ' ', $value)) : $value;
    }

    public function rules(): array
    {
        $name = ['nullable', 'string', 'max:80', 'regex:/^[\pL\s.\'-]+$/u'];
        $nickname = ['nullable', 'string', 'max:18', 'regex:/^[\pL\s.\'-]+$/u'];
        $safeText = ['nullable', 'string', 'not_regex:/[<>]/'];

        return [
            'template_id' => [
                'required',
                Rule::exists('invitation_templates', 'id')->where('is_active', true),
            ],
            'music_id' => [
                'nullable',
                'required_if:music_type,default',
                Rule::exists('musics', 'id')->where('is_active', true),
            ],
            'groom_full_name' => $name,
            'groom_nickname' => $nickname,
            'groom_father_name' => $name,
            'groom_mother_name' => $name,
            'groom_child_order' => [...$safeText, 'max:50'],
            'groom_photo' => ['nullable', 'image', 'max:4096'],
            'bride_full_name' => $name,
            'bride_nickname' => $nickname,
            'bride_father_name' => $name,
            'bride_mother_name' => $name,
            'bride_child_order' => [...$safeText, 'max:50'],
            'bride_photo' => ['nullable', 'image', 'max:4096'],
            'gallery_photos' => ['nullable', 'array', 'max:6'],
            'gallery_photos.*' => ['image', 'max:4096'],
            'gallery_photos_changed' => ['nullable', 'boolean'],
            'opening_quote' => [...$safeText, 'max:300'],
            'event_type' => ['nullable', Rule::in(['Pawiwahan', 'Resepsi'])],
            'event_date' => ['nullable', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'venue_name' => [...$safeText, 'max:120'],
            'venue_address' => [...$safeText, 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_maps_url' => ['nullable', 'url', 'max:2048', 'regex:/^https:\/\/(www\.)?(google\.[a-z.]+\/maps|maps\.app\.goo\.gl|maps\.google\.[a-z.]+)/i'],
            'music_type' => ['nullable', Rule::in(['none', 'default', 'upload'])],
            'music_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:10240'],
            'gift_data' => ['nullable', 'array'],
            'gift_data.is_active' => ['required_with:gift_data', 'boolean'],
            'gift_data.receiver_name' => ['nullable', 'required_if:gift_data.is_active,true,1', 'string', 'max:80', 'not_regex:/[<>]/'],
            'gift_data.receiver_note' => ['nullable', 'string', 'max:300', 'not_regex:/[<>]/'],
            'gift_data.minimum_amount' => ['required_with:gift_data', 'integer', 'min:10000', 'max:100000000'],
            'gift_data.show_amount_public' => ['required_with:gift_data', 'boolean'],
            'gift_data.allow_message' => ['required_with:gift_data', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'template_id' => 'template',
            'groom_full_name' => 'nama lengkap mempelai pria',
            'groom_nickname' => 'nama panggilan mempelai pria',
            'groom_father_name' => 'nama ayah mempelai pria',
            'groom_mother_name' => 'nama ibu mempelai pria',
            'groom_child_order' => 'anak ke mempelai pria',
            'bride_full_name' => 'nama lengkap mempelai wanita',
            'bride_nickname' => 'nama panggilan mempelai wanita',
            'bride_father_name' => 'nama ayah mempelai wanita',
            'bride_mother_name' => 'nama ibu mempelai wanita',
            'bride_child_order' => 'anak ke mempelai wanita',
            'event_type' => 'jenis acara',
            'event_date' => 'tanggal acara',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'venue_name' => 'nama tempat',
            'venue_address' => 'alamat lengkap',
            'google_maps_url' => 'link Google Maps',
            'opening_quote' => 'kutipan pembuka',
            'gift_data.receiver_name' => 'nama penerima Wedding Gift',
            'gift_data.receiver_note' => 'catatan Wedding Gift',
            'gift_data.minimum_amount' => 'minimum nominal Wedding Gift',
        ];
    }

    public function messages(): array
    {
        return [
            '*.regex' => ':attribute memiliki format yang tidak valid.',
            '*.not_regex' => ':attribute tidak boleh mengandung karakter < atau >.',
            'event_date.after_or_equal' => 'tanggal acara tidak boleh sebelum hari ini.',
            'google_maps_url.regex' => 'link Google Maps harus berupa link Google Maps yang valid.',
            'end_time.after' => 'jam selesai harus setelah jam mulai.',
        ];
    }
}
