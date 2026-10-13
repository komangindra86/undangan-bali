<?php

namespace App\Support;

use App\Models\Invitation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * What every premium wedding design shows, worked out once: photos (or sample ones in a preview), the couple's
 * story from the invitation's moments, the guest named in the link, and the links for sharing and the calendar.
 */
class PremiumWedding
{
    private const DEMO_PHOTO = 'templates/bali-preview/hero-couple.jpg';

    private const DEMO_GALLERY = [
        'templates/bali-preview/gallery-pavilion.jpg',
        'templates/bali-preview/gallery-details.jpg',
        'templates/bali-preview/gallery-evening.jpg',
        'templates/bali-preview/hero-couple.jpg',
    ];

    public static function data(Invitation $invitation, bool $isPreview): array
    {
        $gallery = $isPreview ? self::DEMO_GALLERY : ($invitation->gallery_photos ?? []);
        $startTime = substr((string) $invitation->start_time, 0, 5);
        $endTime = $invitation->end_time ? substr((string) $invitation->end_time, 0, 5) : null;
        $couple = $invitation->groom_nickname.' & '.$invitation->bride_nickname;
        $guestInput = request()->query('to');
        $day = $invitation->event_date->format('Ymd');

        return [
            'isPreview' => $isPreview,
            'gallery' => $gallery,
            'groomPhoto' => $invitation->groom_photo ?: ($isPreview ? self::DEMO_PHOTO : null),
            'bridePhoto' => $invitation->bride_photo ?: ($isPreview ? self::DEMO_PHOTO : null),
            'heroPhoto' => $isPreview ? self::DEMO_PHOTO : ($gallery[0] ?? $invitation->groom_photo ?? $invitation->bride_photo),
            'demoPhoto' => self::DEMO_PHOTO,
            'musicPath' => $invitation->music_type === 'default' && $invitation->music ? $invitation->music->file_path : $invitation->music_file,
            'couple' => $couple,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'eventTarget' => $invitation->event_date->format('Y-m-d').'T'.($startTime ?: '00:00').':00+08:00',
            'guest' => is_scalar($guestInput) ? Str::limit(trim(strip_tags((string) $guestInput)), 80) : '',
            'shareText' => 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di '.$invitation->occasion_phrase.'. Buka undangan: '.url()->current(),
            'story' => self::story($invitation, $isPreview),
            'calendarUrl' => 'https://calendar.google.com/calendar/render?'.http_build_query([
                'action' => 'TEMPLATE',
                'text' => $invitation->event_type.' '.$couple,
                'dates' => $day.'T'.str_replace(':', '', $startTime ?: '00:00').'00/'.$day.'T'.str_replace(':', '', $endTime ?: ($startTime ?: '00:00')).'00',
                'ctz' => 'Asia/Makassar',
                'location' => trim($invitation->venue_name.', '.$invitation->venue_address, ', '),
            ]),
            'mediaUrl' => static function (string $path): string {
                $url = Storage::url($path);

                return parse_url($url, PHP_URL_PATH) ?: $url;
            },
        ];
    }

    private static function story(Invitation $invitation, bool $isPreview): array
    {
        $story = $invitation->exists
            ? $invitation->moments()->oldest('occurred_at')->oldest('id')->limit(6)->get()->map(fn ($moment) => [
                'when' => $moment->occurred_at?->translatedFormat('F Y'),
                'title' => $moment->title,
                'body' => $moment->body,
                'photo' => $moment->photo_path,
            ])->all()
            : [];

        return $story || ! $isPreview ? $story : [
            ['when' => 'Maret 2021', 'title' => 'Pertama bertemu', 'body' => 'Berkenalan saat ngayah di pura desa, lalu tak pernah benar-benar berpisah.', 'photo' => null],
            ['when' => 'Juni 2024', 'title' => 'Memadik', 'body' => 'Dua keluarga besar bertemu dan sepakat menyatukan langkah kami.', 'photo' => null],
            ['when' => 'Hari ini', 'title' => 'Pawiwahan', 'body' => 'Dengan restu keluarga, kami memulai hidup sebagai suami istri.', 'photo' => null],
        ];
    }
}
