@php
    $isPreview = $isPreview ?? false;
    $frame = 'templates/bali-heritage/bali-heritage-frame.jpg';
    $demoPhoto = 'templates/bali-preview/hero-couple.jpg';
    $demoGallery = [
        'templates/bali-preview/gallery-details.jpg',
        'templates/bali-preview/gallery-pavilion.jpg',
        'templates/bali-preview/gallery-evening.jpg',
    ];
    $groomPhoto = $invitation->groom_photo ?: ($isPreview ? $demoPhoto : null);
    $bridePhoto = $invitation->bride_photo ?: ($isPreview ? $demoPhoto : null);
    $gallery = $isPreview ? $demoGallery : ($invitation->gallery_photos ?? []);
    $musicPath = $invitation->music_type === 'default' && $invitation->music
        ? $invitation->music->file_path
        : $invitation->music_file;
    $mediaUrl = static function (string $path): string {
        $url = Storage::url($path);

        return parse_url($url, PHP_URL_PATH) ?: $url;
    };
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di acara pernikahan kami. Buka undangan: '.url()->current();
    $startTime = substr((string) $invitation->start_time, 0, 5);
    $endTime = $invitation->end_time ? substr((string) $invitation->end_time, 0, 5) : null;
    $eventTarget = $invitation->event_date->format('Y-m-d').'T'.($startTime ?: '00:00').':00+08:00';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#284238">
    <title>{{ $isPreview ? 'Preview Puspa Kencana' : 'Undangan '.$invitation->groom_nickname.' & '.$invitation->bride_nickname }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Parisienne&display=swap" rel="stylesheet">
    <script>document.documentElement.classList.add('js');</script>
    @include('invitations.partials.mobile-viewport')
    <style>
        :root {
            --heritage-ink: #352916;
            --heritage-muted: #756849;
            --heritage-paper: #fff8ea;
            --heritage-paper-deep: #efe0c0;
            --heritage-gold: #b88a33;
            --heritage-green: #284238;
            --heritage-terracotta: #9b4f35;
            --heritage-line: rgba(74, 54, 24, .18);
        }
        * { box-sizing: border-box; }
        html { background: #e5dac4; scroll-behavior: smooth; }
        body {
            background: radial-gradient(circle at 15% 12%, rgba(187, 142, 56, .18), transparent 28rem), linear-gradient(135deg, #d7c7a9 0%, #f4ecd9 46%, #c9b48d 100%);
            color: var(--heritage-ink);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        .heritage-page {
            background: var(--heritage-paper);
            box-shadow: 0 24px 60px rgba(44, 34, 17, .18);
            margin: 0 auto;
            max-width: 430px;
            overflow: hidden;
            width: 100%;
        }
        .preview-banner {
            background: var(--heritage-green);
            color: #fff0c9;
            font-size: 12px;
            left: 50%;
            max-width: 430px;
            padding: 11px 16px;
            position: fixed;
            text-align: center;
            top: 0;
            transform: translateX(-50%);
            width: 100%;
            z-index: 40;
        }
        .heritage-hero {
            align-items: end;
            background: #d8c5a3;
            color: #fffaf0;
            display: grid;
            min-height: 100svh;
            overflow: hidden;
            padding: {{ $isPreview ? '72px' : '46px' }} 24px 48px;
            position: relative;
            text-align: center;
        }
        .heritage-hero-art {
            animation: heritage-cover-drift 16s ease-in-out infinite alternate;
            background-image: linear-gradient(to bottom, rgba(53, 41, 22, .04), rgba(53, 41, 22, .12) 55%, rgba(53, 41, 22, .45)), url("{{ $mediaUrl($frame) }}");
            background-position: center;
            background-size: cover;
            inset: -3%;
            position: absolute;
            transform: scale(1.035);
            will-change: transform;
            z-index: 0;
        }
        .heritage-hero::before,
        .heritage-hero::after {
            background: linear-gradient(90deg, transparent, rgba(255, 248, 234, .85), transparent);
            content: "";
            height: 1px;
            left: 24px;
            position: absolute;
            right: 24px;
            z-index: 2;
        }
        .heritage-hero::before { top: 30px; }
        .heritage-hero::after { bottom: 26px; }
        .heritage-hero-content { display: grid; gap: 15px; justify-items: center; position: relative; width: 100%; z-index: 2; }
        .js .heritage-hero-content > * {
            animation: heritage-hero-reveal .75s cubic-bezier(.2, .75, .25, 1) forwards;
            opacity: 0;
            transform: translateY(18px);
        }
        .js .heritage-hero-content > :nth-child(2) { animation-delay: .12s; }
        .js .heritage-hero-content > :nth-child(3) { animation-delay: .22s; }
        .js .heritage-hero-content > :nth-child(4) { animation-delay: .32s; }
        .js .heritage-hero-content > :nth-child(5) { animation-delay: .42s; }
        .js .heritage-hero-content > :nth-child(6) { animation-delay: .52s; }
        .heritage-eyebrow,
        .heritage-kicker {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .25em;
            text-transform: uppercase;
        }
        .heritage-script { font: 400 34px/1 Parisienne, cursive; }
        .heritage-page h1,
        .heritage-page h2,
        .heritage-page h3 { font-family: "Cormorant Garamond", Georgia, serif; letter-spacing: 0; margin: 0; }
        .heritage-page h1 { font-size: clamp(46px, 15vw, 64px); line-height: .9; max-width: 340px; text-transform: uppercase; }
        .heritage-page h2 { font-size: 37px; line-height: 1; }
        .heritage-page h3 { font-size: 25px; }
        .heritage-page p { color: var(--heritage-muted); font-size: 14px; line-height: 1.65; margin: 0; }
        .heritage-hero p { color: rgba(255, 250, 240, .92); }
        .heritage-guest {
            backdrop-filter: blur(10px);
            background: rgba(53, 41, 22, .24);
            border: 1px solid rgba(255, 248, 234, .45);
            max-width: 310px;
            padding: 10px 14px;
            width: 100%;
        }
        .heritage-guest strong { display: block; font: 700 23px/1.2 "Cormorant Garamond", Georgia, serif; }
        .heritage-button {
            align-items: center;
            background: linear-gradient(135deg, #cda95a, #9f7228);
            border: 0;
            border-radius: 999px;
            box-shadow: 0 12px 24px rgba(44, 32, 12, .28);
            color: #fffaf0;
            cursor: pointer;
            display: inline-flex;
            font: 700 13px/1 Inter, sans-serif;
            justify-content: center;
            min-height: 46px;
            padding: 0 22px;
            text-decoration: none;
            transition: box-shadow .25s ease, transform .25s ease;
        }
        .heritage-button:hover,
        .heritage-button:focus-visible { box-shadow: 0 15px 30px rgba(44, 32, 12, .36); transform: translateY(-2px); }
        .heritage-button:active { transform: translateY(0) scale(.98); }
        .heritage-section { padding: 58px 24px; position: relative; }
        .heritage-section + .heritage-section { border-top: 1px solid var(--heritage-line); }
        .heritage-intro {
            background: linear-gradient(rgba(255, 248, 234, .93), rgba(255, 248, 234, .93)), repeating-linear-gradient(45deg, rgba(187, 142, 56, .15) 0 1px, transparent 1px 12px);
            text-align: center;
        }
        .heritage-ornament { color: var(--heritage-gold); font: 600 20px/1 "Cormorant Garamond", Georgia, serif; letter-spacing: .15em; }
        .heritage-quote { color: var(--heritage-ink) !important; font: 500 22px/1.4 "Cormorant Garamond", Georgia, serif !important; margin: 20px auto 0 !important; max-width: 330px; }
        .heritage-couple { background: #f8edda; display: grid; gap: 26px; }
        .heritage-person { text-align: center; }
        .heritage-portrait {
            background: linear-gradient(145deg, var(--heritage-green), #172720);
            border: 5px solid #fff7e6;
            border-radius: 14px;
            box-shadow: 0 14px 32px rgba(53, 41, 22, .18);
            display: grid;
            height: 250px;
            margin: 0 auto 22px;
            max-width: 300px;
            overflow: hidden;
            place-items: center;
        }
        .heritage-portrait img { display: block; height: 100%; min-height: 0; min-width: 0; object-fit: cover; transition: transform 1.1s cubic-bezier(.2, .75, .25, 1); width: 100%; }
        .heritage-portrait img.heritage-demo-photo { object-position: center 68%; }
        .heritage-initial { color: #fff8ea; font: 700 68px/1 "Cormorant Garamond", Georgia, serif; }
        .heritage-person h2 { margin: 8px 0 10px; }
        .heritage-amp { animation: heritage-float 3.8s ease-in-out infinite; color: var(--heritage-gold); font: 400 52px/1 Parisienne, cursive; text-align: center; }
        .heritage-date-band {
            background-image: linear-gradient(rgba(40, 66, 56, .9), rgba(40, 66, 56, .9)), url("{{ $mediaUrl($frame) }}");
            background-position: center 58%;
            background-size: cover;
            color: #fff8ea;
            text-align: center;
        }
        .heritage-date-band h2 { margin-top: 9px; }
        .heritage-countdown { display: grid; gap: 7px; grid-template-columns: repeat(4, 1fr); margin-top: 26px; }
        .heritage-timebox { background: rgba(255, 248, 234, .1); border: 1px solid rgba(255, 248, 234, .25); min-width: 0; padding: 12px 4px 10px; }
        .heritage-timebox strong { display: block; font: 600 28px/1 "Cormorant Garamond", Georgia, serif; }
        .heritage-timebox span { font-size: 9px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .heritage-events { background: var(--heritage-paper); display: grid; gap: 15px; }
        .heritage-event { background: #fffaf0; border: 1px solid var(--heritage-line); padding: 23px; }
        .heritage-event .heritage-kicker { color: var(--heritage-terracotta); }
        .heritage-event h3 { margin: 8px 0 12px; }
        .heritage-meta { color: var(--heritage-muted); display: grid; font-size: 13px; gap: 7px; line-height: 1.55; }
        .heritage-map { color: var(--heritage-ink); margin: 4px auto 0; }
        .heritage-gallery { background: #eadabd; }
        .heritage-gallery h2 { margin-top: 7px; }
        .heritage-gallery-grid { display: grid; gap: 8px; grid-template-columns: 1fr 1fr; margin-top: 22px; }
        .heritage-gallery-grid img { border: 4px solid #fff8ea; height: 170px; object-fit: cover; transition: opacity .7s ease, transform .8s cubic-bezier(.2, .75, .25, 1); width: 100%; }
        .heritage-gallery-grid img:nth-child(3n) { grid-column: span 2; height: 230px; }
        .heritage-closing {
            background-image: linear-gradient(rgba(45, 36, 18, .56), rgba(45, 36, 18, .75)), url("{{ $mediaUrl($frame) }}");
            background-position: center bottom;
            background-size: cover;
            color: #fff8ea;
            display: grid;
            gap: 18px;
            min-height: 62svh;
            place-content: center;
            text-align: center;
        }
        .heritage-closing p { color: rgba(255, 248, 234, .88); }
        .heritage-share { margin: 4px auto 0; }
        .heritage-watermark { font-size: 11px !important; margin-top: 24px !important; }
        .heritage-player {
            align-items: center;
            backdrop-filter: blur(12px);
            background: rgba(40, 66, 56, .88);
            border: 1px solid rgba(255, 248, 234, .5);
            border-radius: 999px;
            bottom: 16px;
            color: #fff8ea;
            display: flex;
            gap: 10px;
            padding: 7px 14px 7px 7px;
            position: fixed;
            right: max(16px, calc((100vw - 430px) / 2 + 16px));
            z-index: 30;
        }
        .heritage-player button { background: #fff8ea; border: 0; border-radius: 50%; color: var(--heritage-green); cursor: pointer; font-weight: 700; height: 40px; width: 40px; }
        .heritage-player small { font-size: 11px; }
        .js .heritage-reveal { opacity: 0; transform: translateY(22px); transition: opacity .8s ease, transform .8s ease; }
        .js .heritage-reveal.is-visible { opacity: 1; transform: none; }
        .js .heritage-couple .heritage-portrait { opacity: 0; transform: translateY(18px) scale(.96); transition: opacity .7s ease, transform .8s cubic-bezier(.2, .75, .25, 1); }
        .js .heritage-couple.is-visible .heritage-portrait { opacity: 1; transform: none; }
        .js .heritage-couple.is-visible .heritage-person:last-child .heritage-portrait { transition-delay: .16s; }
        .js .heritage-couple.is-visible .heritage-portrait img { transform: scale(1.035); }
        .js .heritage-date-band .heritage-timebox { opacity: 0; transform: translateY(12px) scale(.94); transition: opacity .55s ease, transform .65s cubic-bezier(.2, .75, .25, 1); }
        .js .heritage-date-band.is-visible .heritage-timebox { opacity: 1; transform: none; }
        .js .heritage-date-band.is-visible .heritage-timebox:nth-child(2) { transition-delay: .08s; }
        .js .heritage-date-band.is-visible .heritage-timebox:nth-child(3) { transition-delay: .16s; }
        .js .heritage-date-band.is-visible .heritage-timebox:nth-child(4) { transition-delay: .24s; }
        .js .heritage-gallery .heritage-gallery-grid img { opacity: 0; transform: translateY(14px) scale(.95); }
        .js .heritage-gallery.is-visible .heritage-gallery-grid img { opacity: 1; transform: none; }
        .js .heritage-gallery.is-visible .heritage-gallery-grid img:nth-child(2) { transition-delay: .1s; }
        .js .heritage-gallery.is-visible .heritage-gallery-grid img:nth-child(3) { transition-delay: .2s; }
        @keyframes heritage-cover-drift {
            from { transform: scale(1.035) translate3d(0, 0, 0); }
            to { transform: scale(1.09) translate3d(0, -1.2%, 0); }
        }
        @keyframes heritage-hero-reveal {
            to { opacity: 1; transform: none; }
        }
        @keyframes heritage-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .heritage-hero-art,
            .heritage-amp,
            .js .heritage-hero-content > *,
            .js .heritage-reveal,
            .js .heritage-couple .heritage-portrait,
            .js .heritage-date-band .heritage-timebox,
            .js .heritage-gallery .heritage-gallery-grid img { animation: none; opacity: 1; transform: none; transition: none; }
        }
        @media (max-width: 370px) {
            .heritage-section { padding-left: 20px; padding-right: 20px; }
            .heritage-page h1 { font-size: 45px; }
            .heritage-timebox strong { font-size: 24px; }
        }
    </style>
</head>
<body>
@if ($isPreview)
    <div class="preview-banner">Preview dummy: Puspa Kencana | Ivory, puspa, dan kilau emas Bali</div>
@endif
<main class="heritage-page page">
    <header class="heritage-hero">
        <div class="heritage-hero-art" aria-hidden="true"></div>
        <div class="heritage-hero-content">
            <div class="heritage-eyebrow">{{ $invitation->event_type }}</div>
            <div class="heritage-script">The Wedding Of</div>
            <h1>{{ $invitation->groom_nickname }} &amp; {{ $invitation->bride_nickname }}</h1>
            <p>{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
            <div class="heritage-guest">Kepada Yth.<strong>Tamu Undangan</strong></div>
            <a class="heritage-button" href="#undangan">Buka Undangan</a>
        </div>
    </header>

    <section class="heritage-section heritage-intro heritage-reveal" id="undangan">
        <div class="heritage-ornament">OM SWASTYASTU</div>
        <p class="heritage-quote">{{ $invitation->opening_quote }}</p>
    </section>

    <section class="heritage-section heritage-couple heritage-reveal" aria-label="Profil mempelai">
        <article class="heritage-person">
            <div class="heritage-portrait groom">
                @if ($groomPhoto)
                    <img class="{{ $isPreview ? 'heritage-demo-photo' : '' }}" src="{{ $mediaUrl($groomPhoto) }}" alt="Foto {{ $invitation->groom_full_name }}">
                @else
                    <span class="heritage-initial">{{ mb_substr($invitation->groom_nickname, 0, 1) }}</span>
                @endif
            </div>
            <div class="heritage-kicker">Mempelai Pria</div>
            <h2>{{ $invitation->groom_full_name }}</h2>
            <p>Putra dari<br>{{ $invitation->groom_father_name }} &amp; {{ $invitation->groom_mother_name }}</p>
        </article>
        <div class="heritage-amp">&amp;</div>
        <article class="heritage-person">
            <div class="heritage-portrait bride">
                @if ($bridePhoto)
                    <img class="{{ $isPreview ? 'heritage-demo-photo' : '' }}" src="{{ $mediaUrl($bridePhoto) }}" alt="Foto {{ $invitation->bride_full_name }}">
                @else
                    <span class="heritage-initial">{{ mb_substr($invitation->bride_nickname, 0, 1) }}</span>
                @endif
            </div>
            <div class="heritage-kicker">Mempelai Wanita</div>
            <h2>{{ $invitation->bride_full_name }}</h2>
            <p>Putri dari<br>{{ $invitation->bride_father_name }} &amp; {{ $invitation->bride_mother_name }}</p>
        </article>
    </section>

    <section class="heritage-section heritage-date-band heritage-reveal" aria-label="Hitung mundur">
        <div class="heritage-kicker">Save The Date</div>
        <h2>{{ $invitation->event_date->translatedFormat('d F Y') }}</h2>
        <div class="heritage-countdown" data-countdown="{{ $eventTarget }}">
            <div class="heritage-timebox"><strong data-days>0</strong><span>Hari</span></div>
            <div class="heritage-timebox"><strong data-hours>0</strong><span>Jam</span></div>
            <div class="heritage-timebox"><strong data-minutes>0</strong><span>Menit</span></div>
            <div class="heritage-timebox"><strong data-seconds>0</strong><span>Detik</span></div>
        </div>
    </section>

    <section class="heritage-section heritage-events heritage-reveal" aria-label="Detail acara">
        <article class="heritage-event">
            <div class="heritage-kicker">{{ $invitation->event_type }}</div>
            <h3>{{ $invitation->event_date->translatedFormat('l, d F Y') }}</h3>
            <div class="heritage-meta">
                <span>{{ $startTime }}@if ($endTime) - {{ $endTime }}@endif WITA</span>
                <strong>{{ $invitation->venue_name }}</strong>
                <span>{{ $invitation->venue_address }}</span>
            </div>
        </article>
        @if ($invitation->google_maps_url)
            <a class="heritage-button heritage-map" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>
        @endif
    </section>

    @if (count($gallery))
        <section class="heritage-section heritage-gallery heritage-reveal" aria-label="Galeri">
            <div class="heritage-kicker">Our Gallery</div>
            <h2>Momen Kami</h2>
            <div class="heritage-gallery-grid">
                @foreach ($gallery as $photo)
                    <img src="{{ $mediaUrl($photo) }}" alt="Foto galeri {{ $invitation->groom_nickname }} dan {{ $invitation->bride_nickname }}" loading="lazy">
                @endforeach
            </div>
        </section>
    @endif

    @if (($isPreview || $invitation->giftSetting?->is_active) && $invitation->giftSetting)
        <div class="heritage-reveal">
            @include('invitations.partials.wedding-gift')
        </div>
    @endif

    <section class="heritage-section heritage-closing heritage-reveal">
        <div class="heritage-script">Matur Suksma</div>
        <h2>{{ $invitation->groom_nickname }} &amp; {{ $invitation->bride_nickname }}</h2>
        <p>Merupakan kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
        @unless ($isPreview)
            <button class="heritage-button heritage-share" type="button" data-share>Bagikan Undangan</button>
        @endunless
        <p class="heritage-watermark">@include('invitations.partials.app-credit')</p>
    </section>
</main>

@if ($musicPath && ! $isPreview)
    <div class="heritage-player">
        <button type="button" data-audio-toggle aria-label="Putar musik">Play</button>
        <small>Musik Undangan</small>
        <audio data-audio loop preload="metadata" src="{{ $mediaUrl($musicPath) }}"></audio>
    </div>
@endif

<script>
    (() => {
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const revealItems = document.querySelectorAll('.heritage-reveal');
        if (reducedMotion || !('IntersectionObserver' in window)) {
            revealItems.forEach((item) => item.classList.add('is-visible'));
        } else {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { threshold: .12 });
            revealItems.forEach((item) => observer.observe(item));
        }

        const countdown = document.querySelector('[data-countdown]');
        if (countdown) {
            const target = new Date(countdown.dataset.countdown).getTime();
            const fields = {
                days: countdown.querySelector('[data-days]'),
                hours: countdown.querySelector('[data-hours]'),
                minutes: countdown.querySelector('[data-minutes]'),
                seconds: countdown.querySelector('[data-seconds]'),
            };
            const updateCountdown = () => {
                const remaining = Math.max(0, target - Date.now());
                fields.days.textContent = Math.floor(remaining / 86400000);
                fields.hours.textContent = String(Math.floor(remaining / 3600000) % 24).padStart(2, '0');
                fields.minutes.textContent = String(Math.floor(remaining / 60000) % 60).padStart(2, '0');
                fields.seconds.textContent = String(Math.floor(remaining / 1000) % 60).padStart(2, '0');
            };
            updateCountdown();
            window.setInterval(updateCountdown, 1000);
        }

        const audio = document.querySelector('[data-audio]');
        const audioToggle = document.querySelector('[data-audio-toggle]');
        if (audio && audioToggle) {
            audioToggle.addEventListener('click', async () => {
                if (audio.paused) {
                    try {
                        await audio.play();
                        audioToggle.textContent = 'Pause';
                    } catch (error) {
                        audioToggle.textContent = 'Play';
                    }
                    return;
                }
                audio.pause();
                audioToggle.textContent = 'Play';
            });
        }

        const shareButton = document.querySelector('[data-share]');
        if (shareButton) {
            shareButton.addEventListener('click', async () => {
                const text = @json($shareText);
                if (navigator.share) {
                    await navigator.share({ text, url: window.location.href });
                    return;
                }
                await navigator.clipboard.writeText(text);
                shareButton.textContent = 'Link tersalin';
            });
        }
    })();
</script>
</body>
</html>
