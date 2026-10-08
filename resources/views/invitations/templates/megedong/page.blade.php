@php
    $isPreview = $isPreview ?? false;
    $motherPhoto = $invitation->bride_photo ? Storage::url($invitation->bride_photo) : null;
    $fatherPhoto = $invitation->groom_photo ? Storage::url($invitation->groom_photo) : null;
    $artwork = asset($megedongTheme === 'kencana' ? 'images/megedong-padma.svg' : 'images/megedong-padma-light.svg');
    $musicPath = $invitation->music_type === 'default' ? $invitation->music?->file_path : ($invitation->music_type === 'upload' ? $invitation->music_file : null);
    $gallery = $invitation->gallery_photos ?? [];
    // The cover always sits under a dark shade, so its fallback is the dark illustration in every theme.
    $coverImage = count($gallery) ? Storage::url($gallery[0]) : ($motherPhoto ?: asset('images/megedong-padma.svg'));
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di '.$invitation->occasion_phrase.'. Buka undangan: '.url()->full();
    $parents = [
        ['role' => 'Calon Ibu', 'name' => $invitation->bride_full_name, 'photo' => $motherPhoto],
        ['role' => 'Calon Ayah', 'name' => $invitation->groom_full_name, 'photo' => $fatherPhoto],
    ];
    $openingColors = [
        'kencana' => ['#d4ad61', 'rgba(24, 15, 9, .74)'],
        'padma' => ['#f3c4cf', 'rgba(74, 42, 52, .62)'],
        'tirta' => ['#cfe3d6', 'rgba(28, 46, 39, .66)'],
    ][$megedongTheme];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : 'Megedong-gedongan '.$invitation->display_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/megedong-invitation.css') }}">
</head>
<body class="megedong megedong--{{ $megedongTheme }}">
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $coverImage,
        'openingAccent' => $openingColors[0],
        'openingShade' => $openingColors[1],
        'openingHasMusic' => (bool) $musicPath,
    ])
    <main class="mg-page">
        @if($isPreview)
            <aside class="mg-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</aside>
        @endif
        <header class="mg-hero">
            <p class="mg-eyebrow">Om Swastyastu</p>
            <img class="mg-emblem" src="{{ $artwork }}" alt="" aria-hidden="true">
            <p class="mg-intro">Upacara Megedong-gedongan</p>
            <h1>{{ $invitation->display_name }}</h1>
            <p class="mg-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
        </header>
        <section class="mg-section mg-greeting" data-reveal>
            <p>{{ $invitation->opening_quote ?: 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Megedong-gedongan. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.' }}</p>
        </section>
        <section class="mg-section" data-reveal>
            <p class="mg-eyebrow">Yang berbahagia</p>
            <div class="mg-parents">
                @foreach($parents as $parent)
                    <article class="mg-parent">
                        @if($parent['photo'])
                            <img src="{{ $parent['photo'] }}" alt="Foto {{ $parent['name'] }}" loading="lazy" decoding="async">
                        @else
                            <span class="mg-initial" aria-hidden="true">{{ Illuminate\Support\Str::upper(Illuminate\Support\Str::substr((string) $parent['name'], 0, 1)) ?: '✦' }}</span>
                        @endif
                        <span class="mg-role">{{ $parent['role'] }}</span>
                        <h2>{{ $parent['name'] }}</h2>
                    </article>
                @endforeach
            </div>
            @if($invitation->pregnancy_age || $invitation->child_order)
                <dl class="mg-facts">
                    @if($invitation->pregnancy_age)<div><dt>Usia kandungan</dt><dd>{{ $invitation->pregnancy_age }}</dd></div>@endif
                    @if($invitation->child_order)<div><dt>Menantikan</dt><dd>{{ $invitation->child_order }}</dd></div>@endif
                </dl>
            @endif
        </section>
        <section class="mg-section mg-event" data-reveal>
            <p class="mg-eyebrow">Waktu dan tempat</p>
            <h2>Upacara Megedong-gedongan</h2>
            <div class="mg-details">
                <div><span>Hari, tanggal</span><strong>{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</strong></div>
                <div><span>Waktu</span><strong>{{ substr((string) $invitation->start_time, 0, 5) }}{{ $invitation->end_time ? ' - '.substr($invitation->end_time, 0, 5) : '' }} WITA</strong></div>
                <div><span>Tempat</span><strong>{{ $invitation->venue_name }}</strong><p>{{ $invitation->venue_address }}</p></div>
            </div>
            @if($invitation->google_maps_url)<a class="mg-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
        </section>
        @if(count($gallery) || $isPreview)
            <section class="mg-section" data-reveal>
                <p class="mg-eyebrow">Menanti dengan sukacita</p><h2>Galeri</h2>
                <div class="mg-gallery">
                    @foreach($gallery as $image)
                        <img src="{{ Storage::url($image) }}" alt="Foto {{ $invitation->display_name }} {{ $loop->iteration }}" loading="lazy" decoding="async">
                    @endforeach
                    @if($isPreview && !count($gallery))
                        @foreach(['Foto maternity', 'Bersama keluarga', 'Momen menanti'] as $caption)
                            <figure><img src="{{ $artwork }}" alt="Ilustrasi galeri" loading="lazy"><figcaption>{{ $caption }} · foto Anda di sini</figcaption></figure>
                        @endforeach
                    @endif
                </div>
            </section>
        @endif
        @if($invitation->giftSetting?->is_active)
            @include('invitations.partials.wedding-gift')
        @endif
        <footer class="mg-section mg-closing" data-reveal>
            <p>Atas kehadiran dan doa restu Bapak/Ibu/Saudara/i, kami mengucapkan terima kasih.</p>
            <h2>Om Shanti, Shanti, Shanti Om</h2>
            <p class="mg-family">Kami yang berbahagia,<br><strong>{{ $invitation->display_name }}</strong> beserta keluarga</p>
            <button type="button" class="mg-button" data-mg-share>Bagikan undangan</button>
            @include('invitations.partials.app-credit')
        </footer>
    </main>
    @if($musicPath)
        <div class="mg-music"><audio data-audio loop preload="none" src="{{ Storage::url($musicPath) }}"></audio><button type="button" data-audio-toggle aria-label="Putar musik">Play</button><span data-audio-message></span></div>
    @endif
    <script>
        (() => {
            const audio = document.querySelector('[data-audio]');
            const toggle = document.querySelector('[data-audio-toggle]');
            toggle?.addEventListener('click', async () => {
                if (!audio.paused) return audio.pause();
                try { await audio.play(); } catch { document.querySelector('[data-audio-message]').textContent = 'Musik belum dapat diputar.'; }
            });
            document.querySelector('[data-mg-share]').addEventListener('click', async () => {
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
