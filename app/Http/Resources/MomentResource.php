<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MomentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $setting = $this->relationLoaded('giftSetting') ? $this->giftSetting : null;
        $groomNickname = $this->safeDisplayText($this->groom_nickname) ?: 'Mempelai';
        $brideNickname = $this->safeDisplayText($this->bride_nickname) ?: 'Pasangan';
        $photoUrls = $this->photoUrls();

        return [
            'id' => $this->id,
            'groom_nickname' => $groomNickname,
            'bride_nickname' => $brideNickname,
            'names' => $groomNickname.' & '.$brideNickname,
            'caption' => $this->safeDisplayText($this->moment_caption),
            'cover_photo_url' => $photoUrls->first(),
            'photo_urls' => $photoUrls,
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

    private function photoUrls()
    {
        $momentPhotos = $this->relationLoaded('moments')
            ? $this->moments->pluck('photo_path')
            : collect();

        return collect($this->gallery_photos ?? [])
            ->merge($momentPhotos)
            ->merge([$this->groom_photo, $this->bride_photo])
            ->filter()
            ->unique()
            ->map(fn (string $path) => url(Storage::disk('public')->url($path)))
            ->values();
    }

    private function safeDisplayText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $withoutExecutableTags = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $value);
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($withoutExecutableTags)));

        return $plainText !== '' ? $plainText : null;
    }
}
