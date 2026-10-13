{{-- Lamak Prada (premium): cloth and gold. The photo sits in a jepun wreath between two hanging lamak, the couple are
     two tilted cards, their story runs down a centre line, and the gallery glides past as two film strips. --}}
@php
    extract(App\Support\PremiumWedding::data($invitation, $isPreview ?? false));
    $ornaments = 'invitations.templates.upacara-anak.ornaments.';
    // Enough photos to fill a moving strip; fewer are shown as a plain grid.
    $filmstrip = count($gallery) >= 4;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#fbf3e4">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : 'Undangan '.$couple }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&family=Pinyon+Script&display=swap" rel="stylesheet">
    @include('invitations.templates.premium.parts.base')
    <style>
        :root {
            --lp-cream: #fbf3e4;
            --lp-paper: #fffaf0;
            --lp-rose: #b65a4c;
            --lp-brick: #8e3d33;
            --lp-gold: #c0963f;
            --lp-gold-soft: #e2c684;
            --lp-ink: #3b302a;
            --lp-muted: #7a6a5c;
            --lp-check: #4a403a;
            --orn: #c0963f;
            --pm-accent: #e2c684;
            --pm-accent-ink: #8e3d33;
            --lp-caps: Cinzel, Georgia, serif;
            --lp-serif: 'Cormorant Garamond', Georgia, serif;
            --lp-script: 'Pinyon Script', 'Brush Script MT', cursive;
            --lp-lattice: repeating-linear-gradient(45deg, rgba(192, 150, 63, .11) 0 1.5px, transparent 1.5px 22px), repeating-linear-gradient(-45deg, rgba(192, 150, 63, .11) 0 1.5px, transparent 1.5px 22px);
            --lp-poleng: conic-gradient(var(--lp-check) 25%, #f6ecd9 0 50%, var(--lp-check) 0 75%, #f6ecd9 0) 0 0 / 16px 16px;
        }
        html { background: #e6d5b8; }
        body { background: linear-gradient(165deg, #e2cfae, #f6ecd9 50%, #dcc59e); color: var(--lp-ink); font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .lp-page { background: var(--lp-cream); box-shadow: 0 30px 80px rgba(90, 52, 30, .22); }
        .lp-eyebrow { color: var(--lp-gold); font-family: var(--lp-caps); font-size: 11px; font-weight: 600; letter-spacing: .32em; margin: 0; text-transform: uppercase; }
        .lp-script { color: var(--lp-rose); font-family: var(--lp-script); font-size: 42px; line-height: 1.1; margin: 0; }
        .lp-section { padding: 70px 24px; position: relative; text-align: center; }
        .lp-section h2 { font-family: var(--lp-caps); font-size: 24px; font-weight: 400; letter-spacing: .1em; line-height: 1.3; margin: 10px 0 0; text-transform: uppercase; }
        .lp-button { background: linear-gradient(135deg, #c96a5a, var(--lp-brick)); border: 0; border-radius: 999px; box-shadow: 0 12px 26px rgba(142, 61, 51, .3); color: #fff6ea; cursor: pointer; display: inline-flex; font: 600 13px Inter, system-ui, sans-serif; justify-content: center; letter-spacing: .06em; min-height: 48px; overflow: hidden; padding: 14px 26px; position: relative; text-decoration: none; }
        .lp-button::after { animation: lp-sheen 4.4s ease-in-out infinite; background: linear-gradient(110deg, transparent 35%, rgba(255, 255, 255, .42) 50%, transparent 65%); content: ''; inset: 0; position: absolute; transform: translateX(-120%); }
        .lp-button.is-quiet { background: transparent; border: 1px solid var(--lp-gold-soft); box-shadow: none; color: var(--lp-brick); }

        /* First screen: gold lattice, two lamak hanging at the sides, the photo in a wreath. */
        .lp-hero { animation: lp-lattice 40s linear infinite; background: radial-gradient(circle at 50% 34%, #fffaf0, rgba(255, 250, 240, 0) 62%), var(--lp-lattice), var(--lp-cream); padding: 58px 24px 56px; position: relative; text-align: center; }
        .lp-hero > :not(.orn):not(.pm-motes) { position: relative; }
        .lp-hero .orn-lamak { animation: lp-sway 6s ease-in-out infinite; position: absolute; top: -6px; transform-origin: 50% 0; width: 44px; }
        .lp-hero .orn-lamak.is-left { left: 14px; }
        .lp-hero .orn-lamak.is-right { animation-delay: -3s; right: 14px; }
        .lp-medal { aspect-ratio: 1; margin: 26px auto 0; width: min(78%, 310px); }
        .lp-medal .orn-karangan { animation: lp-breathe 9s ease-in-out infinite; inset: 0; position: absolute; width: 100%; }
        .lp-medal::before { animation: pm-spin 36s linear infinite; border: 1.5px dashed var(--lp-gold-soft); border-radius: 50%; content: ''; inset: 16%; position: absolute; }
        .lp-photo { background: linear-gradient(160deg, #fff, #f1dfbd); border: 5px solid #fffaf0; border-radius: 50%; box-shadow: 0 0 0 1.5px var(--lp-gold-soft), 0 20px 44px rgba(90, 52, 30, .26); inset: 21%; overflow: hidden; position: absolute; }
        .lp-photo img { animation: lp-drift 18s ease-in-out infinite alternate; height: 100%; object-fit: cover; width: 100%; }
        .lp-photo span { align-items: center; color: var(--lp-gold); display: flex; font-family: var(--lp-caps); font-size: 34px; height: 100%; justify-content: center; }
        .lp-hero h1 { font-family: var(--lp-caps); font-size: clamp(32px, 9.6vw, 46px); font-weight: 400; letter-spacing: .12em; line-height: 1.12; margin: 26px 0 0; text-transform: uppercase; text-wrap: balance; }
        .lp-hero h1 span { color: var(--lp-gold); display: block; font-family: var(--lp-script); font-size: 1.05em; letter-spacing: 0; line-height: .9; text-transform: none; }
        .lp-ribbon { background: linear-gradient(135deg, #c26655, var(--lp-brick)); color: #fff3e2; display: inline-block; font-family: var(--lp-caps); font-size: 12px; letter-spacing: .22em; margin: 26px 0 0; padding: 14px 22px; position: relative; text-transform: uppercase; }
        .lp-ribbon::before, .lp-ribbon::after { background: var(--lp-poleng); content: ''; height: 8px; left: 0; opacity: .9; position: absolute; right: 0; }
        .lp-ribbon::before { top: -8px; }
        .lp-ribbon::after { bottom: -8px; }
        .lp-guest { color: var(--lp-muted); font-size: 12px; margin: 30px 0 0; }
        .lp-guest strong { color: var(--lp-ink); display: block; font-family: var(--lp-serif); font-size: 23px; font-weight: 600; margin-top: 2px; overflow-wrap: anywhere; }

        .lp-greeting blockquote { color: var(--lp-muted); font-family: var(--lp-serif); font-size: 21px; font-style: italic; line-height: 1.6; margin: 18px auto 0; max-width: 400px; }

        /* The couple: two cards, tilted like cards laid on cloth. */
        .lp-couple { background: var(--lp-lattice), linear-gradient(180deg, var(--lp-cream), #f6e9d2 55%, var(--lp-cream)); }
        .lp-cards { display: grid; gap: 14px; grid-template-columns: 1fr 1fr; margin: 30px 0 0; position: relative; }
        .lp-kartu { background: var(--lp-paper); border: 1px solid var(--lp-gold-soft); border-radius: 18px; box-shadow: 0 18px 36px rgba(90, 52, 30, .18); padding: 9px 9px 18px; transform: rotate(-2.4deg); }
        .lp-kartu.is-bride { transform: rotate(2.4deg); }
        .js .lp-kartu[data-reveal].is-revealed { transform: rotate(-2.4deg); }
        .js .lp-kartu.is-bride[data-reveal].is-revealed { transform: rotate(2.4deg); }
        .lp-kartu figure { aspect-ratio: 3 / 4; background: linear-gradient(160deg, #fff, #f1dfbd); border-radius: 12px; margin: 0; overflow: hidden; }
        .lp-kartu figure img { height: 100%; object-fit: cover; width: 100%; }
        .lp-kartu.is-demo-groom figure img { transform: scale(2.7); transform-origin: 43% 74%; }
        .lp-kartu.is-demo-bride figure img { transform: scale(2.7); transform-origin: 57% 74%; }
        .lp-kartu figure span { align-items: center; color: var(--lp-gold); display: flex; font-family: var(--lp-caps); font-size: 52px; height: 100%; justify-content: center; }
        .lp-kartu .lp-eyebrow { font-size: 9px; letter-spacing: .22em; margin-top: 14px; }
        .lp-kartu h3 { font-family: var(--lp-serif); font-size: 21px; font-weight: 600; line-height: 1.15; margin: 6px 0 0; overflow-wrap: anywhere; }
        .lp-kartu p:not(.lp-eyebrow) { color: var(--lp-muted); font-size: 12px; line-height: 1.55; margin: 8px 0 0; }
        .lp-kartu p strong { color: var(--lp-ink); display: block; font-weight: 600; }
        .lp-amp { align-items: center; background: linear-gradient(135deg, #f3dfa6, var(--lp-gold)); border: 3px solid var(--lp-paper); border-radius: 50%; box-shadow: 0 10px 20px rgba(90, 52, 30, .24); color: #fffaf0; display: flex; font-family: var(--lp-script); font-size: 30px; height: 56px; justify-content: center; left: calc(50% - 28px); margin: 0; position: absolute; top: 30%; width: 56px; z-index: 1; }

        /* Their story on both sides of a gold line. */
        .lp-timeline { margin: 34px 0 0; position: relative; }
        .lp-timeline::before { background: linear-gradient(180deg, transparent, var(--lp-gold-soft) 8%, var(--lp-gold-soft) 92%, transparent); bottom: 0; content: ''; left: calc(50% - .75px); position: absolute; top: 0; width: 1.5px; }
        .lp-step { padding: 0 20px 28px 0; position: relative; text-align: right; width: 50%; }
        .lp-step:nth-child(even) { margin-left: 50%; padding: 0 0 28px 20px; text-align: left; }
        .lp-step::before { background: var(--lp-rose); border: 3px solid var(--lp-cream); border-radius: 2px; box-shadow: 0 0 0 1.5px var(--lp-gold-soft); content: ''; height: 13px; position: absolute; right: -6.5px; top: 6px; transform: rotate(45deg); width: 13px; }
        .lp-step:nth-child(even)::before { left: -6.5px; right: auto; }
        .lp-step time { color: var(--lp-gold); font-family: var(--lp-caps); font-size: 10px; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; }
        .lp-step h3 { font-family: var(--lp-serif); font-size: 21px; font-weight: 600; line-height: 1.15; margin: 4px 0 0; }
        .lp-step p { color: var(--lp-muted); font-size: 13px; line-height: 1.6; margin: 6px 0 0; }
        .lp-step img { border-radius: 14px; margin-top: 10px; }

        /* Countdown on a cloth band edged with poleng. */
        .lp-band { animation: lp-lattice 40s linear infinite; background: repeating-linear-gradient(45deg, rgba(240, 207, 138, .13) 0 1.5px, transparent 1.5px 22px), repeating-linear-gradient(-45deg, rgba(240, 207, 138, .13) 0 1.5px, transparent 1.5px 22px), linear-gradient(160deg, #c26655, var(--lp-brick)); color: #fff3e2; padding: 76px 22px; position: relative; text-align: center; }
        .lp-band::before, .lp-band::after { background: var(--lp-poleng); content: ''; height: 16px; left: 0; position: absolute; right: 0; }
        .lp-band::before { top: 0; }
        .lp-band::after { bottom: 0; }
        .lp-band > :not(.pm-motes) { position: relative; }
        .lp-band .lp-eyebrow { color: #f3dfa6; }
        .lp-band h2 { color: #fff8ea; font-family: var(--lp-caps); font-size: 24px; font-weight: 400; letter-spacing: .1em; margin: 10px 0 0; text-transform: uppercase; }
        .lp-tiles { display: grid; gap: 10px; grid-template-columns: repeat(4, 1fr); margin: 28px auto 0; max-width: 380px; }
        .lp-tiles div { background: linear-gradient(160deg, #fbeec4, #dcb765); border-radius: 12px; box-shadow: 0 10px 22px rgba(60, 24, 18, .28), inset 0 1px 0 #fff8e0; color: var(--lp-ink); padding: 14px 4px 11px; }
        .js .lp-tiles div { opacity: 0; transform: perspective(600px) rotateX(-80deg); transform-origin: top; transition: opacity .5s ease, transform .8s cubic-bezier(.3, 1.3, .5, 1); }
        .js .lp-band.is-revealed .lp-tiles div { opacity: 1; transform: none; }
        .js .lp-band.is-revealed .lp-tiles div:nth-child(2) { transition-delay: .12s; }
        .js .lp-band.is-revealed .lp-tiles div:nth-child(3) { transition-delay: .24s; }
        .js .lp-band.is-revealed .lp-tiles div:nth-child(4) { transition-delay: .36s; }
        .lp-tiles strong { display: block; font-family: var(--lp-caps); font-size: 27px; font-weight: 600; line-height: 1; }
        .lp-tiles span { color: var(--lp-brick); display: block; font-size: 9.5px; font-weight: 700; letter-spacing: .14em; margin-top: 6px; text-transform: uppercase; }
        .lp-band .lp-button { background: #fff6ea; box-shadow: none; color: var(--lp-brick); margin-top: 28px; }

        /* The ceremony between two gebogan. */
        .lp-offer { align-items: flex-end; display: flex; justify-content: center; margin: 26px 0 0; }
        .lp-offer .orn-gebogan { animation: pm-bob 6s ease-in-out infinite; flex: none; width: clamp(64px, 20vw, 92px); }
        .lp-offer .orn-gebogan.is-b { animation-delay: -3s; }
        .lp-day { margin: 0; padding: 0 12px 26px; }
        .lp-day span { color: var(--lp-muted); display: block; font-family: var(--lp-caps); font-size: 11px; letter-spacing: .2em; text-transform: uppercase; }
        .lp-day strong { color: var(--lp-brick); display: block; font-family: var(--lp-caps); font-size: clamp(58px, 18vw, 80px); font-weight: 400; line-height: 1; }
        .lp-details { background: var(--lp-paper); border: 1px solid var(--lp-gold-soft); border-radius: 22px; box-shadow: 0 18px 44px rgba(90, 52, 30, .14); margin: 4px 0 0; padding: 28px 22px; }
        .lp-details dl { margin: 0; }
        .lp-details div + div { border-top: 1px solid #f0e2c4; margin-top: 16px; padding-top: 16px; }
        .lp-details dt { color: var(--lp-gold); font-family: var(--lp-caps); font-size: 10px; font-weight: 600; letter-spacing: .26em; text-transform: uppercase; }
        .lp-details dd { font-family: var(--lp-serif); font-size: 23px; font-weight: 600; line-height: 1.25; margin: 6px 0 0; }
        .lp-details dd small { color: var(--lp-muted); display: block; font-family: Inter, system-ui, sans-serif; font-size: 13px; font-weight: 400; line-height: 1.6; margin-top: 6px; }
        .lp-details .lp-button { margin-top: 24px; }

        /* Gallery: two strips gliding in opposite directions. */
        .lp-gallery { padding-left: 0; padding-right: 0; }
        .lp-marquee { margin: 12px 0 0; overflow: hidden; }
        .lp-marquee:first-of-type { margin-top: 28px; }
        .lp-strip { animation: lp-marquee var(--t, 40s) linear infinite; display: flex; width: max-content; }
        .lp-marquee.is-back .lp-strip { animation-direction: reverse; }
        .lp-marquee:hover .lp-strip, .lp-marquee:active .lp-strip { animation-play-state: paused; }
        .lp-set { display: flex; gap: 10px; padding-right: 10px; }
        .lp-set img { border: 4px solid var(--lp-paper); border-radius: 14px; box-shadow: 0 10px 22px rgba(90, 52, 30, .18); height: 150px; max-width: none; width: auto; }
        .lp-grid { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; margin: 28px 24px 0; }
        .lp-grid img { aspect-ratio: 1; border: 4px solid var(--lp-paper); border-radius: 14px; box-shadow: 0 10px 22px rgba(90, 52, 30, .18); object-fit: cover; width: 100%; }
        .lp-hint { color: var(--lp-gold); font-family: var(--lp-caps); font-size: 10px; letter-spacing: .2em; margin: 18px 0 0; text-transform: uppercase; }

        .lp-closing { background: var(--lp-lattice), linear-gradient(180deg, var(--lp-cream), #f3e2c4); padding-bottom: 96px; }
        .lp-closing .orn-banten { animation: pm-bob 6s ease-in-out infinite; margin: 0 auto 16px; width: 104px; }
        .lp-closing > p:not(.lp-script):not(.lp-eyebrow) { color: var(--lp-muted); font-size: 14px; line-height: 1.75; margin: 16px auto 0; max-width: 380px; }
        .lp-closing .lp-button { margin-top: 26px; }
        .lp-credit { color: #a8957c !important; font-size: 12px !important; margin-top: 34px !important; }

        @keyframes lp-sway { 0%, 100% { transform: rotate(2.2deg); } 50% { transform: rotate(-2.2deg); } }
        @keyframes lp-breathe { 0%, 100% { transform: scale(1) rotate(-2deg); } 50% { transform: scale(1.035) rotate(2deg); } }
        @keyframes lp-drift { from { transform: scale(1.02); } to { transform: scale(1.12); } }
        @keyframes lp-lattice { to { background-position: 0 0, 124px 0, -124px 0, 0 0; } }
        @keyframes lp-sheen { 0%, 55% { transform: translateX(-120%); } 100% { transform: translateX(120%); } }
        @keyframes lp-marquee { to { transform: translateX(-50%); } }
        @media (prefers-reduced-motion: reduce) {
            .lp-marquee { overflow-x: auto; }
            .js .lp-tiles div { opacity: 1; transform: none; }
        }
        @media (max-width: 350px) {
            .lp-section { padding-left: 18px; padding-right: 18px; }
            .lp-kartu h3 { font-size: 18px; }
            .lp-step h3 { font-size: 18px; }
            .lp-tiles strong { font-size: 22px; }
        }
    </style>
</head>
<body>
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $mediaUrl($heroPhoto ?: $demoPhoto),
        'openingAccent' => '#f0d79f',
        'openingText' => '#fff8ea',
        'openingShade' => 'rgba(96, 36, 28, .55)',
        'openingFont' => 'Cinzel, Georgia, serif',
        'openingHasMusic' => (bool) ($musicPath && ! $isPreview),
    ])
    @include('invitations.templates.upacara-anak.parts.defs')
    <main class="pm-page lp-page">
        @if ($isPreview)
            <p class="pm-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</p>
        @endif

        <header class="lp-hero">
            @include($ornaments.'lamak', ['class' => 'is-left'])
            @include($ornaments.'lamak', ['class' => 'is-right'])
            <div class="pm-motes" aria-hidden="true">
                @foreach ([[20, 14], [38, 8], [61, 12], [80, 22], [24, 62], [50, 78], [76, 66], [90, 88], [12, 90], [44, 94]] as [$left, $top])
                    <i style="left: {{ $left }}%; top: {{ $top }}%; animation-delay: {{ $loop->index * 0.5 }}s"></i>
                @endforeach
            </div>
            <p class="lp-eyebrow" data-enter style="--i: 0">Om Swastyastu</p>
            <p class="lp-script" data-enter style="--i: 1">{{ $invitation->event_type }}</p>
            <div class="lp-medal" data-enter="bloom" style="--i: 2">
                @include($ornaments.'karangan')
                <div class="lp-photo">
                    @if ($heroPhoto)
                        <img src="{{ $mediaUrl($heroPhoto) }}" alt="Foto {{ $couple }}">
                    @else
                        <span>{{ mb_substr($invitation->groom_nickname, 0, 1) }} &amp; {{ mb_substr($invitation->bride_nickname, 0, 1) }}</span>
                    @endif
                </div>
            </div>
            <h1 data-enter style="--i: 4">{{ $invitation->groom_nickname }}<span>&amp;</span>{{ $invitation->bride_nickname }}</h1>
            <p class="lp-ribbon" data-enter style="--i: 5">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
            @if ($guest !== '')
                <p class="lp-guest" data-enter style="--i: 6">Kepada Yth.<strong>{{ $guest }}</strong></p>
            @endif
        </header>

        <section class="lp-section lp-greeting" data-reveal>
            @include($ornaments.'patra')
            <p class="lp-eyebrow">Atas asung kertha wara nugraha</p>
            <blockquote>{{ $invitation->opening_quote ?: 'Dengan restu keluarga, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu pada hari bahagia kami.' }}</blockquote>
        </section>

        <section class="lp-section lp-couple" aria-label="Mempelai">
            <p class="lp-script" data-reveal>Mempelai</p>
            <div class="lp-cards">
                <p class="lp-amp" aria-hidden="true" data-reveal="zoom" style="--d: .6s">&amp;</p>
                @foreach ([['Mempelai pria', 'Putra dari', 'groom', $groomPhoto], ['Mempelai wanita', 'Putri dari', 'bride', $bridePhoto]] as [$role, $childOf, $who, $photo])
                    <article class="lp-kartu is-{{ $who }} {{ $isPreview && ! $invitation->{$who.'_photo'} ? 'is-demo-'.$who : '' }}" data-reveal="flip" style="--d: {{ $loop->index * 0.25 }}s">
                        <figure>
                            @if ($photo)
                                <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $invitation->{$who.'_full_name'} }}" loading="lazy">
                            @else
                                <span>{{ mb_substr($invitation->{$who.'_nickname'}, 0, 1) }}</span>
                            @endif
                        </figure>
                        <p class="lp-eyebrow">{{ $role }}</p>
                        <h3>{{ $invitation->{$who.'_full_name'} }}</h3>
                        @if ($invitation->{$who.'_father_name'} || $invitation->{$who.'_mother_name'})
                            <p>{{ $childOf }}<strong>{{ collect([$invitation->{$who.'_father_name'}, $invitation->{$who.'_mother_name'}])->filter()->implode(' & ') }}</strong></p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        @if ($story)
            <section class="lp-section" aria-label="Kisah kami">
                <p class="lp-script" data-reveal>Kisah kami</p>
                <div class="lp-timeline">
                    @foreach ($story as $chapter)
                        <article class="lp-step" data-reveal="{{ $loop->odd ? 'left' : 'right' }}">
                            @if ($chapter['when'])<time>{{ $chapter['when'] }}</time>@endif
                            <h3>{{ $chapter['title'] }}</h3>
                            @if ($chapter['body'])<p>{{ $chapter['body'] }}</p>@endif
                            @if ($chapter['photo'])<img src="{{ $mediaUrl($chapter['photo']) }}" alt="{{ $chapter['title'] }}" loading="lazy">@endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="lp-band" data-reveal aria-label="Hitung mundur">
            <div class="pm-motes" aria-hidden="true">
                @foreach ([[8, 24], [22, 72], [41, 16], [60, 82], [77, 26], [92, 62]] as [$left, $top])
                    <i style="left: {{ $left }}%; top: {{ $top }}%; animation-delay: {{ $loop->index * 0.6 }}s"></i>
                @endforeach
            </div>
            <p class="lp-eyebrow">Menuju hari bahagia</p>
            <h2>{{ $invitation->event_date->translatedFormat('d F Y') }}</h2>
            <div class="lp-tiles" data-countdown="{{ $eventTarget }}">
                <div><strong data-days>0</strong><span>Hari</span></div>
                <div><strong data-hours>00</strong><span>Jam</span></div>
                <div><strong data-minutes>00</strong><span>Menit</span></div>
                <div><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
            <a class="lp-button" href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer">Simpan ke kalender</a>
        </section>

        <section class="lp-section" aria-label="Waktu dan tempat">
            <p class="lp-eyebrow" data-reveal>Waktu dan tempat</p>
            <h2 data-reveal>{{ $invitation->event_type }}</h2>
            <div class="lp-offer" data-reveal="zoom">
                @include($ornaments.'gebogan')
                <p class="lp-day">
                    <span>{{ $invitation->event_date->translatedFormat('l') }}</span>
                    <strong>{{ $invitation->event_date->translatedFormat('d') }}</strong>
                    <span>{{ $invitation->event_date->translatedFormat('F Y') }}</span>
                </p>
                @include($ornaments.'gebogan', ['class' => 'is-b'])
            </div>
            <div class="lp-details" data-reveal>
                <dl>
                    <div><dt>Waktu</dt><dd>{{ $startTime }}@if ($endTime) - {{ $endTime }}@endif WITA</dd></div>
                    <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
                </dl>
                @if ($invitation->google_maps_url)
                    <a class="lp-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>
                @endif
            </div>
        </section>

        @if (count($gallery))
            <section class="lp-section lp-gallery" aria-label="Galeri">
                <p class="lp-script" data-reveal>Momen kami</p>
                @if ($filmstrip)
                    @foreach (['is-front' => array_keys($gallery), 'is-back' => array_reverse(array_keys($gallery))] as $direction => $order)
                        <div class="lp-marquee {{ $direction }}" data-reveal>
                            <div class="lp-strip" style="--t: {{ count($gallery) * 7 }}s">
                                @foreach ([0, 1] as $copy)
                                    <div class="lp-set" @if ($copy) aria-hidden="true" @endif>
                                        @foreach ($order as $index)
                                            <img src="{{ $mediaUrl($gallery[$index]) }}" alt="{{ $copy ? '' : 'Foto '.$couple.' '.($index + 1) }}" decoding="async" @if ($direction === 'is-front' && ! $copy) data-gallery @else data-gallery-ref="{{ $index }}" @endif>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <p class="lp-hint">Ketuk foto untuk melihat lebih besar</p>
                @else
                    <div class="lp-grid">
                        @foreach ($gallery as $photo)
                            <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $couple }} {{ $loop->iteration }}" loading="lazy" data-reveal="zoom" data-gallery>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if (($isPreview || $invitation->giftSetting?->is_active) && $invitation->giftSetting)
            <div data-reveal>
                @include('invitations.partials.wedding-gift')
            </div>
        @endif

        <footer class="lp-section lp-closing" data-reveal>
            @include($ornaments.'banten')
            <p class="lp-script">Matur suksma</p>
            <h2>{{ $couple }}</h2>
            <p>Merupakan kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
            <p class="lp-eyebrow" style="margin-top: 22px">Om Shanti, Shanti, Shanti Om</p>
            @unless ($isPreview)
                <button class="lp-button is-quiet" type="button" data-share>Bagikan undangan</button>
            @endunless
            <p class="lp-credit">@include('invitations.partials.app-credit')</p>
        </footer>
    </main>
    @include('invitations.templates.premium.parts.behaviour')
</body>
</html>
