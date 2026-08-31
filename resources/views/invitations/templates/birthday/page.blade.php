@php
    $isPreview = $isPreview ?? false;
    $photo = $invitation->celebrant_photo ? Storage::url($invitation->celebrant_photo) : null;
    $artwork = asset('images/birthday-celebration.svg');
    $musicPath = $invitation->music_type === 'default' ? $invitation->music?->file_path : ($invitation->music_type === 'upload' ? $invitation->music_file : null);
    $gallery = $invitation->gallery_photos ?? [];
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di perayaan ulang tahun '.$invitation->display_name.'. Buka undangan: '.url()->full();
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : 'Ulang Tahun '.$invitation->display_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/birthday-invitation.css') }}">
</head>
<body class="birthday birthday--{{ $birthdayTheme }}">
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Undangan Ulang Tahun',
        'openingImage' => $photo ?: $artwork,
        'openingAccent' => $birthdayTheme === 'ceria' ? '#ffd36c' : '#dac699',
        'openingShade' => $birthdayTheme === 'bali' ? 'rgba(12, 39, 32, .78)' : 'rgba(44, 28, 58, .68)',
        'openingHasMusic' => (bool) $musicPath,
    ])
    <main class="birthday-page">
        @if($isPreview)
            <aside class="demo-banner">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</aside>
        @endif
        <header class="birthday-hero">
            <div class="hero-copy">
                <p class="eyebrow">Satu hari, banyak cerita bahagia</p>
                <span class="birthday-ornament" aria-hidden="true">✦</span>
                <p class="intro">{{ $invitation->event_title ?: 'Mari rayakan ulang tahun' }}</p>
                <h1>{{ $invitation->display_name }}</h1>
                @if($invitation->celebrant_age)
                    <p class="age"><strong>{{ $invitation->celebrant_age }}</strong> tahun penuh cerita</p>
                @endif
                <p class="hero-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
            </div>
            <figure class="hero-photo">
                <img src="{{ $photo ?: $artwork }}" alt="{{ $photo ? 'Foto '.$invitation->display_name : 'Ilustrasi perayaan ulang tahun' }}" fetchpriority="high">
                @if($isPreview)<figcaption>Tambahkan foto sendiri saat membuat undangan.</figcaption>@endif
            </figure>
        </header>
        <section class="birthday-section greeting" data-reveal>
            <p class="eyebrow">Dengan penuh sukacita</p>
            <h2>{{ $invitation->celebrant_full_name }}</h2>
            <p>{{ $invitation->opening_quote ?: 'Kehadiran dan doa baik Anda akan membuat momen ini semakin berarti.' }}</p>
            @if($invitation->host_name)<p class="host">Mengundang dengan hangat,<br><strong>{{ $invitation->host_name }}</strong></p>@endif
        </section>
        <section class="birthday-section event-card" data-reveal>
            <p class="eyebrow">Catat hari bahagianya</p>
            <h2>{{ $invitation->event_title ?: 'Perayaan Ulang Tahun' }}</h2>
            <div class="event-details">
                <div><span>Tanggal acara</span><strong>{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</strong></div>
                <div><span>Waktu</span><strong>{{ substr($invitation->start_time, 0, 5) }}{{ $invitation->end_time ? ' - '.substr($invitation->end_time, 0, 5) : '' }} WITA</strong></div>
                <div><span>Tempat</span><strong>{{ $invitation->venue_name }}</strong><p>{{ $invitation->venue_address }}</p></div>
                @if($invitation->dress_code)<div><span>Dress code</span><strong>{{ $invitation->dress_code }}</strong></div>@endif
            </div>
            @if($invitation->google_maps_url)<a class="birthday-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
        </section>
        @if(count($gallery) || $isPreview)
            <section class="birthday-section" data-reveal>
                <p class="eyebrow">Kenangan kecil, bahagia besar</p><h2>Galeri cerita</h2>
                <div class="birthday-gallery">
                    @foreach($gallery as $image)
                        <img src="{{ Storage::url($image) }}" alt="Kenangan {{ $invitation->display_name }} {{ $loop->iteration }}" loading="lazy" decoding="async">
                    @endforeach
                    @if($isPreview && !count($gallery))
                        @foreach(['Cerita pertama', 'Momen kesayangan', 'Hari penuh tawa'] as $caption)
                            <figure><img src="{{ $artwork }}" alt="Ilustrasi galeri" loading="lazy"><figcaption>{{ $caption }} · foto Anda di sini</figcaption></figure>
                        @endforeach
                    @endif
                </div>
            </section>
        @endif
        @if($invitation->giftSetting?->is_active)
            @include('invitations.partials.wedding-gift')
        @endif
        <footer class="birthday-section closing" data-reveal>
            <p class="eyebrow">Sampai berjumpa</p><h2>Bahagia dirayakan bersama.</h2>
            <p>Terima kasih atas kehadiran dan doa baik Anda.</p>
            <button type="button" class="birthday-button" data-birthday-share>Bagikan undangan</button>
            @include('invitations.partials.app-credit')
        </footer>
    </main>
    @if($musicPath)
        <div class="birthday-music"><audio data-audio loop preload="none" src="{{ Storage::url($musicPath) }}"></audio><button type="button" data-audio-toggle aria-label="Putar musik">Play</button><span data-audio-message></span></div>
    @endif
    <script>
        (() => {
            const audio = document.querySelector('[data-audio]');
            const toggle = document.querySelector('[data-audio-toggle]');
            toggle?.addEventListener('click', async () => {
                if (!audio.paused) return audio.pause();
                try { await audio.play(); } catch { document.querySelector('[data-audio-message]').textContent = 'Musik belum dapat diputar.'; }
            });
            document.querySelector('[data-birthday-share]').addEventListener('click', async () => {
                const text = {{ Illuminate\Support\Js::from($shareText) }};
                if (navigator.share) { try { await navigator.share({ text }); } catch {} }
                else { window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank', 'noopener'); }
            });
            if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
                const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
                    if (entry.isIntersecting) { entry.target.classList.add('is-revealed'); observer.unobserve(entry.target); }
                }), { threshold: .1 });
                document.querySelectorAll('[data-reveal]').forEach((section) => observer.observe(section));
            }
        })();
    </script>
</body>
</html>
