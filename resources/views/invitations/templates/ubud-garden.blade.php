@php
    $isPreview = $isPreview ?? false;
    $demoPhoto = 'templates/bali-preview/hero-couple.jpg';
    $demoGallery = ['templates/bali-preview/gallery-pavilion.jpg', 'templates/bali-preview/gallery-details.jpg', 'templates/bali-preview/gallery-evening.jpg'];
    $groomPhoto = $invitation->groom_photo ?: ($isPreview ? $demoPhoto : null);
    $bridePhoto = $invitation->bride_photo ?: ($isPreview ? $demoPhoto : null);
    $gallery = $isPreview ? $demoGallery : ($invitation->gallery_photos ?? []);
    $hero = $isPreview ? $demoGallery[0] : ($gallery[0] ?? $groomPhoto ?? $bridePhoto ?? 'templates/bali-preview/gallery-pavilion.jpg');
    $musicPath = $invitation->music_type === 'default' && $invitation->music ? $invitation->music->file_path : $invitation->music_file;
    $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di acara pernikahan kami. Buka undangan: '.url()->current();
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $isPreview ? 'Preview Ubud Garden' : 'Undangan '.$invitation->groom_nickname.' & '.$invitation->bride_nickname }}</title>
    @include('invitations.partials.mobile-viewport')
    <style>
        :root { --green:#294537; --sage:#647a61; --paper:#f8f3e8; --cream:#fffaf0; --gold:#ae8752; }
        * { box-sizing:border-box; } html { scroll-behavior:smooth; } body { background:#dcd8cc; color:var(--green); font-family:Arial,sans-serif; margin:0; }
        .banner { background:var(--green); color:#fbebca; font-size:13px; padding:13px; position:fixed; text-align:center; top:0; width:100%; z-index:20; }
        .page { background:var(--paper); margin:auto; max-width:min(560px, 100vw); min-height:100vh; overflow:hidden; position:relative; }
        .page:before { background:radial-gradient(circle,#c9bc9940 1px,transparent 1px); background-size:19px 19px; content:""; inset:0; opacity:.6; pointer-events:none; position:absolute; }
        .hero { min-height:100vh; padding:78px 26px 42px; position:relative; text-align:center; }
        .leaf { color:var(--sage); font-size:22px; letter-spacing:12px; margin:24px 0; }
        .label { color:var(--gold); font-size:11px; font-weight:bold; letter-spacing:.38em; margin:0 0 20px; text-transform:uppercase; }
        h1 { color:var(--green); font:normal clamp(46px,12vw,62px) Georgia,serif; line-height:1.07; margin:0 0 24px; }
        h1 span { color:var(--gold); display:block; font-size:.6em; margin:10px 0; }
        .hero-date { color:var(--sage); letter-spacing:.17em; margin-bottom:36px; }
        .arch { border-radius:18px; box-shadow:0 12px 34px #29453724; height:390px; object-fit:cover; width:100%; }
        .section { padding:68px 32px; position:relative; text-align:center; }
        .quote { background:var(--cream); border-radius:4px; box-shadow:0 8px 25px #40533d14; color:#506852; font:italic 18px Georgia,serif; line-height:1.9; padding:48px 31px; }
        .couple { display:grid; gap:22px; }
        .card { background:#fffdf5; border-radius:18px; padding:12px 14px 31px; }
        .card img { border-radius:14px; height:258px; object-fit:cover; width:100%; }
        .card h3 { color:var(--green); font:28px Georgia,serif; margin:24px 0 10px; }
        .card p { color:var(--sage); font-size:13px; line-height:1.7; margin:0; }
        .ceremony { background:#324c3e; color:#faf2df; padding:68px 30px; text-align:center; }
        .ceremony .label { color:#e7ce98; }
        .date-block { border-block:1px solid #e4c98c60; margin:30px 0; padding:26px 0; }
        .date-block b { display:block; font:44px Georgia,serif; margin-bottom:9px; }
        .ceremony p { color:#dfd5c2; line-height:1.8; }
        .map { background:#d7bc7d; border-radius:26px; color:#263c31; display:inline-block; font-weight:bold; margin-top:22px; padding:14px 28px; text-decoration:none; }
        .mosaic { display:grid; gap:14px; grid-template-columns:1fr 1fr; }
        .mosaic img { border-radius:14px; height:255px; object-fit:cover; width:100%; }
        .mosaic img:first-child { border-radius:16px; grid-column:span 2; height:260px; }
        .closing { padding:75px 34px 94px; text-align:center; }
        .closing h2 { color:var(--green); font:42px Georgia,serif; font-weight:400; margin:0 0 16px; }
        .closing p { color:var(--sage); line-height:1.8; }
        .share { background:var(--green); border:0; border-radius:30px; color:#fff2d8; margin-top:25px; padding:15px 29px; }
        .mark { color:#9b978a; font-size:11px; margin-top:54px; }
        .float-in { opacity:0; transform:translateY(18px); transition:.8s ease; }.float-in.seen { opacity:1; transform:none; }
        .player { align-items:center; background:#fff8e8; border:1px solid #dec998; border-radius:30px; bottom:18px; box-shadow:0 5px 22px #0002; display:flex; gap:11px; padding:8px 14px 8px 8px; position:fixed; right:15px; z-index:25; }
        .player button { background:var(--green); border:0; border-radius:50%; color:#fff; height:40px; width:40px; } .player small { color:var(--green); }
    </style>
</head>
<body>
@if($isPreview)<div class="banner">Preview dummy: Ubud Garden | Tema terang editorial</div>@endif
<main class="page">
    <section class="hero">
        <p class="label">Pawiwahan di Taman Ubud</p>
        <div class="leaf">&#10087; &#10087;</div>
        <h1>{{ $invitation->groom_nickname }} <span>&amp;</span> {{ $invitation->bride_nickname }}</h1>
        <p class="hero-date">{{ $invitation->event_date->translatedFormat('d F Y') }}</p>
        <img class="arch" src="{{ Storage::url($hero) }}" alt="Taman pernikahan Bali">
    </section>
    <section class="section float-in"><p class="label">Om Swastyastu</p><div class="quote">"{{ $invitation->opening_quote }}"</div></section>
    <section class="section float-in">
        <p class="label">Putra &amp; Putri Kami</p>
        <div class="couple">
            <article class="card">@if($groomPhoto)<img src="{{ Storage::url($groomPhoto) }}" alt="Mempelai pria">@endif<h3>{{ $invitation->groom_full_name }}</h3><p>Putra dari<br>{{ $invitation->groom_father_name }} &amp; {{ $invitation->groom_mother_name }}</p></article>
            <article class="card">@if($bridePhoto)<img src="{{ Storage::url($bridePhoto) }}" alt="Mempelai wanita">@endif<h3>{{ $invitation->bride_full_name }}</h3><p>Putri dari<br>{{ $invitation->bride_father_name }} &amp; {{ $invitation->bride_mother_name }}</p></article>
        </div>
    </section>
    <section class="ceremony float-in">
        <p class="label">{{ $invitation->event_type }}</p>
        <div class="date-block"><b>{{ $invitation->event_date->translatedFormat('d F') }}</b>{{ $invitation->event_date->translatedFormat('l, Y') }}</div>
        <p>{{ substr($invitation->start_time, 0, 5) }}@if($invitation->end_time) - {{ substr($invitation->end_time,0,5) }}@endif WITA<br><br><strong>{{ $invitation->venue_name }}</strong><br>{{ $invitation->venue_address }}</p>
        @if($invitation->google_maps_url)<a class="map" href="{{ $invitation->google_maps_url }}" target="_blank">Buka Google Maps</a>@endif
    </section>
    @if(count($gallery))
    <section class="section float-in"><p class="label">Galeri Bahagia</p><div class="mosaic">@foreach($gallery as $photo)<img src="{{ Storage::url($photo) }}" alt="Foto galeri pernikahan">@endforeach</div></section>
    @endif
    @if(($isPreview || $invitation->giftSetting?->is_active) && $invitation->giftSetting)
        @include('invitations.partials.wedding-gift')
    @endif
    <section class="closing float-in"><h2>Matur Suksma</h2><p>Dengan penuh kasih, kami menantikan kehadiran dan doa restu Anda.</p>@unless($isPreview)<button id="share" class="share">Bagikan Undangan</button>@endunless<p class="mark">Dibuat gratis dengan aplikasi Undangan Pernikahan Bali</p></section>
</main>
@if($musicPath && !$isPreview)<div class="player"><button data-audio-toggle>Play</button><small>Musik Undangan</small><audio data-audio loop preload="metadata" src="{{ Storage::url($musicPath) }}"></audio></div>@endif
<script>
    const seen = new IntersectionObserver((entries) => entries.forEach((entry) => entry.isIntersecting && entry.target.classList.add('seen')), {threshold:.15});
    document.querySelectorAll('.float-in').forEach((item) => seen.observe(item));
    const audio=document.querySelector('[data-audio]'), play=document.querySelector('[data-audio-toggle]');
    if(play) play.addEventListener('click',()=>{if(audio.paused){audio.play();play.textContent='Pause';}else{audio.pause();play.textContent='Play';}});
    const share=document.getElementById('share'); if(share) share.addEventListener('click',async()=>{const text=@json($shareText);if(navigator.share)return navigator.share({text,url:location.href});await navigator.clipboard.writeText(text);share.textContent='Link tersalin';});
</script>
</body>
</html>
