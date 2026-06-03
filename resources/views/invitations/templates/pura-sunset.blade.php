@php
    $isPreview = $isPreview ?? false;
    $demoPhoto = 'templates/bali-preview/hero-couple.jpg';
    $demoGallery = ['templates/bali-preview/gallery-evening.jpg', 'templates/bali-preview/gallery-details.jpg', 'templates/bali-preview/gallery-pavilion.jpg'];
    $groomPhoto = $invitation->groom_photo ?: ($isPreview ? $demoPhoto : null);
    $bridePhoto = $invitation->bride_photo ?: ($isPreview ? $demoPhoto : null);
    $gallery = $isPreview ? $demoGallery : ($invitation->gallery_photos ?? []);
    $hero = $isPreview ? $demoGallery[0] : ($gallery[0] ?? $groomPhoto ?? $bridePhoto ?? 'templates/bali-preview/gallery-evening.jpg');
    $musicPath = $invitation->music_type === 'default' && $invitation->music ? $invitation->music->file_path : $invitation->music_file;
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di acara pernikahan kami. Buka undangan: '.url()->current();
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $isPreview ? 'Preview Pura Sunset' : 'Undangan '.$invitation->groom_nickname.' & '.$invitation->bride_nickname }}</title>
    @include('invitations.partials.mobile-viewport')
    <style>
        * { box-sizing: border-box; } html { scroll-behavior: smooth; }
        body { background:#100b12; color:#f9eadf; font-family: Arial, sans-serif; margin:0; }
        .banner { background:#321d29; color:#ffceb0; font-size:13px; left:0; padding:13px; position:fixed; right:0; text-align:center; top:0; z-index:20; }
        .page { background:#1a1017; box-shadow:0 0 70px #000; margin:auto; max-width:min(520px, 100vw); overflow:hidden; }
        .cover { display:flex; flex-direction:column; height:100vh; justify-content:space-between; overflow:hidden; padding:74px 30px 48px; position:relative; text-align:center; }
        .cover img { animation:cinema 14s ease-in-out infinite alternate; height:100%; inset:0; object-fit:cover; position:absolute; width:100%; }
        .cover:after { background:linear-gradient(to bottom,rgba(20,9,16,.38),rgba(20,9,16,.4) 38%,#1a1017 100%); content:""; inset:0; position:absolute; }
        .cover > * { position:relative; z-index:1; }
        .small { color:#f1ac7e; font-size:11px; letter-spacing:.48em; margin:0; text-transform:uppercase; }
        h1 { color:#ffe5c9; font-family: Georgia, serif; font-size:51px; font-weight:400; line-height:1.18; margin:0; }
        h1 em { color:#e68d60; display:block; font-size:34px; font-style:normal; margin:10px; }
        .date { color:#e9c8b7; letter-spacing:.22em; margin:28px 0 0; }
        .opening { background:#241520; border:1px solid #7d4536; border-radius:999px; color:#ffd4b7; display:inline-block; margin-top:30px; padding:13px 28px; }
        .section { padding:68px 30px; text-align:center; }
        h2 { color:#f19b70; font-size:11px; font-weight:600; letter-spacing:.45em; margin:0 0 34px; text-transform:uppercase; }
        .profiles { display:grid; gap:38px; }
        .profile img { border-radius:50%; box-shadow:0 0 0 1px #bd7255,0 0 0 8px #22151d; height:158px; object-fit:cover; width:158px; }
        .profile h3 { color:#ffe5c9; font:32px Georgia,serif; margin:25px 0 10px; }
        .profile p { color:#c9aa9e; font-size:13px; line-height:1.8; margin:0; }
        .prayer { background:#28151f; border-block:1px solid #5e2e2f; color:#dac1b5; font:italic 17px Georgia,serif; line-height:2; }
        .event-card { background:linear-gradient(145deg,#3d201f,#23131c); border-radius:6px; padding:42px 24px; }
        .day { color:#f4aa7a; font:76px Georgia,serif; line-height:1; }
        .month { color:#f9eadf; font-size:15px; letter-spacing:.35em; margin:13px 0 28px; text-transform:uppercase; }
        .detail { color:#d0afa0; line-height:1.9; margin:0 0 26px; }
        .button { border:1px solid #df875f; color:#ffd4b7; display:inline-block; font-size:14px; padding:14px 27px; text-decoration:none; }
        .countdown { background:#f0a173; color:#26131c; display:grid; gap:4px; grid-template-columns:repeat(4,1fr); padding:30px 16px; }
        .countdown b { display:block; font:29px Georgia,serif; }
        .countdown span { font-size:10px; letter-spacing:.12em; text-transform:uppercase; }
        .gallery { display:grid; gap:8px; grid-template-columns:1fr 1fr; padding:0 10px 68px; }
        .gallery img { height:210px; object-fit:cover; width:100%; }
        .gallery img:first-child { grid-column:span 2; height:310px; }
        .veda { background:#24151e; color:#e9c9bb; font:italic 17px Georgia,serif; line-height:2; padding:65px 35px; text-align:center; }
        .closing { padding:70px 30px 95px; text-align:center; }
        .closing-title { color:#ffe0c3; font:36px Georgia,serif; margin:0 0 18px; }
        .muted { color:#c0a49a; font-size:14px; line-height:1.8; }
        .share { background:transparent; border:1px solid #df875f; color:#ffd4b7; margin-top:26px; padding:14px 25px; }
        .watermark { color:#725d58; font-size:11px; margin-top:52px; }
        .reveal { opacity:0; transform:translateY(30px); transition:.8s ease; } .reveal.visible { opacity:1; transform:none; }
        .player { align-items:center; background:#321d29; border:1px solid #794631; border-radius:30px; bottom:17px; display:flex; gap:12px; padding:8px 15px 8px 9px; position:fixed; right:15px; z-index:30; }
        .player button { background:#e88e63; border:0; border-radius:50%; color:#21121a; height:39px; width:39px; } .player small { color:#f7d5c1; }
        @keyframes cinema { to { transform:scale(1.12) translateY(-1.5%); } }
    </style>
</head>
<body>
@if ($isPreview)<div class="banner">Preview dummy: Pura Sunset | Lihat perjalanan undangan sampai akhir</div>@endif
<main class="page">
    <section class="cover">
        <img src="{{ Storage::url($hero) }}" alt="Suasana senja Bali">
        <p class="small">The wedding celebration</p>
        <div>
            <h1>{{ $invitation->groom_nickname }} <em>&amp;</em> {{ $invitation->bride_nickname }}</h1>
            <p class="date">{{ $invitation->event_date->translatedFormat('d . m . Y') }}</p>
            <span class="opening">Pawiwahan di Senja Bali</span>
        </div>
    </section>
    <section class="section reveal">
        <h2>Kedua Mempelai</h2>
        <div class="profiles">
            <article class="profile">
                @if ($groomPhoto)<img src="{{ Storage::url($groomPhoto) }}" alt="Foto mempelai pria">@endif
                <h3>{{ $invitation->groom_full_name }}</h3>
                <p>Putra dari<br>{{ $invitation->groom_father_name }} &amp; {{ $invitation->groom_mother_name }}</p>
            </article>
            <article class="profile">
                @if ($bridePhoto)<img src="{{ Storage::url($bridePhoto) }}" alt="Foto mempelai wanita">@endif
                <h3>{{ $invitation->bride_full_name }}</h3>
                <p>Putri dari<br>{{ $invitation->bride_father_name }} &amp; {{ $invitation->bride_mother_name }}</p>
            </article>
        </div>
    </section>
    <section class="section prayer reveal"><h2>Om Swastyastu</h2>"{{ $invitation->opening_quote }}"</section>
    <section class="section reveal">
        <h2>{{ $invitation->event_type }}</h2>
        <div class="event-card">
            <div class="day">{{ $invitation->event_date->format('d') }}</div>
            <div class="month">{{ $invitation->event_date->translatedFormat('F Y') }}</div>
            <p class="detail">{{ $invitation->event_date->translatedFormat('l') }} | {{ substr($invitation->start_time, 0, 5) }}@if ($invitation->end_time) - {{ substr($invitation->end_time, 0, 5) }}@endif WITA<br><br>{{ $invitation->venue_name }}<br>{{ $invitation->venue_address }}</p>
            @if ($invitation->google_maps_url)<a class="button" href="{{ $invitation->google_maps_url }}" target="_blank">Map Lokasi Acara</a>@endif
        </div>
    </section>
    <div class="countdown" data-target="{{ $invitation->event_date->format('Y-m-d') }}T{{ substr($invitation->start_time, 0, 5) }}:00">
        <div><b data-days>00</b><span>Hari</span></div><div><b data-hours>00</b><span>Jam</span></div><div><b data-minutes>00</b><span>Menit</span></div><div><b data-seconds>00</b><span>Detik</span></div>
    </div>
    <section class="veda reveal">"Ihaiva stam ma vi yaustam, visvam ayur vyasnutam."<br><small>Rg Veda X.85.42</small></section>
    @if (count($gallery))
        <section class="section reveal"><h2>Momen Bahagia</h2></section>
        <div class="gallery reveal">@foreach ($gallery as $photo)<img src="{{ Storage::url($photo) }}" alt="Momen bahagia">@endforeach</div>
    @endif
    @if (($isPreview || $invitation->giftSetting?->is_active) && $invitation->giftSetting)
        @include('invitations.partials.wedding-gift')
    @endif
    <section class="closing reveal">
        <p class="closing-title">Matur Suksma</p>
        <p class="muted">Merupakan kehormatan bagi kami apabila Anda berkenan hadir memberikan doa restu.</p>
        @unless($isPreview)<button id="share" class="share">Bagikan Undangan</button>@endunless
        <p class="watermark">Dibuat gratis dengan aplikasi Undangan Pernikahan Bali</p>
    </section>
</main>
@if ($musicPath && ! $isPreview)
    <div class="player"><button type="button" data-audio-toggle>Play</button><small>Musik Undangan</small><audio data-audio loop preload="metadata" src="{{ Storage::url($musicPath) }}"></audio></div>
@endif
<script>
    document.querySelectorAll('.reveal').forEach((node) => new IntersectionObserver((items) => items.forEach((item) => item.isIntersecting && item.target.classList.add('visible')), {threshold:.14}).observe(node));
    const counter = document.querySelector('.countdown');
    function count() {
        const distance = Math.max(0, new Date(counter.dataset.target).getTime() - Date.now());
        const parts = [Math.floor(distance / 86400000), Math.floor(distance / 3600000) % 24, Math.floor(distance / 60000) % 60, Math.floor(distance / 1000) % 60];
        ['days','hours','minutes','seconds'].forEach((part, i) => counter.querySelector(`[data-${part}]`).textContent = String(parts[i]).padStart(2, '0'));
    }
    count(); setInterval(count, 1000);
    const audio = document.querySelector('[data-audio]'), toggle = document.querySelector('[data-audio-toggle]');
    if (toggle) toggle.addEventListener('click', () => { if (audio.paused) { audio.play(); toggle.textContent='Pause'; } else { audio.pause(); toggle.textContent='Play'; } });
    const share = document.getElementById('share');
    if (share) share.addEventListener('click', async () => { const text=@json($shareText); if (navigator.share) return navigator.share({text,url:location.href}); await navigator.clipboard.writeText(text); share.textContent='Link tersalin'; });
</script>
</body>
</html>
