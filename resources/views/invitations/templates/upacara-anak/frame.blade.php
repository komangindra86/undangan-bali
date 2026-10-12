@php
    // Frame for every child ceremony design: $design names the body under designs/ and the cover under covers/.
    $isPreview = $isPreview ?? false;
    $childPhoto = $invitation->child_photo ? Storage::url($invitation->child_photo) : null;
    $musicPath = $invitation->music_type === 'default' ? $invitation->music?->file_path : ($invitation->music_type === 'upload' ? $invitation->music_file : null);
    $gallery = $invitation->gallery_photos ?? [];
    $ceremonyName = 'Upacara '.$invitation->ceremony_title;
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di '.$invitation->occasion_phrase.'. Buka undangan: '.url()->full();
    $parents = $invitation->groom_nickname.' & '.$invitation->bride_nickname;
    $greeting = $invitation->opening_quote ?: 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan '.mb_strtolower($ceremonyName).' bagi buah hati kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';
    $timeText = substr((string) $invitation->start_time, 0, 5).($invitation->end_time ? ' - '.substr($invitation->end_time, 0, 5) : '').' WITA';
    $childLine = $invitation->child_label.($invitation->child_order ? ' · '.$invitation->child_order : '').' dari pasangan';
    $galleryLabel = $invitation->isNumberedCeremony() ? 'Momen berharga' : 'Hari-hari pertama';
    $ornaments = 'invitations.templates.upacara-anak.ornaments.';
    $parts = 'invitations.templates.upacara-anak.parts.';

    // Opening cover colours: accent and shade over a photo, then accent, text and shade over the design's own
    // illustration, which is shown when the invitation has no photo and is light enough to need dark text.
    [$photoAccent, $photoShade, $artAccent, $artText, $artShade] = [
        'rare-kencana' => ['#e6c98a', 'rgba(52, 35, 18, .62)', '#7d5415', '#4a3421', 'rgba(255, 250, 238, .14)'],
        'sekar-jepun' => ['#f6d36b', 'rgba(74, 58, 22, .6)', '#8f6400', '#55482f', 'rgba(255, 253, 244, .2)'],
        'langit-kumara' => ['#bfe4e1', 'rgba(20, 52, 60, .62)', '#1a6169', '#23434a', 'rgba(240, 250, 253, .14)'],
        'nyambutin-kencana' => ['#f3c9a8', 'rgba(70, 26, 14, .62)', '#963b23', '#5a2a1c', 'rgba(255, 244, 234, .16)'],
        'tunjung-petak' => ['#cfe8d3', 'rgba(24, 52, 38, .62)', '#2f6a48', '#2f4a3a', 'rgba(247, 251, 245, .2)'],
        'surya-alit' => ['#ffd08a', 'rgba(84, 42, 14, .6)', '#a34a12', '#5a3520', 'rgba(255, 246, 236, .14)'],
        'oton-kencana' => ['#e9c877', 'rgba(52, 36, 14, .56)', '#7a5210', '#4b3a26', 'rgba(255, 250, 238, .16)'],
        'sekar-cempaka' => ['#f8cdd7', 'rgba(72, 28, 46, .56)', '#a8456a', '#55323d', 'rgba(255, 246, 247, .16)'],
        'purnama-alit' => ['#f6e6ad', 'rgba(22, 22, 56, .58)', '#f6e6ad', '#fffaf0', 'rgba(22, 22, 56, .2)'],
    ][$design];
    $coverPhoto = $childPhoto ?: (count($gallery) ? Storage::url($gallery[0]) : null);
    $coverArt = $coverPhoto ? null : 'data:image/svg+xml;base64,'.base64_encode(view('invitations.templates.upacara-anak.covers.'.$design, compact('ornaments', 'parts'))->render());
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
<body class="ub ub--{{ $design }}{{ $coverPhoto ? '' : ' ub-cover-art' }}">
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $coverPhoto ?: $coverArt,
        'openingAccent' => $coverPhoto ? $photoAccent : $artAccent,
        'openingShade' => $coverPhoto ? $photoShade : $artShade,
        'openingText' => $coverPhoto ? '#fff8e8' : $artText,
        'openingHasMusic' => (bool) $musicPath,
    ])
    @include($parts.'defs')
    <main class="ub-page">
        @if($isPreview)
            <aside class="ub-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</aside>
        @endif
        @include('invitations.templates.upacara-anak.designs.'.$design)
    </main>
    @include($parts.'behaviour')
</body>
</html>
