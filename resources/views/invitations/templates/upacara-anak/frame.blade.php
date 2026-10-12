@php
    // Frame for the designs that have their own layout and Balinese ornaments. $design names the body under
    // designs/, $cover holds the opening cover's fallback illustration, accent, and the shade over a photo and
    // over the illustration (which is dark enough to need less).
    $isPreview = $isPreview ?? false;
    $childPhoto = $invitation->child_photo ? Storage::url($invitation->child_photo) : null;
    $musicPath = $invitation->music_type === 'default' ? $invitation->music?->file_path : ($invitation->music_type === 'upload' ? $invitation->music_file : null);
    $gallery = $invitation->gallery_photos ?? [];
    $coverPhoto = $childPhoto ?: (count($gallery) ? Storage::url($gallery[0]) : null);
    $ceremonyName = 'Upacara '.$invitation->ceremony_title;
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di '.$invitation->occasion_phrase.'. Buka undangan: '.url()->full();
    $parents = $invitation->groom_nickname.' & '.$invitation->bride_nickname;
    $greeting = $invitation->opening_quote ?: 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan '.mb_strtolower($ceremonyName).' bagi buah hati kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';
    $timeText = substr((string) $invitation->start_time, 0, 5).($invitation->end_time ? ' - '.substr($invitation->end_time, 0, 5) : '').' WITA';
    $childLine = $invitation->child_label.($invitation->child_order ? ' · '.$invitation->child_order : '').' dari pasangan';
    $galleryLabel = $invitation->isNumberedCeremony() ? 'Momen berharga' : 'Hari-hari pertama';
    $ornaments = 'invitations.templates.upacara-anak.ornaments.';
    $parts = 'invitations.templates.upacara-anak.parts.';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : $invitation->ceremony_title.' '.$invitation->display_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/upacara-bali.css') }}">
</head>
<body class="ub ub--{{ $design }}">
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $coverPhoto ?: asset('images/'.$cover[0]),
        'openingAccent' => $cover[1],
        'openingShade' => $cover[$coverPhoto ? 2 : 3],
        'openingHasMusic' => (bool) $musicPath,
    ])
    <main class="ub-page">
        @if($isPreview)
            <aside class="ub-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</aside>
        @endif
        @include('invitations.templates.upacara-anak.designs.'.$design)
    </main>
    @include($parts.'behaviour')
</body>
</html>
