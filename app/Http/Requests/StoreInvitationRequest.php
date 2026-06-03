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

    public function rules(): array
    {
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
            'groom_full_name' => ['nullable', 'string', 'max:255'],
            'groom_nickname' => ['nullable', 'string', 'max:100'],
            'groom_father_name' => ['nullable', 'string', 'max:255'],
            'groom_mother_name' => ['nullable', 'string', 'max:255'],
            'groom_child_order' => ['nullable', 'string', 'max:100'],
            'groom_photo' => ['nullable', 'image', 'max:4096'],
            'bride_full_name' => ['nullable', 'string', 'max:255'],
            'bride_nickname' => ['nullable', 'string', 'max:100'],
            'bride_father_name' => ['nullable', 'string', 'max:255'],
            'bride_mother_name' => ['nullable', 'string', 'max:255'],
            'bride_child_order' => ['nullable', 'string', 'max:100'],
            'bride_photo' => ['nullable', 'image', 'max:4096'],
            'gallery_photos' => ['nullable', 'array', 'max:6'],
            'gallery_photos.*' => ['image', 'max:4096'],
            'gallery_photos_changed' => ['nullable', 'boolean'],
            'opening_quote' => ['nullable', 'string', 'max:1000'],
            'event_type' => ['nullable', Rule::in(['Pawiwahan', 'Resepsi'])],
            'event_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_maps_url' => ['nullable', 'url', 'max:2048'],
            'music_type' => ['nullable', Rule::in(['none', 'default', 'upload'])],
            'music_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:10240'],
            'gift_data' => ['nullable', 'array'],
            'gift_data.is_active' => ['required_with:gift_data', 'boolean'],
            'gift_data.receiver_name' => ['nullable', 'required_if:gift_data.is_active,true,1', 'string', 'max:255'],
            'gift_data.receiver_note' => ['nullable', 'string', 'max:500'],
            'gift_data.minimum_amount' => ['required_with:gift_data', 'integer', 'min:10000', 'max:100000000'],
            'gift_data.show_amount_public' => ['required_with:gift_data', 'boolean'],
            'gift_data.allow_message' => ['required_with:gift_data', 'boolean'],
        ];
    }
}
