{{-- Wiwaha Agung (premium): the couple's photo in a kori arch between two penjor, a songket date band, and their story. --}}
@php
    $isPreview = $isPreview ?? false;
    $ornaments = 'invitations.templates.upacara-anak.ornaments.';
    $demoPhoto = 'templates/bali-preview/hero-couple.jpg';
    $demoGallery = [
        'templates/bali-preview/gallery-pavilion.jpg',
        'templates/bali-preview/gallery-details.jpg',
        'templates/bali-preview/gallery-evening.jpg',
    ];
    $mediaUrl = static function (string $path): string {
        $url = Storage::url($path);

        return parse_url($url, PHP_URL_PATH) ?: $url;
    };
    $gallery = $isPreview ? $demoGallery : ($invitation->gallery_photos ?? []);
    $groomPhoto = $invitation->groom_photo ?: ($isPreview ? $demoPhoto : null);
    $bridePhoto = $invitation->bride_photo ?: ($isPreview ? $demoPhoto : null);
    $heroPhoto = $isPreview ? $demoPhoto : ($gallery[0] ?? $invitation->groom_photo ?? $invitation->bride_photo);
    $musicPath = $invitation->music_type === 'default' && $invitation->music ? $invitation->music->file_path : $invitation->music_file;
    $couple = $invitation->groom_nickname.' & '.$invitation->bride_nickname;
    $startTime = substr((string) $invitation->start_time, 0, 5);
    $endTime = $invitation->end_time ? substr((string) $invitation->end_time, 0, 5) : null;
    $eventTarget = $invitation->event_date->format('Y-m-d').'T'.($startTime ?: '00:00').':00+08:00';
    $guestInput = request()->query('to');
    $guest = is_scalar($guestInput) ? Illuminate\Support\Str::limit(trim(strip_tags((string) $guestInput)), 80) : '';
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di '.$invitation->occasion_phrase.'. Buka undangan: '.url()->current();

    // The couple's own story, written as moments in the app; a preview shows three sample chapters.
    $story = $invitation->exists
        ? $invitation->moments()->oldest('occurred_at')->oldest('id')->limit(6)->get()->map(fn ($moment) => [
            'when' => $moment->occurred_at?->translatedFormat('F Y'),
            'title' => $moment->title,
            'body' => $moment->body,
            'photo' => $moment->photo_path,
        ])->all()
        : [];
    if ($isPreview && ! $story) {
        $story = [
            ['when' => 'Maret 2021', 'title' => 'Pertama bertemu', 'body' => 'Berkenalan saat ngayah di pura desa, lalu tak pernah benar-benar berpisah.', 'photo' => null],
            ['when' => 'Juni 2024', 'title' => 'Memadik', 'body' => 'Dua keluarga besar bertemu dan sepakat menyatukan langkah kami.', 'photo' => null],
            ['when' => 'Agustus 2026', 'title' => 'Pawiwahan', 'body' => 'Dengan restu keluarga, kami memulai hidup sebagai suami istri.', 'photo' => null],
        ];
    }

    $calendarStart = $invitation->event_date->format('Ymd').'T'.str_replace(':', '', $startTime ?: '00:00').'00';
    $calendarEnd = $invitation->event_date->format('Ymd').'T'.str_replace(':', '', $endTime ?: ($startTime ?: '00:00')).'00';
    $calendarUrl = 'https://calendar.google.com/calendar/render?'.http_build_query([
        'action' => 'TEMPLATE',
        'text' => $invitation->event_type.' '.$couple,
        'dates' => $calendarStart.'/'.$calendarEnd,
        'ctz' => 'Asia/Makassar',
        'location' => trim($invitation->venue_name.', '.$invitation->venue_address, ', '),
    ]);
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#fbf1dc">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : 'Undangan '.$couple }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Parisienne&display=swap" rel="stylesheet">
    <script>document.documentElement.classList.add('js');</script>
    <style>
        :root {
            --wa-ivory: #fdf8ee;
            --wa-cream: #f6ead2;
            --wa-sand: #ead9b6;
            --wa-gold: #b3863a;
            --wa-gold-soft: #d9bb78;
            --wa-maroon: #7c3040;
            --wa-ink: #3d2c20;
            --wa-muted: #7b6a56;
            --orn: #b3863a;
            --wa-serif: 'Cormorant Garamond', Georgia, serif;
            --wa-script: Parisienne, 'Brush Script MT', cursive;
        }
        * { box-sizing: border-box; }
        html { background: #e9dcc0; scroll-behavior: smooth; }
        html, body { min-width: 0; overflow-x: hidden; }
        body {
            background: radial-gradient(circle at 20% 0%, #fff7e2, transparent 34rem), linear-gradient(160deg, #eadbb9, #f6eedb 48%, #dcc79c);
            color: var(--wa-ink);
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif;
            margin: 0;
        }
        img { display: block; max-width: 100%; }
        .ub-defs { position: absolute; }
        .orn { display: block; height: auto; overflow: visible; }
        .orn-line { fill: none; stroke: var(--orn); stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.1; }
        .orn-dot { fill: var(--orn); }
        .orn.is-mirrored { transform: scaleX(-1); }
        .orn-patra { margin: 0 auto 22px; max-width: 72%; width: 220px; }

        .wa-page {
            background: var(--wa-ivory);
            box-shadow: 0 30px 80px rgba(74, 52, 20, .2);
            margin: 0 auto;
            max-width: 520px;
            overflow: hidden;
            position: relative;
        }
        .wa-demo {
            background: #fff4d6;
            border-bottom: 1px solid #ecd9a8;
            color: #6c5320;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 9px 16px;
            text-align: center;
        }
        .wa-eyebrow { color: var(--wa-gold); font-size: 11px; font-weight: 700; letter-spacing: .34em; margin: 0; text-transform: uppercase; }
        .wa-script { color: var(--wa-maroon); font-family: var(--wa-script); font-size: 34px; line-height: 1.1; margin: 0; }
        .wa-section { padding: 68px 26px; position: relative; text-align: center; }
        .wa-section h2 { font-family: var(--wa-serif); font-size: 34px; font-weight: 600; line-height: 1.12; margin: 10px 0 0; }
        .wa-button {
            align-items: center;
            background: linear-gradient(135deg, #c9a253, #a87a2c);
            border: 0;
            border-radius: 999px;
            box-shadow: 0 10px 24px rgba(150, 108, 34, .28);
            color: #fffaf0;
            cursor: pointer;
            display: inline-flex;
            font: 700 13px Inter, system-ui, sans-serif;
            justify-content: center;
            letter-spacing: .06em;
            min-height: 48px;
            padding: 12px 26px;
            text-decoration: none;
        }
        .wa-button.is-quiet { background: transparent; border: 1px solid var(--wa-gold-soft); box-shadow: none; color: var(--wa-gold); }

        /* Hero: dawn sky, two penjor bending over the arch that holds the couple's photo. */
        .wa-hero {
            background:
                radial-gradient(circle at 50% 44%, rgba(255, 233, 178, .9), transparent 46%),
                linear-gradient(180deg, #fdebd0 0%, #fdf1dc 46%, var(--wa-ivory) 100%);
            padding: 54px 24px 60px;
            position: relative;
            text-align: center;
        }
        /* One penjor stands at the right edge and bends over the heading; its sampian hangs above the arch. */
        .wa-hero .orn-penjor { animation: wa-sway 7s ease-in-out infinite; height: 580px; position: absolute; right: 8px; top: 18px; transform-origin: 86% 100%; width: auto; }
        .wa-hero > :not(.orn) { position: relative; }
        .wa-heading { max-width: 58%; text-align: left; }
        .wa-heading .wa-script { font-size: 38px; margin-top: 12px; }
        .wa-arch-wrap { margin: 92px auto 0; width: min(62%, 270px); }
        .wa-arch-wrap .orn-jepun { position: absolute; z-index: 1; }
        .wa-arch-wrap .orn-jepun.is-a { bottom: 34px; left: -50px; transform: rotate(-18deg); width: 62px; }
        .wa-arch-wrap .orn-jepun.is-b { bottom: -16px; left: -18px; transform: rotate(24deg); width: 42px; }
        .wa-arch {
            aspect-ratio: 3 / 4;
            background: linear-gradient(160deg, #fff, #f3e3c2);
            border-radius: 999px 999px 26px 26px;
            box-shadow: 0 0 0 1px #e7d3a4, 0 0 0 9px #fffaf0, 0 0 0 10px var(--wa-gold-soft), 0 30px 60px rgba(104, 72, 22, .26);
            overflow: hidden;
        }
        .wa-arch img { height: 100%; object-fit: cover; width: 100%; }
        .wa-arch-empty { align-items: center; color: var(--wa-gold); display: flex; flex-direction: column; font-family: var(--wa-serif); font-size: 58px; gap: 6px; height: 100%; justify-content: center; }
        .wa-arch-empty .orn-canang { width: 46%; }
        .wa-hero h1 { font-family: var(--wa-serif); font-size: clamp(46px, 13vw, 66px); font-weight: 600; line-height: 1; margin: 34px 0 0; text-wrap: balance; }
        .wa-hero h1 span { color: var(--wa-gold); display: block; font-family: var(--wa-script); font-size: .6em; font-weight: 400; line-height: 1.1; }
        .wa-date { color: var(--wa-muted); font-size: 12px; font-weight: 600; letter-spacing: .26em; margin: 20px 0 0; text-transform: uppercase; }
        .wa-guest {
            background: rgba(255, 255, 255, .7);
            border: 1px solid #ecdcb6;
            border-radius: 18px;
            color: var(--wa-muted);
            display: inline-block;
            font-size: 12px;
            margin: 24px 0 0;
            padding: 12px 22px;
        }
        .wa-guest strong { color: var(--wa-ink); display: block; font-family: var(--wa-serif); font-size: 22px; font-weight: 600; margin-top: 2px; overflow-wrap: anywhere; }

        .wa-greeting p:not(.wa-eyebrow) { color: var(--wa-muted); font-family: var(--wa-serif); font-size: 21px; line-height: 1.6; margin: 18px auto 0; max-width: 400px; }

        /* The couple: two portraits stepping left and right. */
        .wa-couple { background: linear-gradient(180deg, var(--wa-ivory), #fbf2de 50%, var(--wa-ivory)); padding-bottom: 40px; }
        .wa-person { align-items: center; display: grid; gap: 20px; grid-template-columns: 42% 1fr; margin: 34px 0 0; text-align: left; }
        .wa-person.is-bride { grid-template-columns: 1fr 42%; text-align: right; }
        .wa-person.is-bride .wa-portrait { order: 2; }
        .wa-portrait {
            aspect-ratio: 4 / 5;
            background: linear-gradient(160deg, #fff, var(--wa-cream));
            border-radius: 999px 999px 18px 18px;
            box-shadow: 0 0 0 6px #fffaf0, 0 0 0 7px var(--wa-gold-soft), 0 18px 34px rgba(104, 72, 22, .2);
            overflow: hidden;
        }
        .wa-portrait img { height: 100%; object-fit: cover; width: 100%; }
        .wa-portrait.is-demo-groom img { transform: scale(2.7); transform-origin: 43% 74%; }
        .wa-portrait.is-demo-bride img { transform: scale(2.7); transform-origin: 57% 74%; }
        .wa-initial { align-items: center; color: var(--wa-gold); display: flex; font-family: var(--wa-serif); font-size: 64px; height: 100%; justify-content: center; }
        .wa-person h3 { font-family: var(--wa-serif); font-size: 27px; font-weight: 600; line-height: 1.12; margin: 6px 0 0; overflow-wrap: anywhere; }
        .wa-person p { color: var(--wa-muted); font-size: 13px; line-height: 1.65; margin: 10px 0 0; }
        .wa-person p strong { color: var(--wa-ink); display: block; font-weight: 600; }
        .wa-amp { color: var(--wa-gold); font-family: var(--wa-script); font-size: 46px; line-height: 1; margin: 22px 0 -12px; }

        /* Their story, down a gold thread. */
        .wa-story ol { list-style: none; margin: 34px auto 0; max-width: 400px; padding: 0 0 0 26px; position: relative; text-align: left; }
        .wa-story ol::before { background: linear-gradient(180deg, transparent, var(--wa-gold-soft) 10%, var(--wa-gold-soft) 90%, transparent); bottom: 0; content: ''; left: 6px; position: absolute; top: 0; width: 1.5px; }
        .wa-story li { padding: 0 0 30px 14px; position: relative; }
        .wa-story li::before { background: var(--wa-ivory); border: 2px solid var(--wa-gold); border-radius: 50%; content: ''; height: 13px; left: -26px; position: absolute; top: 5px; width: 13px; }
        .wa-story time { color: var(--wa-gold); font-size: 11px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; }
        .wa-story h3 { font-family: var(--wa-serif); font-size: 25px; font-weight: 600; margin: 4px 0 0; }
        .wa-story li p { color: var(--wa-muted); font-size: 14px; line-height: 1.7; margin: 6px 0 0; }
        .wa-story img { border-radius: 18px; box-shadow: 0 14px 30px rgba(104, 72, 22, .18); margin: 14px 0 0; }

        /* Songket band with the countdown. */
        .wa-band {
            background:
                repeating-linear-gradient(45deg, rgba(240, 207, 138, .13) 0 2px, transparent 2px 15px),
                repeating-linear-gradient(-45deg, rgba(240, 207, 138, .13) 0 2px, transparent 2px 15px),
                linear-gradient(160deg, #94455a, #77303f 60%, #8a3d4f);
            color: #fff5e1;
            padding: 60px 22px;
            position: relative;
            text-align: center;
        }
        .wa-band::before, .wa-band::after { background: repeating-linear-gradient(90deg, #e7c988 0 10px, transparent 10px 16px); content: ''; height: 3px; left: 0; opacity: .8; position: absolute; right: 0; }
        .wa-band::before { top: 10px; }
        .wa-band::after { bottom: 10px; }
        .wa-band .wa-eyebrow { color: #f0d79f; }
        .wa-band h2 { color: #fff8e8; font-family: var(--wa-serif); font-size: 36px; font-weight: 600; margin: 10px 0 0; }
        .wa-countdown { display: grid; gap: 10px; grid-template-columns: repeat(4, 1fr); margin: 26px auto 0; max-width: 380px; }
        .wa-countdown div { background: rgba(255, 248, 232, .12); border: 1px solid rgba(240, 215, 159, .45); border-radius: 16px; padding: 14px 4px 12px; }
        .wa-countdown strong { display: block; font-family: var(--wa-serif); font-size: 32px; font-weight: 600; line-height: 1; }
        .wa-countdown span { color: #f0d79f; display: block; font-size: 10px; letter-spacing: .16em; margin-top: 6px; text-transform: uppercase; }
        .wa-band .wa-button { background: #fff8e8; box-shadow: none; color: var(--wa-maroon); margin-top: 26px; }

        /* The ceremony, between the two halves of a candi bentar. */
        .wa-gate { align-items: flex-end; display: flex; justify-content: center; margin: 26px 0 0; }
        .wa-gate .orn-gapura { flex: none; width: clamp(92px, 27vw, 140px); }
        .wa-gate-date { margin: 0; padding: 0 6px 22px; }
        .wa-gate-date span { color: var(--wa-muted); display: block; font-size: 11px; font-weight: 600; letter-spacing: .2em; text-transform: uppercase; }
        .wa-gate-date strong { color: var(--wa-maroon); display: block; font-family: var(--wa-serif); font-size: clamp(60px, 19vw, 86px); font-weight: 600; line-height: .95; }
        .wa-details { background: #fff; border-radius: 26px; box-shadow: 0 18px 44px rgba(104, 72, 22, .14); margin: -6px 0 0; padding: 28px 22px; position: relative; }
        .wa-details dl { margin: 0; }
        .wa-details div + div { border-top: 1px solid #f0e4c8; margin-top: 16px; padding-top: 16px; }
        .wa-details dt { color: var(--wa-gold); font-size: 10px; font-weight: 700; letter-spacing: .26em; text-transform: uppercase; }
        .wa-details dd { font-family: var(--wa-serif); font-size: 23px; font-weight: 600; line-height: 1.25; margin: 6px 0 0; }
        .wa-details dd small { color: var(--wa-muted); display: block; font-family: Inter, system-ui, sans-serif; font-size: 13px; font-weight: 400; line-height: 1.6; margin-top: 6px; }
        .wa-details .wa-button { margin-top: 24px; }

        .wa-gallery-grid { column-count: 2; column-gap: 12px; margin: 30px 0 0; }
        .wa-gallery-grid img { border-radius: 18px; box-shadow: 0 12px 26px rgba(104, 72, 22, .16); break-inside: avoid; margin: 0 0 12px; width: 100%; }
        .wa-gallery-grid img:first-child { aspect-ratio: 5 / 4; border-radius: 170px 170px 18px 18px; column-span: all; object-fit: cover; }

        .wa-closing { background: linear-gradient(180deg, var(--wa-ivory), #f8ecd3); padding-bottom: 96px; }
        .wa-closing .orn-canang { margin: 0 auto 20px; width: 96px; }
        .wa-closing h2 { margin-top: 4px; }
        .wa-closing p:not(.wa-script):not(.wa-eyebrow) { color: var(--wa-muted); font-size: 14px; line-height: 1.75; margin: 16px auto 0; max-width: 380px; }
        .wa-closing .wa-button { margin-top: 26px; }
        .wa-credit { color: #a2927a; font-size: 12px; margin-top: 34px !important; }

        .wa-player { bottom: 18px; position: fixed; right: 18px; z-index: 30; }
        .wa-player button { background: #fffaf0; border: 1px solid var(--wa-gold-soft); border-radius: 999px; box-shadow: 0 10px 24px rgba(74, 52, 20, .22); color: var(--wa-gold); cursor: pointer; font: 700 12px Inter, system-ui, sans-serif; min-height: 44px; padding: 0 18px; }

        .js [data-reveal] { opacity: 0; transform: translateY(26px); transition: opacity .8s ease, transform .9s cubic-bezier(.2, .75, .25, 1); }
        .js [data-reveal].is-revealed { opacity: 1; transform: none; }
        @keyframes wa-sway { 0%, 100% { transform: rotate(0); } 50% { transform: rotate(-1.4deg); } }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .wa-hero .orn-penjor { animation: none; }
            .js [data-reveal] { opacity: 1; transform: none; transition: none; }
        }
        @media (max-width: 350px) {
            .wa-section { padding-left: 18px; padding-right: 18px; }
            .wa-person, .wa-person.is-bride { gap: 14px; }
            .wa-person h3 { font-size: 23px; }
            .wa-countdown strong { font-size: 26px; }
        }
    </style>
</head>
<body>
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $mediaUrl($heroPhoto ?: $demoPhoto),
        'openingAccent' => '#ecd08a',
        'openingText' => '#fff8e8',
        'openingShade' => 'rgba(70, 30, 38, .56)',
        'openingFont' => "'Cormorant Garamond', Georgia, serif",
        'openingHasMusic' => (bool) ($musicPath && ! $isPreview),
    ])
    @include('invitations.templates.upacara-anak.parts.defs')
    <main class="wa-page">
        @if ($isPreview)
            <p class="wa-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</p>
        @endif

        <header class="wa-hero">
            @include($ornaments.'penjor')
            <div class="wa-heading">
                <p class="wa-eyebrow">Om Swastyastu</p>
                <p class="wa-script">{{ $invitation->event_type }}</p>
            </div>
            <div class="wa-arch-wrap">
            @include($ornaments.'jepun', ['class' => 'is-a'])
            @include($ornaments.'jepun', ['class' => 'is-b'])
            <div class="wa-arch">
                @if ($heroPhoto)
                    <img src="{{ $mediaUrl($heroPhoto) }}" alt="Foto {{ $couple }}">
                @else
                    <div class="wa-arch-empty">
                        @include($ornaments.'canang')
                        <span>{{ mb_substr($invitation->groom_nickname, 0, 1) }} &amp; {{ mb_substr($invitation->bride_nickname, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            </div>
            <h1>{{ $invitation->groom_nickname }} <span>&amp;</span> {{ $invitation->bride_nickname }}</h1>
            <p class="wa-date">{{ $invitation->event_date->translatedFormat('l · d F Y') }}</p>
            @if ($guest !== '')
                <p class="wa-guest">Kepada Yth.<strong>{{ $guest }}</strong></p>
            @endif
        </header>

        <section class="wa-section wa-greeting" data-reveal>
            @include($ornaments.'patra')
            <p class="wa-eyebrow">Atas asung kertha wara nugraha</p>
            <p>{{ $invitation->opening_quote ?: 'Dengan restu keluarga, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu pada hari bahagia kami.' }}</p>
        </section>

        <section class="wa-section wa-couple" data-reveal aria-label="Mempelai">
            <p class="wa-eyebrow">Mempelai</p>
            <article class="wa-person">
                <div class="wa-portrait {{ $isPreview && ! $invitation->groom_photo ? 'is-demo-groom' : '' }}">
                    @if ($groomPhoto)
                        <img src="{{ $mediaUrl($groomPhoto) }}" alt="Foto {{ $invitation->groom_full_name }}" loading="lazy">
                    @else
                        <span class="wa-initial">{{ mb_substr($invitation->groom_nickname, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <p class="wa-eyebrow">Mempelai pria</p>
                    <h3>{{ $invitation->groom_full_name }}</h3>
                    @if ($invitation->groom_father_name || $invitation->groom_mother_name)
                        <p>Putra dari
                            <strong>{{ collect([$invitation->groom_father_name, $invitation->groom_mother_name])->filter()->implode(' & ') }}</strong></p>
                    @endif
                </div>
            </article>
            <p class="wa-amp" aria-hidden="true">&amp;</p>
            <article class="wa-person is-bride">
                <div class="wa-portrait {{ $isPreview && ! $invitation->bride_photo ? 'is-demo-bride' : '' }}">
                    @if ($bridePhoto)
                        <img src="{{ $mediaUrl($bridePhoto) }}" alt="Foto {{ $invitation->bride_full_name }}" loading="lazy">
                    @else
                        <span class="wa-initial">{{ mb_substr($invitation->bride_nickname, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <p class="wa-eyebrow">Mempelai wanita</p>
                    <h3>{{ $invitation->bride_full_name }}</h3>
                    @if ($invitation->bride_father_name || $invitation->bride_mother_name)
                        <p>Putri dari
                            <strong>{{ collect([$invitation->bride_father_name, $invitation->bride_mother_name])->filter()->implode(' & ') }}</strong></p>
                    @endif
                </div>
            </article>
        </section>

        @if ($story)
            <section class="wa-section wa-story" data-reveal aria-label="Kisah kami">
                <p class="wa-script">Kisah kami</p>
                <ol>
                    @foreach ($story as $chapter)
                        <li>
                            @if ($chapter['when'])<time>{{ $chapter['when'] }}</time>@endif
                            <h3>{{ $chapter['title'] }}</h3>
                            @if ($chapter['body'])<p>{{ $chapter['body'] }}</p>@endif
                            @if ($chapter['photo'])<img src="{{ $mediaUrl($chapter['photo']) }}" alt="{{ $chapter['title'] }}" loading="lazy">@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <section class="wa-band" data-reveal aria-label="Hitung mundur">
            <p class="wa-eyebrow">Menuju hari bahagia</p>
            <h2>{{ $invitation->event_date->translatedFormat('d F Y') }}</h2>
            <div class="wa-countdown" data-countdown="{{ $eventTarget }}">
                <div><strong data-days>0</strong><span>Hari</span></div>
                <div><strong data-hours>00</strong><span>Jam</span></div>
                <div><strong data-minutes>00</strong><span>Menit</span></div>
                <div><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
            <a class="wa-button" href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer">Simpan ke kalender</a>
        </section>

        <section class="wa-section" data-reveal aria-label="Waktu dan tempat">
            <p class="wa-eyebrow">Waktu dan tempat</p>
            <h2>{{ $invitation->event_type }}</h2>
            <div class="wa-gate">
                @include($ornaments.'gapura')
                <p class="wa-gate-date">
                    <span>{{ $invitation->event_date->translatedFormat('l') }}</span>
                    <strong>{{ $invitation->event_date->translatedFormat('d') }}</strong>
                    <span>{{ $invitation->event_date->translatedFormat('F Y') }}</span>
                </p>
                @include($ornaments.'gapura', ['class' => 'is-mirrored'])
            </div>
            <div class="wa-details">
                <dl>
                    <div><dt>Waktu</dt><dd>{{ $startTime }}@if ($endTime) - {{ $endTime }}@endif WITA</dd></div>
                    <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
                </dl>
                @if ($invitation->google_maps_url)
                    <a class="wa-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>
                @endif
            </div>
        </section>

        @if (count($gallery))
            <section class="wa-section" data-reveal aria-label="Galeri">
                <p class="wa-script">Momen kami</p>
                <div class="wa-gallery-grid">
                    @foreach ($gallery as $photo)
                        <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $couple }} {{ $loop->iteration }}" loading="lazy">
                    @endforeach
                </div>
            </section>
        @endif

        @if (($isPreview || $invitation->giftSetting?->is_active) && $invitation->giftSetting)
            <div data-reveal>
                @include('invitations.partials.wedding-gift')
            </div>
        @endif

        <footer class="wa-section wa-closing" data-reveal>
            @include($ornaments.'canang')
            <p class="wa-script">Matur suksma</p>
            <h2>{{ $couple }}</h2>
            <p>Merupakan kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
            <p class="wa-eyebrow" style="margin-top: 22px">Om Shanti, Shanti, Shanti Om</p>
            @unless ($isPreview)
                <button class="wa-button is-quiet" type="button" data-share>Bagikan undangan</button>
            @endunless
            <p class="wa-credit">@include('invitations.partials.app-credit')</p>
        </footer>
    </main>

    @if ($musicPath && ! $isPreview)
        <div class="wa-player">
            <button type="button" data-audio-toggle aria-label="Putar musik">Musik</button>
            <audio data-audio loop preload="none" src="{{ $mediaUrl($musicPath) }}"></audio>
        </div>
    @endif

    <script>
        (() => {
            const reveal = document.querySelectorAll('[data-reveal]');
            if (!('IntersectionObserver' in window) || matchMedia('(prefers-reduced-motion: reduce)').matches) {
                reveal.forEach((item) => item.classList.add('is-revealed'));
            } else {
                const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
                    if (entry.isIntersecting) { entry.target.classList.add('is-revealed'); observer.unobserve(entry.target); }
                }), { threshold: .1 });
                reveal.forEach((item) => observer.observe(item));
            }

            const countdown = document.querySelector('[data-countdown]');
            const target = new Date(countdown.dataset.countdown).getTime();
            const field = (name) => countdown.querySelector(`[data-${name}]`);
            const tick = () => {
                const left = Math.max(0, target - Date.now());
                field('days').textContent = Math.floor(left / 86400000);
                field('hours').textContent = String(Math.floor(left / 3600000) % 24).padStart(2, '0');
                field('minutes').textContent = String(Math.floor(left / 60000) % 60).padStart(2, '0');
                field('seconds').textContent = String(Math.floor(left / 1000) % 60).padStart(2, '0');
            };
            tick();
            setInterval(tick, 1000);

            const audio = document.querySelector('[data-audio]');
            const toggle = document.querySelector('[data-audio-toggle]');
            toggle?.addEventListener('click', async () => {
                if (!audio.paused) { audio.pause(); toggle.textContent = 'Musik'; return; }
                try { await audio.play(); toggle.textContent = 'Jeda'; } catch { toggle.textContent = 'Musik'; }
            });

            document.querySelector('[data-share]')?.addEventListener('click', async (event) => {
                const text = {{ Illuminate\Support\Js::from($shareText) }};
                if (navigator.share) { try { await navigator.share({ text }); } catch {} return; }
                await navigator.clipboard.writeText(text);
                event.currentTarget.textContent = 'Link tersalin';
            });
        })();
    </script>
</body>
</html>
