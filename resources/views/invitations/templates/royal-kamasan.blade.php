@php
    $isPreview = $isPreview ?? false;
    $demoPhoto = 'templates/bali-preview/hero-couple.jpg';
    $demoGallery = [
        'templates/bali-preview/gallery-details.jpg',
        'templates/bali-preview/gallery-evening.jpg',
        'templates/bali-preview/gallery-pavilion.jpg',
    ];
    $groomPhoto = $invitation->groom_photo ?: ($isPreview ? $demoPhoto : null);
    $bridePhoto = $invitation->bride_photo ?: ($isPreview ? $demoPhoto : null);
    $gallery = $isPreview ? $demoGallery : ($invitation->gallery_photos ?? []);
    $hero = $isPreview ? $demoGallery[0] : ($gallery[0] ?? $groomPhoto ?? $bridePhoto ?? 'templates/bali-preview/gallery-details.jpg');
    $musicPath = $invitation->music_type === 'default' && $invitation->music ? $invitation->music->file_path : $invitation->music_file;
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di acara pernikahan kami. Buka undangan: '.url()->current();
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isPreview ? 'Preview Royal Kamasan' : 'Undangan '.$invitation->groom_nickname.' & '.$invitation->bride_nickname }}</title>
    <style>
        :root {
            --ink: #150d0a;
            --maroon: #3a1512;
            --maroon-soft: #5a241c;
            --gold: #d9aa56;
            --gold-soft: #f5df9f;
            --ivory: #fff7e8;
            --muted: #d7c2a8;
            --shadow: rgba(0, 0, 0, .36);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            background:
                radial-gradient(circle at top, #5c281d 0, transparent 34rem),
                linear-gradient(145deg, #120b09, #27110e 48%, #0f0907);
            color: var(--ivory);
            font-family: Arial, sans-serif;
            letter-spacing: 0;
            margin: 0;
        }
        .preview-banner {
            background: rgba(19, 10, 8, .86);
            border-bottom: 1px solid rgba(217, 170, 86, .32);
            color: var(--gold-soft);
            font-size: 13px;
            left: 0;
            padding: 13px 18px;
            position: fixed;
            right: 0;
            text-align: center;
            top: 0;
            z-index: 30;
        }
        .page {
            background:
                linear-gradient(90deg, rgba(217, 170, 86, .08) 1px, transparent 1px),
                linear-gradient(rgba(217, 170, 86, .08) 1px, transparent 1px),
                linear-gradient(180deg, #2b100d, #160d0b 42%, #25100d);
            background-size: 28px 28px, 28px 28px, auto;
            box-shadow: 0 0 70px var(--shadow);
            margin: 0 auto;
            max-width: 560px;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }
        .page:before,
        .page:after {
            background: linear-gradient(180deg, transparent, rgba(217, 170, 86, .34), transparent);
            content: "";
            height: 100%;
            position: absolute;
            top: 0;
            width: 1px;
            z-index: 2;
        }
        .page:before { left: 18px; }
        .page:after { right: 18px; }
        .hero {
            display: grid;
            min-height: 100vh;
            overflow: hidden;
            place-items: end center;
            position: relative;
            text-align: center;
        }
        .hero img {
            animation: portraitDrift 16s ease-in-out infinite alternate;
            height: 100%;
            inset: 0;
            object-fit: cover;
            opacity: .64;
            position: absolute;
            width: 100%;
        }
        .hero:after {
            background:
                linear-gradient(to bottom, rgba(21, 13, 10, .5), rgba(21, 13, 10, .08) 36%, #2b100d 100%),
                radial-gradient(circle at center, transparent 0 42%, rgba(21, 13, 10, .55) 72%);
            content: "";
            inset: 0;
            position: absolute;
        }
        .hero-content {
            padding: 84px 30px 72px;
            position: relative;
            width: 100%;
            z-index: 1;
        }
        .kicker {
            color: var(--gold);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .34em;
            line-height: 1.8;
            margin: 0 0 26px;
            text-transform: uppercase;
        }
        .patra {
            align-items: center;
            color: var(--gold-soft);
            display: flex;
            gap: 14px;
            justify-content: center;
            margin: 0 auto 28px;
        }
        .patra:before,
        .patra:after {
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            content: "";
            height: 1px;
            width: 82px;
        }
        .patra span {
            border: 1px solid var(--gold);
            border-radius: 50%;
            display: grid;
            height: 42px;
            place-items: center;
            width: 42px;
        }
        h1 {
            color: var(--ivory);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(44px, 11vw, 62px);
            font-weight: 400;
            line-height: 1.08;
            margin: 0;
            text-shadow: 0 8px 28px rgba(0, 0, 0, .48);
        }
        h1 em {
            color: var(--gold);
            display: block;
            font-size: .6em;
            font-style: normal;
            margin: 8px 0;
        }
        .hero-date {
            color: var(--gold-soft);
            font-size: 14px;
            letter-spacing: .18em;
            margin: 34px 0 0;
        }
        .section {
            padding: 68px 32px;
            position: relative;
            text-align: center;
            z-index: 3;
        }
        .panel {
            background: rgba(20, 11, 9, .58);
            border: 1px solid rgba(217, 170, 86, .34);
            box-shadow: 0 18px 46px rgba(0, 0, 0, .22);
            padding: 34px 24px;
        }
        .quote {
            color: var(--muted);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 18px;
            font-style: italic;
            line-height: 2;
            margin: 0;
        }
        .couple-grid {
            display: grid;
            gap: 18px;
        }
        .person {
            background: linear-gradient(180deg, rgba(91, 38, 28, .78), rgba(27, 14, 11, .84));
            border: 1px solid rgba(217, 170, 86, .3);
            padding: 12px 12px 30px;
        }
        .person img {
            display: block;
            height: 288px;
            object-fit: cover;
            width: 100%;
        }
        .person h2 {
            color: var(--gold-soft);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 29px;
            font-weight: 400;
            line-height: 1.22;
            margin: 25px 0 11px;
        }
        .parents {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.75;
            margin: 0;
        }
        .event {
            background: linear-gradient(180deg, rgba(217, 170, 86, .12), rgba(217, 170, 86, .03));
            border-bottom: 1px solid rgba(217, 170, 86, .22);
            border-top: 1px solid rgba(217, 170, 86, .22);
        }
        .event-date {
            color: var(--gold-soft);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 34px;
            line-height: 1.25;
            margin: 0 0 18px;
        }
        .event-time {
            color: var(--gold);
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .12em;
            margin: 0 0 28px;
        }
        .venue {
            color: var(--ivory);
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 10px;
        }
        .address {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
            margin: 0;
        }
        .button,
        .share {
            background: linear-gradient(135deg, #f1cd75, #b98235);
            border: 0;
            border-radius: 999px;
            color: #1e100b;
            display: inline-block;
            font-weight: 800;
            margin-top: 28px;
            min-height: 48px;
            padding: 15px 28px;
            text-decoration: none;
        }
        .countdown {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            padding: 0 24px 68px;
            position: relative;
            z-index: 3;
        }
        .countdown div {
            background: #f7df9b;
            color: #29110c;
            min-height: 84px;
            padding: 15px 4px;
            text-align: center;
        }
        .countdown b {
            display: block;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 28px;
            line-height: 1.2;
        }
        .countdown span {
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .gallery-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .gallery-grid img {
            height: 218px;
            object-fit: cover;
            width: 100%;
        }
        .gallery-grid img:first-child {
            grid-column: span 2;
            height: 290px;
        }
        .closing-copy {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
            margin: 18px 0 0;
        }
        .thanks {
            color: var(--gold-soft);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 38px;
            font-weight: 400;
            margin: 0;
        }
        .watermark {
            color: rgba(215, 194, 168, .58);
            font-size: 11px;
            margin: 54px 0 0;
        }
        .player {
            align-items: center;
            background: #24100d;
            border: 1px solid rgba(217, 170, 86, .5);
            border-radius: 999px;
            bottom: 18px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .32);
            display: flex;
            gap: 10px;
            padding: 8px 15px 8px 8px;
            position: fixed;
            right: 15px;
            z-index: 25;
        }
        .player button {
            background: var(--gold);
            border: 0;
            border-radius: 50%;
            color: #1e100b;
            height: 40px;
            width: 40px;
        }
        .player small {
            color: var(--gold-soft);
            font-size: 12px;
        }
        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .78s ease, transform .78s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: none;
        }
        @keyframes portraitDrift {
            to { transform: scale(1.1) translateY(-1.5%); }
        }
        @media (max-width: 430px) {
            .section { padding-left: 26px; padding-right: 26px; }
            .page:before { left: 12px; }
            .page:after { right: 12px; }
            .person img { height: 244px; }
            .gallery-grid img:first-child { height: 240px; }
            .gallery-grid img { height: 188px; }
            .countdown { padding-left: 18px; padding-right: 18px; }
        }
    </style>
</head>
<body>
@if ($isPreview)
    <div class="preview-banner">Preview dummy: Royal Kamasan | Tema adat gelap dengan aksen emas</div>
@endif
<main class="page">
    <section class="hero">
        <img src="{{ Storage::url($hero) }}" alt="Detail pernikahan adat Bali">
        <div class="hero-content">
            <p class="kicker">Pawiwahan Adat Bali</p>
            <div class="patra"><span>&#10022;</span></div>
            <h1>{{ $invitation->groom_nickname }} <em>&amp;</em> {{ $invitation->bride_nickname }}</h1>
            <p class="hero-date">{{ $invitation->event_date->translatedFormat('d F Y') }}</p>
        </div>
    </section>

    <section class="section reveal">
        <p class="kicker">Om Swastyastu</p>
        <div class="panel">
            <p class="quote">"{{ $invitation->opening_quote }}"</p>
        </div>
    </section>

    <section class="section reveal">
        <p class="kicker">Kedua Mempelai</p>
        <div class="couple-grid">
            <article class="person">
                @if ($groomPhoto)
                    <img src="{{ Storage::url($groomPhoto) }}" alt="Foto mempelai pria">
                @endif
                <h2>{{ $invitation->groom_full_name }}</h2>
                <p class="parents">Putra dari<br>{{ $invitation->groom_father_name }} &amp; {{ $invitation->groom_mother_name }}</p>
            </article>
            <article class="person">
                @if ($bridePhoto)
                    <img src="{{ Storage::url($bridePhoto) }}" alt="Foto mempelai wanita">
                @endif
                <h2>{{ $invitation->bride_full_name }}</h2>
                <p class="parents">Putri dari<br>{{ $invitation->bride_father_name }} &amp; {{ $invitation->bride_mother_name }}</p>
            </article>
        </div>
    </section>

    <section class="section event reveal">
        <p class="kicker">{{ $invitation->event_type }}</p>
        <div class="panel">
            <p class="event-date">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
            <p class="event-time">{{ substr($invitation->start_time, 0, 5) }}@if ($invitation->end_time) - {{ substr($invitation->end_time, 0, 5) }}@endif WITA</p>
            <p class="venue">{{ $invitation->venue_name }}</p>
            <p class="address">{{ $invitation->venue_address }}</p>
            @if ($invitation->google_maps_url)
                <a class="button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener">Buka Google Maps</a>
            @endif
        </div>
    </section>

    <div class="countdown" data-target="{{ $invitation->event_date->format('Y-m-d') }}T{{ substr($invitation->start_time, 0, 5) }}:00">
        <div><b data-days>00</b><span>Hari</span></div>
        <div><b data-hours>00</b><span>Jam</span></div>
        <div><b data-minutes>00</b><span>Menit</span></div>
        <div><b data-seconds>00</b><span>Detik</span></div>
    </div>

    @if (count($gallery))
        <section class="section reveal">
            <p class="kicker">Galeri Bahagia</p>
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

    <section class="section reveal">
        <p class="thanks">Matur Suksma</p>
        <p class="closing-copy">Merupakan kehormatan dan kebahagiaan bagi kami apabila Anda berkenan hadir memberi doa restu.</p>
        @unless ($isPreview)
            <button id="share" class="share">Bagikan Undangan</button>
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
    }), { threshold: 0.14 });
    document.querySelectorAll('.reveal').forEach((node) => observer.observe(node));

    const counter = document.querySelector('.countdown');
    function updateCounter() {
        const distance = Math.max(0, new Date(counter.dataset.target).getTime() - Date.now());
        const parts = [
            Math.floor(distance / 86400000),
            Math.floor(distance / 3600000) % 24,
            Math.floor(distance / 60000) % 60,
            Math.floor(distance / 1000) % 60,
        ];
        ['days', 'hours', 'minutes', 'seconds'].forEach((part, index) => {
            counter.querySelector(`[data-${part}]`).textContent = String(parts[index]).padStart(2, '0');
        });
    }
    updateCounter();
    setInterval(updateCounter, 1000);

    const audio = document.querySelector('[data-audio]');
    const audioToggle = document.querySelector('[data-audio-toggle]');
    if (audioToggle) {
        audioToggle.addEventListener('click', () => {
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
        shareButton.addEventListener('click', async () => {
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
