<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MomentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cover = $this->groom_photo ?: $this->bride_photo;
        $setting = $this->relationLoaded('giftSetting') ? $this->giftSetting : null;

        return [
            'id' => $this->id,
            'groom_nickname' => $this->groom_nickname,
            'bride_nickname' => $this->bride_nickname,
            'names' => trim($this->groom_nickname.' & '.$this->bride_nickname),
            'caption' => $this->moment_caption,
            'cover_photo_url' => $cover ? url(Storage::disk('public')->url($cover)) : null,
            'template_name' => $this->template?->name,
            'published_at' => $this->published_at?->toISOString(),
            'gift_active' => (bool) ($setting?->is_active),
            'gift_url' => $setting?->is_active ? route('gifts.public', $this->slug) : null,
            'reactions' => [
                'like' => (int) ($this->like_reactions_count ?? 0),
                'love' => (int) ($this->love_reactions_count ?? 0),
            ],
            'comments_count' => (int) ($this->comments_count ?? 0),
        ];
    }
}
