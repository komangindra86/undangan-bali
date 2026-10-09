@php
    // Shared page for ceremonies held for a baby or child; $anakTheme picks the design.
    $isPreview = $isPreview ?? false;
    $childPhoto = $invitation->child_photo ? Storage::url($invitation->child_photo) : null;
    $musicPath = $invitation->music_type === 'default' ? $invitation->music?->file_path : ($invitation->music_type === 'upload' ? $invitation->music_file : null);
    $gallery = $invitation->gallery_photos ?? [];
    // The cover always sits under a dark shade, so its fallback is the dark illustration in every theme.
    $coverImage = $childPhoto ?: (count($gallery) ? Storage::url($gallery[0]) : asset('images/upacara-anak-jepun.svg'));
    $ceremonyName = 'Upacara '.$invitation->ceremony_title;
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di '.$invitation->occasion_phrase.'. Buka undangan: '.url()->full();
    $parents = $invitation->groom_nickname.' & '.$invitation->bride_nickname;
    $openingColors = [
        'rare' => ['#e6c98a', 'rgba(52, 35, 18, .70)'],
        'jepun' => ['#f6d36b', 'rgba(74, 58, 22, .62)'],
        'kumara' => ['#bfe4e1', 'rgba(20, 52, 60, .68)'],
    ][$anakTheme];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : $invitation->ceremony_title.' '.$invitation->display_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/upacara-anak.css') }}">
</head>
<body class="anak anak--{{ $anakTheme }}">
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $coverImage,
        'openingAccent' => $openingColors[0],
        'openingShade' => $openingColors[1],
        'openingHasMusic' => (bool) $musicPath,
    ])
    <main class="ua-page">
        @if($isPreview)
            <aside class="ua-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</aside>
        @endif
        <header class="ua-hero">
            <p class="ua-eyebrow">Om Swastyastu</p>
            <svg class="ua-flower" viewBox="-100 -100 200 200" aria-hidden="true">
                <g class="ua-petals">
                    @foreach([0, 72, 144, 216, 288] as $angle)
                        <path transform="rotate({{ $angle }})" d="M0 0C-22-18-32-56-12-86 4-92 24-80 30-60 26-32 14-12 0 0Z"/>
                    @endforeach
                </g>
                <circle class="ua-flower-heart" r="22"/>
            </svg>
            <p class="ua-intro">{{ $ceremonyName }}</p>
            @if($invitation->ceremony_note)<p class="ua-note">{{ $invitation->ceremony_note }}</p>@endif
            <h1>{{ $invitation->display_name }}</h1>
            <p class="ua-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
        </header>
        <section class="ua-section ua-greeting" data-reveal>
            <p>{{ $invitation->opening_quote ?: 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan '.mb_strtolower($ceremonyName).' bagi buah hati kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.' }}</p>
        </section>
        <section class="ua-section" data-reveal>
            <p class="ua-eyebrow">Buah hati kami</p>
            <article class="ua-child">
                @if($childPhoto)
                    <img class="ua-child-photo" src="{{ $childPhoto }}" alt="Foto {{ $invitation->child_full_name ?: 'buah hati' }}" loading="lazy" decoding="async">
                @endif
                @if($invitation->child_full_name)
                    <h2>{{ $invitation->child_full_name }}</h2>
                @endif
                <p class="ua-relation">{{ $invitation->child_label }}@if($invitation->child_order) · {{ $invitation->child_order }}@endif dari pasangan</p>
                <p class="ua-parents">
                    <strong>{{ $invitation->groom_full_name }}</strong>
                    <span aria-hidden="true">&amp;</span>
                    <strong>{{ $invitation->bride_full_name }}</strong>
                </p>
                @if($invitation->child_birth_date)
                    <dl class="ua-facts"><div><dt>Lahir</dt><dd>{{ $invitation->child_birth_date->translatedFormat('d F Y') }}</dd></div></dl>
                @endif
            </article>
        </section>
        <section class="ua-section ua-event" data-reveal>
            <p class="ua-eyebrow">Waktu dan tempat</p>
            <h2>{{ $ceremonyName }}</h2>
            <div class="ua-details">
                <div><span>Hari, tanggal</span><strong>{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</strong></div>
                <div><span>Waktu</span><strong>{{ substr((string) $invitation->start_time, 0, 5) }}{{ $invitation->end_time ? ' - '.substr($invitation->end_time, 0, 5) : '' }} WITA</strong></div>
                <div><span>Tempat</span><strong>{{ $invitation->venue_name }}</strong><p>{{ $invitation->venue_address }}</p></div>
            </div>
            @if($invitation->google_maps_url)<a class="ua-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
        </section>
        @if(count($gallery) || $isPreview)
            <section class="ua-section" data-reveal>
                <p class="ua-eyebrow">Hari-hari pertama</p><h2>Galeri</h2>
                <div class="ua-gallery">
                    @foreach($gallery as $image)
                        <img src="{{ Storage::url($image) }}" alt="Foto {{ $invitation->display_name }} {{ $loop->iteration }}" loading="lazy" decoding="async">
                    @endforeach
                    @if($isPreview && !count($gallery))
                        @foreach(['Senyum pertama', 'Bersama ayah dan ibu', 'Keluarga besar'] as $caption)
                            <figure><div class="ua-placeholder" aria-hidden="true"></div><figcaption>{{ $caption }} · foto Anda di sini</figcaption></figure>
                        @endforeach
                    @endif
                </div>
            </section>
        @endif
        @if($invitation->giftSetting?->is_active)
            @include('invitations.partials.wedding-gift')
        @endif
        <footer class="ua-section ua-closing" data-reveal>
            <p>Atas kehadiran dan doa restu Bapak/Ibu/Saudara/i, kami mengucapkan terima kasih.</p>
            <h2>Om Shanti, Shanti, Shanti Om</h2>
            <p class="ua-family">Kami yang berbahagia,<br><strong>{{ $parents }}</strong> beserta keluarga</p>
            <button type="button" class="ua-button" data-ua-share>Bagikan undangan</button>
            @include('invitations.partials.app-credit')
        </footer>
    </main>
    @if($musicPath)
        <div class="ua-music"><audio data-audio loop preload="none" src="{{ Storage::url($musicPath) }}"></audio><button type="button" data-audio-toggle aria-label="Putar musik">Play</button><span data-audio-message></span></div>
    @endif
    <script>
        (() => {
            const audio = document.querySelector('[data-audio]');
            const toggle = document.querySelector('[data-audio-toggle]');
            toggle?.addEventListener('click', async () => {
                if (!audio.paused) return audio.pause();
                try { await audio.play(); } catch { document.querySelector('[data-audio-message]').textContent = 'Musik belum dapat diputar.'; }
            });
            document.querySelector('[data-ua-share]').addEventListener('click', async () => {
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
