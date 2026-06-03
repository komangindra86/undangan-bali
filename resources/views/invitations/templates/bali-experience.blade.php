@php
    $isPreview = $isPreview ?? false;
    $styleKey = $invitation->template->slug ?? 'bali-classic';
    $styles = [
        'bali-classic' => [
            'label' => 'Bali Classic',
            'background' => '#160f0c',
            'panel' => '#241814',
            'accent' => '#cf9c46',
            'soft' => '#f6dfad',
            'motif' => '#c8913e24',
            'hero' => 'templates/bali-preview/hero-couple.jpg',
            'ceremony' => 'Pawiwahan Adat Bali',
        ],
        'pura-sunset' => [
            'label' => 'Pura Sunset',
            'background' => '#180f17',
            'panel' => '#2a1622',
            'accent' => '#e58e59',
            'soft' => '#ffd9bb',
            'motif' => '#ed956530',
            'hero' => 'templates/bali-preview/gallery-evening.jpg',
            'ceremony' => 'Janji Suci di Senja Bali',
        ],
        'ubud-garden' => [
            'label' => 'Ubud Garden',
            'background' => '#111811',
            'panel' => '#17251a',
            'accent' => '#d2ab5f',
            'soft' => '#f1e3b6',
            'motif' => '#d4b36224',
            'hero' => 'templates/bali-preview/gallery-pavilion.jpg',
            'ceremony' => 'Pawiwahan di Taman Ubud',
        ],
    ];
    $theme = $styles[$styleKey] ?? $styles['bali-classic'];
    $musicPath = $invitation->music_type === 'default' && $invitation->music
        ? $invitation->music->file_path
        : $invitation->music_file;
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di acara pernikahan kami. Buka undangan: '.url()->current();
    $demoPhoto = 'templates/bali-preview/hero-couple.jpg';
    $groomPhoto = $invitation->groom_photo ?: ($isPreview ? $demoPhoto : null);
    $bridePhoto = $invitation->bride_photo ?: ($isPreview ? $demoPhoto : null);
    $demoGallery = [
        'templates/bali-preview/gallery-details.jpg',
        'templates/bali-preview/gallery-pavilion.jpg',
        'templates/bali-preview/gallery-evening.jpg',
    ];
    $gallery = $isPreview ? $demoGallery : ($invitation->gallery_photos ?? []);
    $hero = $isPreview
        ? $theme['hero']
        : ($gallery[0] ?? $groomPhoto ?? $bridePhoto ?? 'templates/bali-preview/gallery-pavilion.jpg');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $isPreview ? 'Preview '.$theme['label'] : 'Undangan '.$invitation->groom_nickname.' & '.$invitation->bride_nickname }}</title>
    @include('invitations.partials.mobile-viewport')
    <style>
        :root {
            --bg: {{ $theme['background'] }};
            --panel: {{ $theme['panel'] }};
            --accent: {{ $theme['accent'] }};
            --soft: {{ $theme['soft'] }};
            --motif: {{ $theme['motif'] }};
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--bg);
            color: #fbf7ef;
            font-family: Georgia, "Times New Roman", serif;
        }
        .preview-banner {
            align-items: center;
            backdrop-filter: blur(10px);
            background: rgba(0, 0, 0, .76);
            display: flex;
            font: 14px Arial, sans-serif;
            justify-content: space-between;
            left: 0;
            padding: 14px 18px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 30;
        }
        .preview-banner strong { color: var(--soft); font-weight: 500; }
        .preview-banner span { color: #cfc7bd; }
        .page {
            background-color: var(--panel);
            background-image:
                radial-gradient(circle at 0 0, var(--motif) 0 2px, transparent 3px),
                linear-gradient(45deg, transparent 48%, var(--motif) 49%, transparent 52%);
            background-size: 24px 24px, 54px 54px;
            box-shadow: 0 0 50px rgba(0, 0, 0, .4);
            margin: 0 auto;
            max-width: min(540px, 100vw);
            min-height: 100vh;
            overflow: hidden;
        }
        .hero {
            align-items: flex-end;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }
        .hero-image {
            animation: slowZoom 16s ease-in-out infinite alternate;
            height: 100%;
            inset: 0;
            object-fit: cover;
            opacity: .64;
            position: absolute;
            width: 100%;
        }
        .hero-shade {
            background: linear-gradient(to top, var(--panel) 0%, rgba(0, 0, 0, .3) 48%, rgba(0, 0, 0, .48) 100%);
            inset: 0;
            position: absolute;
        }
        .hero-content {
            padding: 92px 28px 76px;
            position: relative;
            text-align: center;
            width: 100%;
        }
        .overline {
            color: var(--accent);
            font: 12px Arial, sans-serif;
            letter-spacing: .38em;
            margin: 0 0 30px;
            text-transform: uppercase;
        }
        .hero .overline { color: var(--soft); }
        .ornament {
            animation: float 3.5s ease-in-out infinite;
            border: 1px solid var(--accent);
            height: 48px;
            margin: 0 auto 34px;
            transform: rotate(45deg);
            width: 48px;
        }
        h1 {
            color: var(--soft);
            font-size: clamp(44px, 11vw, 58px);
            font-weight: 400;
            line-height: 1.12;
            margin: 0;
        }
        h1 span { color: var(--accent); }
        .date {
            color: #f0ebe3;
            font-size: 14px;
            letter-spacing: .18em;
            margin: 34px 0 0;
        }
        .section {
            padding: 64px 30px;
            text-align: center;
        }
        .quote {
            color: #ddd4c9;
            font-size: 17px;
            font-style: italic;
            line-height: 2;
            margin: 0;
        }
        .couple { padding-top: 0; }
        .photo-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-bottom: 38px;
        }
        .photo-grid img {
            border: 1px solid var(--accent);
            border-radius: 16px;
            height: 245px;
            object-fit: cover;
            width: 100%;
        }
        .person + .person { margin-top: 34px; }
        h2 {
            color: var(--soft);
            font-size: 28px;
            font-weight: 400;
            margin: 0 0 12px;
        }
        .parents {
            color: #d0c6bb;
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }
        .event {
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            border-top: 1px solid rgba(255, 255, 255, .1);
        }
        .event-card {
            background: rgba(0, 0, 0, .17);
            border: 1px solid var(--motif);
            border-radius: 28px;
            padding: 34px 23px;
        }
        .event-date {
            color: var(--soft);
            font-size: 24px;
            margin: 0;
        }
        .event-time { color: #d0c6bb; margin: 16px 0 28px; }
        h3 { font-size: 21px; font-weight: 400; margin: 0 0 12px; }
        .address { color: #d0c6bb; font-size: 14px; line-height: 1.75; margin: 0; }
        .map-button {
            background: var(--accent);
            border-radius: 999px;
            color: #21150f;
            display: inline-block;
            font: 600 14px Arial, sans-serif;
            margin-top: 28px;
            padding: 14px 28px;
            text-decoration: none;
            transition: transform .2s ease;
        }
        .map-button:hover { transform: scale(1.04); }
        .gallery { padding-left: 20px; padding-right: 20px; }
        .gallery-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .gallery-grid img {
            border-radius: 24px;
            height: 208px;
            object-fit: cover;
            width: 100%;
        }
        .gallery-grid img:first-child {
            grid-column: span 2;
            height: 230px;
        }
        .closing {
            border-top: 1px solid rgba(255, 255, 255, .1);
        }
        .thanks { color: var(--soft); font-size: 32px; margin: 0 0 16px; }
        .closing-copy { color: #d0c6bb; font-size: 14px; line-height: 1.8; margin: 0; }
        .share-button {
            background: transparent;
            border: 1px solid var(--accent);
            border-radius: 999px;
            color: var(--soft);
            cursor: pointer;
            font: 500 14px Arial, sans-serif;
            margin: 34px 0 0;
            padding: 14px 28px;
        }
        .watermark { color: #877b70; font: 12px Arial, sans-serif; margin: 58px 0 0; }
        .player {
            align-items: center;
            background: var(--panel);
            border: 1px solid var(--accent);
            border-radius: 30px;
            bottom: 18px;
            display: flex;
            gap: 11px;
            padding: 8px 14px 8px 8px;
            position: fixed;
            right: 18px;
            z-index: 20;
        }
        .player button {
            background: var(--accent);
            border: 0;
            border-radius: 50%;
            color: #21150f;
            cursor: pointer;
            height: 40px;
            width: 40px;
        }
        .player small { color: var(--soft); font: 12px Arial, sans-serif; }
        .rise { animation: rise .9s ease both; }
        .rise-delay { animation: rise .9s .22s ease both; }
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .75s ease, transform .75s ease;
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        @keyframes slowZoom { from { transform: scale(1); } to { transform: scale(1.08); } }
        @keyframes float {
            0%, 100% { transform: rotate(45deg) translate(0); }
            50% { transform: rotate(45deg) translate(-5px, -5px); }
        }
        @keyframes rise { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 430px) {
            .preview-banner { font-size: 12px; gap: 12px; }
            .section { padding-left: 24px; padding-right: 24px; }
            .photo-grid img { height: 205px; }
        }
    </style>
</head>
<body>
    @if ($isPreview)
        <div class="preview-banner">
            <strong>Preview dummy: {{ $theme['label'] }}</strong>
            <span>Kembali ke aplikasi untuk memilih</span>
        </div>
    @endif
    <main class="page">
        <section class="hero">
            <img class="hero-image" src="{{ Storage::url($hero) }}" alt="Nuansa pernikahan Bali">
            <div class="hero-shade"></div>
            <div class="hero-content">
                <p class="overline rise">{{ $theme['ceremony'] }}</p>
                <div class="ornament"></div>
                <h1 class="rise-delay">{{ $invitation->groom_nickname }} <span>&amp;</span> {{ $invitation->bride_nickname }}</h1>
                <p class="date rise-delay">{{ $invitation->event_date->translatedFormat('d F Y') }}</p>
            </div>
        </section>

        <section class="section reveal">
            <p class="overline">Om Swastyastu</p>
            <p class="quote">"{{ $invitation->opening_quote }}"</p>
        </section>

        <section class="section couple reveal">
            <p class="overline">Mempelai</p>
            @if ($groomPhoto || $bridePhoto)
                <div class="photo-grid">
                    @if ($groomPhoto)
                        <img src="{{ Storage::url($groomPhoto) }}" alt="Foto mempelai pria">
                    @endif
                    @if ($bridePhoto)
                        <img src="{{ Storage::url($bridePhoto) }}" alt="Foto mempelai wanita">
                    @endif
                </div>
            @endif
            <article class="person">
                <h2>{{ $invitation->groom_full_name }}</h2>
                <p class="parents">Putra dari<br>{{ $invitation->groom_father_name }} &amp; {{ $invitation->groom_mother_name }}</p>
            </article>
            <article class="person">
                <h2>{{ $invitation->bride_full_name }}</h2>
                <p class="parents">Putri dari<br>{{ $invitation->bride_father_name }} &amp; {{ $invitation->bride_mother_name }}</p>
            </article>
        </section>

        <section class="section event reveal">
            <p class="overline">{{ $invitation->event_type }}</p>
            <div class="event-card">
                <p class="event-date">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
                <p class="event-time">{{ substr($invitation->start_time, 0, 5) }}@if ($invitation->end_time) - {{ substr($invitation->end_time, 0, 5) }}@endif WITA</p>
                <h3>{{ $invitation->venue_name }}</h3>
                <p class="address">{{ $invitation->venue_address }}</p>
                @if ($invitation->google_maps_url)
                    <a class="map-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener">Buka Google Maps</a>
                @endif
            </div>
        </section>

        @if (count($gallery))
            <section class="section gallery reveal">
                <p class="overline">Galeri Bahagia</p>
                <div class="gallery-grid">
                    @foreach ($gallery as $photo)
                        <img src="{{ Storage::url($photo) }}" alt="Foto galeri pernikahan">
                    @endforeach
                </div>
            </section>
        @endif

        @if (($isPreview || $invitation->giftSetting?->is_active) && $invitation->giftSetting)
            @include('invitations.partials.wedding-gift')
        @endif

        <section class="section closing reveal">
            <p class="thanks">Matur Suksma</p>
            <p class="closing-copy">Merupakan kehormatan dan kebahagiaan bagi kami apabila Anda berkenan hadir.</p>
            @unless ($isPreview)
                <button id="share" class="share-button">Bagikan Undangan</button>
            @endunless
            <p class="watermark">Dibuat gratis dengan aplikasi Undangan Pernikahan Bali</p>
        </section>
    </main>

    @if ($musicPath && ! $isPreview)
        <div class="player">
            <button type="button" data-audio-toggle>Play</button>
            <small>Musik Undangan</small>
            <audio data-audio loop preload="metadata" src="{{ Storage::url($musicPath) }}"></audio>
        </div>
    @endif
    <script>
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        }), { threshold: 0.16 });
        document.querySelectorAll('.reveal').forEach((node) => observer.observe(node));

        const audio = document.querySelector('[data-audio]');
        const audioToggle = document.querySelector('[data-audio-toggle]');
        if (audioToggle) {
            audioToggle.addEventListener('click', function () {
                if (audio.paused) {
                    audio.play();
                    audioToggle.textContent = 'Pause';
                    return;
                }
                audio.pause();
                audioToggle.textContent = 'Play';
            });
        }

        const shareButton = document.getElementById('share');
        if (shareButton) {
            shareButton.addEventListener('click', async function () {
                const text = @json($shareText);
                if (navigator.share) {
                    await navigator.share({ text: text, url: window.location.href });
                    return;
                }
                await navigator.clipboard.writeText(text);
                shareButton.textContent = 'Link tersalin';
            });
        }
    </script>
</body>
</html>
