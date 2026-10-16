{{-- Ukir Kencana: brick, carved wood and gold. A kori of red brick with a stepped crown holds the couple's photo
     behind carved doors that swing open, oil lamps flicker at its foot, the countdown and the ceremony are written on
     lontar leaves, and the gallery hangs in gold frames. Free designs leave out the couple's story. --}}
@php
    extract(App\Support\PremiumWedding::data($invitation, $isPreview ?? false));
    $ornaments = 'invitations.templates.upacara-anak.ornaments.';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#fbf2e6">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : 'Undangan '.$couple }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Italianno&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    @include('invitations.templates.premium.parts.base')
    <style>
        :root {
            --uk-cream: #fbf2e6;
            --uk-paper: #fffaf2;
            --uk-brick: #a64a33;
            --uk-brick-deep: #84351f;
            --uk-paras: #eadfc9;
            --uk-gold: #c29a3e;
            --uk-gold-soft: #e6cb8b;
            --uk-wood: #6b3d1f;
            --uk-lontar: #ecdcae;
            --uk-ink: #3b2a20;
            --uk-muted: #7d6553;
            --orn: #c29a3e;
            --mote: #ffe1a6;
            --pm-accent: #e6cb8b;
            --pm-accent-ink: #84351f;
            --uk-serif: 'Playfair Display', Georgia, serif;
            --uk-script: Italianno, 'Brush Script MT', cursive;
            --uk-bricks: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='28'%3E%3Cg fill='none' stroke='%23f4dcc2' stroke-opacity='.5' stroke-width='2'%3E%3Cpath d='M0 1h40M0 15h40M1 0v14M21 14v14'/%3E%3C/g%3E%3C/svg%3E"), linear-gradient(170deg, #b8573d, var(--uk-brick) 45%, var(--uk-brick-deep));
        }
        html { background: #e8d6bf; }
        body { background: linear-gradient(165deg, #e2ccb0, #f7ecdd 50%, #dcc3a2); color: var(--uk-ink); font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .uk-page { background: var(--uk-cream); box-shadow: 0 30px 80px rgba(110, 50, 30, .22); }
        .uk-eyebrow { color: var(--uk-gold); font-size: 11px; font-weight: 700; letter-spacing: .32em; margin: 0; text-transform: uppercase; }
        .uk-script { color: var(--uk-brick); font-family: var(--uk-script); font-size: 54px; line-height: 1; margin: 0; }
        .uk-section { padding: 70px 24px; position: relative; text-align: center; }
        .uk-section h2 { font-family: var(--uk-serif); font-size: 30px; font-weight: 600; line-height: 1.2; margin: 8px 0 0; }
        .uk-button { background: linear-gradient(135deg, #d0a650, #a77f2c); border: 0; border-radius: 999px; box-shadow: 0 12px 26px rgba(150, 100, 30, .3); color: #fffaf0; cursor: pointer; display: inline-flex; font: 600 13px Inter, system-ui, sans-serif; justify-content: center; letter-spacing: .06em; min-height: 48px; overflow: hidden; padding: 14px 26px; position: relative; text-decoration: none; }
        .uk-button::after { animation: uk-sheen 4.4s ease-in-out infinite; background: linear-gradient(110deg, transparent 35%, rgba(255, 255, 255, .5) 50%, transparent 65%); content: ''; inset: 0; position: absolute; transform: translateX(-120%); }
        .uk-button.is-quiet { background: transparent; border: 1px solid var(--uk-gold-soft); box-shadow: none; color: var(--uk-brick-deep); }

        /* First screen: the kori with its doors, lamps at its foot, marigold petals drifting down. */
        .uk-hero { background: radial-gradient(circle at 50% 36%, #fff5e4, rgba(255, 245, 228, 0) 58%), var(--uk-cream); overflow: hidden; padding: 50px 22px 54px; position: relative; text-align: center; }
        .uk-hero > :not(.pm-motes):not(.uk-petals) { position: relative; }
        .uk-kori { margin: 30px auto 0; padding-top: 58px; position: relative; width: min(74%, 290px); }
        .uk-crown { left: 50%; position: absolute; top: 0; transform: translateX(-50%); width: 100%; }
        .uk-crown i { background: var(--uk-bricks); border-bottom: 5px solid var(--uk-paras); display: block; height: 18px; margin: 0 auto; }
        .uk-crown i:nth-child(1) { width: 34%; }
        .uk-crown i:nth-child(2) { width: 60%; }
        .uk-crown i:nth-child(3) { width: 86%; }
        .uk-crown b { background: radial-gradient(circle at 40% 35%, #fbe7a8, var(--uk-gold) 60%, #8f6a22); border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%; display: block; height: 20px; left: calc(50% - 8px); position: absolute; top: -16px; width: 16px; }
        .uk-frame { background: var(--uk-bricks); border-radius: 6px; box-shadow: 0 30px 60px rgba(110, 50, 30, .3); padding: 16px 16px 0; }
        .uk-opening { aspect-ratio: 3 / 3.9; background: linear-gradient(160deg, #fff, var(--uk-paras)); border: 4px solid var(--uk-paras); border-bottom: 0; overflow: hidden; perspective: 900px; position: relative; }
        .uk-opening img { animation: uk-drift 18s ease-in-out infinite alternate; height: 100%; object-fit: cover; width: 100%; }
        .uk-opening span { align-items: center; color: var(--uk-gold); display: flex; font-family: var(--uk-serif); font-size: 44px; height: 100%; justify-content: center; }
        .uk-door { background: repeating-linear-gradient(90deg, rgba(0, 0, 0, .05) 0 3px, transparent 3px 9px), linear-gradient(90deg, #5a3218, #7c4826 50%, #5a3218); bottom: 0; display: none; position: absolute; top: 0; width: 50%; }
        .js .uk-door { display: block; }
        .uk-door::before { border: 1.5px solid rgba(230, 203, 139, .75); border-radius: 4px; box-shadow: inset 0 0 0 5px rgba(0, 0, 0, .12), inset 0 0 0 6px rgba(230, 203, 139, .4); content: ''; inset: 9% 14% 9% 14%; position: absolute; }
        .uk-door::after { background: radial-gradient(circle, #fbe7a8, var(--uk-gold) 60%, #8f6a22); border-radius: 50%; content: ''; height: 12px; position: absolute; top: calc(50% - 6px); width: 12px; }
        .uk-door.is-left { border-right: 1px solid #3a1f0d; left: 0; transform-origin: left center; }
        .uk-door.is-left::after { right: 8px; }
        .uk-door.is-right { border-left: 1px solid #3a1f0d; right: 0; transform-origin: right center; }
        .uk-door.is-right::after { left: 8px; }
        html.js:not(.invitation-pending) .uk-door.is-left { animation: uk-open-left 2s cubic-bezier(.6, .05, .3, 1) 1.1s both; }
        html.js:not(.invitation-pending) .uk-door.is-right { animation: uk-open-right 2s cubic-bezier(.6, .05, .3, 1) 1.1s both; }
        .uk-step { background: var(--uk-paras); height: 12px; margin: 0 -10px; }
        .uk-step + .uk-step { background: #dccdb1; height: 10px; margin: 0 -22px; }
        .uk-lamp { bottom: -2px; position: absolute; width: 40px; }
        .uk-lamp.is-left { left: -50px; }
        .uk-lamp.is-right { right: -50px; }
        .uk-lamp::before { background: radial-gradient(ellipse at 50% 20%, #d9905e, #9c4f2a 70%); border-radius: 0 0 50% 50% / 0 0 100% 100%; content: ''; display: block; height: 14px; width: 100%; }
        .uk-lamp i { animation: uk-flicker .16s ease-in-out infinite alternate; background: radial-gradient(circle at 50% 72%, #fffbd6 0 18%, #ffc845 42%, #ff8a24 70%, rgba(255, 120, 30, 0) 72%); border-radius: 50% 50% 46% 46% / 64% 64% 36% 36%; bottom: 12px; box-shadow: 0 0 22px 10px rgba(255, 176, 70, .32); height: 24px; left: calc(50% - 7px); position: absolute; transform-origin: 50% 100%; width: 14px; }
        .uk-hero h1 { font-family: var(--uk-serif); font-size: clamp(40px, 11.6vw, 56px); font-weight: 600; line-height: 1.05; margin: 34px 0 0; text-wrap: balance; }
        .uk-hero h1 span { color: var(--uk-gold); display: block; font-family: var(--uk-script); font-size: .9em; font-weight: 400; line-height: .9; }
        .uk-ribbon { border-bottom: 1px solid var(--uk-gold-soft); border-top: 1px solid var(--uk-gold-soft); color: var(--uk-brick-deep); display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: .24em; margin: 18px 0 0; padding: 10px 8px; text-transform: uppercase; }
        .uk-guest { color: var(--uk-muted); font-size: 12px; margin: 24px 0 0; }
        .uk-guest strong { color: var(--uk-ink); display: block; font-family: var(--uk-serif); font-size: 22px; font-weight: 600; margin-top: 2px; overflow-wrap: anywhere; }
        .uk-petals { inset: 0; overflow: hidden; pointer-events: none; position: absolute; z-index: 3; }
        .uk-petals i { animation: uk-fall linear infinite; background: radial-gradient(ellipse at 35% 30%, #ffd66b, #f39a1e 60%, #d9701a); border-radius: 60% 40% 60% 40%; height: 10px; opacity: 0; position: absolute; top: -6%; width: 7px; }

        .uk-greeting p:not(.uk-eyebrow) { color: var(--uk-muted); font-family: var(--uk-serif); font-size: 19px; font-style: italic; line-height: 1.7; margin: 16px auto 0; max-width: 400px; }

        /* The couple: two oval portraits in gold, side by side. */
        .uk-couple { background: linear-gradient(180deg, var(--uk-cream), #f5e6d2 55%, var(--uk-cream)); }
        .uk-pair { display: grid; gap: 16px; grid-template-columns: 1fr 1fr; margin: 28px 0 0; }
        .uk-oval { aspect-ratio: 3 / 4; background: linear-gradient(160deg, #fff, var(--uk-paras)); border-radius: 50%; box-shadow: 0 0 0 5px var(--uk-paper), 0 0 0 7px var(--uk-gold-soft), 0 0 0 9px var(--uk-paper), 0 0 0 10px var(--uk-gold), 0 22px 40px rgba(110, 50, 30, .22); margin: 12px auto 0; overflow: hidden; width: 86%; }
        .uk-oval img { height: 100%; object-fit: cover; width: 100%; }
        .uk-oval.is-demo-groom img { transform: scale(2.7); transform-origin: 43% 74%; }
        .uk-oval.is-demo-bride img { transform: scale(2.7); transform-origin: 57% 74%; }
        .uk-oval span { align-items: center; color: var(--uk-gold); display: flex; font-family: var(--uk-serif); font-size: 48px; height: 100%; justify-content: center; }
        .uk-pair .uk-eyebrow { font-size: 9px; letter-spacing: .22em; margin-top: 24px; }
        .uk-pair h3 { font-family: var(--uk-serif); font-size: 19px; font-weight: 600; line-height: 1.2; margin: 6px 0 0; overflow-wrap: anywhere; }
        .uk-pair p:not(.uk-eyebrow) { color: var(--uk-muted); font-size: 12px; line-height: 1.55; margin: 8px 0 0; }
        .uk-pair p strong { color: var(--uk-ink); display: block; font-weight: 600; }

        /* Countdown on lontar leaves against the brick wall. */
        .uk-wall { background: var(--uk-bricks); color: #fff3e2; padding: 70px 22px; position: relative; text-align: center; }
        .uk-wall > :not(.pm-motes) { position: relative; }
        .uk-wall .uk-eyebrow { color: #f6dc9e; }
        .uk-wall h2 { color: #fff8ec; font-family: var(--uk-serif); font-size: 30px; font-weight: 600; margin: 8px 0 0; }
        .uk-lontars { display: grid; gap: 12px; margin: 26px auto 0; max-width: 340px; }
        .uk-lontar { align-items: center; background: linear-gradient(180deg, #f3e6bd, var(--uk-lontar) 60%, #d9c48c); border-radius: 40px / 50%; box-shadow: 0 10px 22px rgba(60, 20, 10, .3), inset 0 0 0 1px rgba(120, 90, 40, .25); color: var(--uk-ink); display: flex; justify-content: space-between; padding: 12px 34px; position: relative; }
        .uk-lontar::before, .uk-lontar::after { background: #9b7a3e; border-radius: 50%; content: ''; height: 7px; position: absolute; top: calc(50% - 3.5px); width: 7px; }
        .uk-lontar::before { left: 16px; }
        .uk-lontar::after { right: 16px; }
        .uk-lontar strong { font-family: var(--uk-serif); font-size: 28px; font-weight: 600; line-height: 1; }
        .uk-lontar span { color: var(--uk-brick-deep); font-size: 11px; font-weight: 700; letter-spacing: .24em; text-transform: uppercase; }
        .js .uk-lontar { opacity: 0; transform: translateX(-60px) rotate(-3deg); transition: opacity .6s ease, transform .9s cubic-bezier(.2, .75, .25, 1); }
        .js .uk-lontar:nth-child(even) { transform: translateX(60px) rotate(3deg); }
        .js .uk-wall.is-revealed .uk-lontar { opacity: 1; transform: none; }
        .js .uk-wall.is-revealed .uk-lontar:nth-child(2) { transition-delay: .12s; }
        .js .uk-wall.is-revealed .uk-lontar:nth-child(3) { transition-delay: .24s; }
        .js .uk-wall.is-revealed .uk-lontar:nth-child(4) { transition-delay: .36s; }
        .uk-wall .uk-button { margin-top: 28px; }

        /* The ceremony written on a long lontar leaf. */
        .uk-leaf { background: linear-gradient(180deg, #f6ebc8, var(--uk-lontar) 60%, #dcc792); border-radius: 46px / 22px; box-shadow: 0 20px 44px rgba(110, 50, 30, .18), inset 0 0 0 1px rgba(120, 90, 40, .25); margin: 28px auto 0; max-width: 410px; padding: 34px 26px; position: relative; }
        .uk-leaf::before { background: #9b7a3e; border-radius: 50%; content: ''; height: 9px; left: calc(50% - 4.5px); position: absolute; top: 14px; width: 9px; }
        .uk-leaf .uk-day { color: var(--uk-brick-deep); font-family: var(--uk-serif); font-size: 60px; font-weight: 600; line-height: 1; margin: 8px 0 0; }
        .uk-leaf .uk-month { color: var(--uk-muted); font-size: 12px; font-weight: 700; letter-spacing: .24em; margin: 8px 0 0; text-transform: uppercase; }
        .uk-leaf dl { border-top: 1px solid rgba(120, 90, 40, .25); margin: 20px 0 0; padding-top: 18px; }
        .uk-leaf dl div + div { margin-top: 16px; }
        .uk-leaf dt { color: var(--uk-brick); font-size: 10px; font-weight: 700; letter-spacing: .26em; text-transform: uppercase; }
        .uk-leaf dd { font-family: var(--uk-serif); font-size: 21px; font-weight: 600; line-height: 1.3; margin: 6px 0 0; }
        .uk-leaf dd small { color: var(--uk-muted); display: block; font-family: Inter, system-ui, sans-serif; font-size: 13px; font-weight: 400; line-height: 1.6; margin-top: 6px; }
        .uk-leaf .uk-button { margin-top: 22px; }

        /* Gallery: photos hung in gold frames. */
        .uk-frames { display: grid; gap: 16px; grid-template-columns: 1fr 1fr; margin: 30px 0 0; }
        .uk-frames figure { background: var(--uk-paper); box-shadow: 0 0 0 3px var(--uk-gold), 0 0 0 6px var(--uk-paper), 0 0 0 7px var(--uk-gold-soft), 0 16px 30px rgba(110, 50, 30, .22); margin: 7px; padding: 6px; }
        .uk-frames figure:first-child { grid-column: 1 / -1; }
        .uk-frames img { aspect-ratio: 1; object-fit: cover; width: 100%; }
        .uk-frames figure:first-child img { aspect-ratio: 16 / 10; }

        .uk-closing { background: linear-gradient(180deg, var(--uk-cream), #f1dfc6); padding-bottom: 96px; }
        .uk-closing .uk-lamps { display: flex; gap: 90px; justify-content: center; margin: 0 0 22px; }
        .uk-closing .uk-lamp { bottom: auto; left: auto; position: relative; right: auto; }
        .uk-closing > p:not(.uk-script):not(.uk-eyebrow) { color: var(--uk-muted); font-size: 14px; line-height: 1.75; margin: 16px auto 0; max-width: 380px; }
        .uk-closing .uk-button { margin-top: 26px; }
        .uk-credit { color: #a48b74 !important; font-size: 12px !important; margin-top: 34px !important; }

        @keyframes uk-open-left { 0% { opacity: 1; transform: rotateY(0); } 80% { opacity: 1; } 100% { opacity: 0; transform: rotateY(-108deg); } }
        @keyframes uk-open-right { 0% { opacity: 1; transform: rotateY(0); } 80% { opacity: 1; } 100% { opacity: 0; transform: rotateY(108deg); } }
        @keyframes uk-flicker { 0% { transform: scale(1, 1) skewX(0); opacity: .95; } 50% { transform: scale(.94, 1.06) skewX(2deg); } 100% { transform: scale(1.05, .95) skewX(-2deg); opacity: 1; } }
        @keyframes uk-drift { from { transform: scale(1.02); } to { transform: scale(1.1); } }
        @keyframes uk-sheen { 0%, 55% { transform: translateX(-120%); } 100% { transform: translateX(120%); } }
        @keyframes uk-fall {
            0% { opacity: 0; top: -6%; transform: translateX(0) rotate(0); }
            10% { opacity: .95; }
            50% { transform: translateX(26px) rotate(200deg); }
            90% { opacity: .9; }
            100% { opacity: 0; top: 104%; transform: translateX(-12px) rotate(380deg); }
        }
        @media (prefers-reduced-motion: reduce) {
            .uk-door, .js .uk-door, .uk-petals { display: none; }
            .js .uk-lontar { opacity: 1; transform: none; }
        }
        @media (max-width: 350px) {
            .uk-section { padding-left: 18px; padding-right: 18px; }
            .uk-lamp.is-left { left: -42px; }
            .uk-lamp.is-right { right: -42px; }
            .uk-pair h3 { font-size: 17px; }
        }
    </style>
</head>
<body>
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $mediaUrl($heroPhoto ?: $demoPhoto),
        'openingAccent' => '#f3d58f',
        'openingText' => '#fff8ec',
        'openingShade' => 'rgba(100, 38, 22, .55)',
        'openingFont' => "'Playfair Display', Georgia, serif",
        'openingHasMusic' => (bool) ($musicPath && ! $isPreview),
    ])
    @include('invitations.templates.upacara-anak.parts.defs')
    <main class="pm-page uk-page">
        @if ($isPreview)
            <p class="pm-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</p>
        @endif

        <header class="uk-hero">
            <div class="pm-motes" aria-hidden="true">
                @foreach ([[10, 20], [30, 10], [70, 12], [90, 24], [16, 60], [84, 58], [40, 88], [62, 80]] as [$left, $top])
                    <i style="left: {{ $left }}%; top: {{ $top }}%; animation-delay: {{ $loop->index * 0.5 }}s"></i>
                @endforeach
            </div>
            <div class="uk-petals" aria-hidden="true">
                @foreach ([[8, 11, 0], [22, 14, 3], [41, 12, 6.5], [58, 15, 1.5], [74, 13, 8], [90, 16, 4.5], [32, 17, 10]] as [$left, $duration, $delay])
                    <i style="left: {{ $left }}%; animation-duration: {{ $duration }}s; animation-delay: {{ $delay }}s"></i>
                @endforeach
            </div>
            <p class="uk-eyebrow" data-enter style="--i: 0">Om Swastyastu</p>
            <p class="uk-script" data-enter style="--i: 1">{{ $invitation->event_type }}</p>
            <div class="uk-kori" data-enter="bloom" style="--i: 2">
                <div class="uk-crown" aria-hidden="true"><b></b><i></i><i></i><i></i></div>
                <div class="uk-frame">
                    <div class="uk-opening">
                        @if ($heroPhoto)
                            <img src="{{ $mediaUrl($heroPhoto) }}" alt="Foto {{ $couple }}">
                        @else
                            <span>{{ mb_substr($invitation->groom_nickname, 0, 1) }} &amp; {{ mb_substr($invitation->bride_nickname, 0, 1) }}</span>
                        @endif
                        <div class="uk-door is-left" aria-hidden="true"></div>
                        <div class="uk-door is-right" aria-hidden="true"></div>
                    </div>
                </div>
                <div class="uk-step" aria-hidden="true"></div>
                <div class="uk-step" aria-hidden="true"></div>
                <div class="uk-lamp is-left" aria-hidden="true"><i></i></div>
                <div class="uk-lamp is-right" aria-hidden="true"><i></i></div>
            </div>
            <h1 data-enter style="--i: 6">{{ $invitation->groom_nickname }}<span>&amp;</span>{{ $invitation->bride_nickname }}</h1>
            <p class="uk-ribbon" data-enter style="--i: 7">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
            @if ($guest !== '')
                <p class="uk-guest" data-enter style="--i: 8">Kepada Yth.<strong>{{ $guest }}</strong></p>
            @endif
        </header>

        <section class="uk-section uk-greeting" data-reveal>
            @include($ornaments.'patra')
            <p class="uk-eyebrow">Atas asung kertha wara nugraha</p>
            <p>{{ $invitation->opening_quote ?: 'Dengan restu keluarga, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu pada hari bahagia kami.' }}</p>
        </section>

        <section class="uk-section uk-couple" aria-label="Mempelai">
            <p class="uk-script" data-reveal>Mempelai</p>
            <div class="uk-pair">
                @foreach ([['Mempelai pria', 'Putra dari', 'groom', $groomPhoto], ['Mempelai wanita', 'Putri dari', 'bride', $bridePhoto]] as [$role, $childOf, $who, $photo])
                    <article data-reveal style="--d: {{ $loop->index * 0.2 }}s">
                        <div class="uk-oval {{ $isPreview && ! $invitation->{$who.'_photo'} ? 'is-demo-'.$who : '' }}">
                            @if ($photo)
                                <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $invitation->{$who.'_full_name'} }}" loading="lazy">
                            @else
                                <span>{{ mb_substr($invitation->{$who.'_nickname'}, 0, 1) }}</span>
                            @endif
                        </div>
                        <p class="uk-eyebrow">{{ $role }}</p>
                        <h3>{{ $invitation->{$who.'_full_name'} }}</h3>
                        @if ($invitation->{$who.'_father_name'} || $invitation->{$who.'_mother_name'})
                            <p>{{ $childOf }}<strong>{{ collect([$invitation->{$who.'_father_name'}, $invitation->{$who.'_mother_name'}])->filter()->implode(' & ') }}</strong></p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section class="uk-wall" data-reveal aria-label="Hitung mundur">
            <div class="pm-motes" aria-hidden="true">
                @foreach ([[8, 24], [24, 70], [44, 14], [64, 84], [80, 30], [93, 66]] as [$left, $top])
                    <i style="left: {{ $left }}%; top: {{ $top }}%; animation-delay: {{ $loop->index * 0.6 }}s"></i>
                @endforeach
            </div>
            <p class="uk-eyebrow">Menuju hari bahagia</p>
            <h2>{{ $invitation->event_date->translatedFormat('d F Y') }}</h2>
            <div class="uk-lontars" data-countdown="{{ $eventTarget }}">
                <div class="uk-lontar"><strong data-days>0</strong><span>Hari</span></div>
                <div class="uk-lontar"><strong data-hours>00</strong><span>Jam</span></div>
                <div class="uk-lontar"><strong data-minutes>00</strong><span>Menit</span></div>
                <div class="uk-lontar"><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
            <a class="uk-button" href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer">Simpan ke kalender</a>
        </section>

        <section class="uk-section" aria-label="Waktu dan tempat">
            <p class="uk-eyebrow" data-reveal>Waktu dan tempat</p>
            <h2 data-reveal>{{ $invitation->event_type }}</h2>
            <article class="uk-leaf" data-reveal="zoom">
                <p class="uk-day">{{ $invitation->event_date->translatedFormat('d') }}</p>
                <p class="uk-month">{{ $invitation->event_date->translatedFormat('l · F Y') }}</p>
                <dl>
                    <div><dt>Waktu</dt><dd>{{ $startTime }}@if ($endTime) - {{ $endTime }}@endif WITA</dd></div>
                    <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
                </dl>
                @if ($invitation->google_maps_url)
                    <a class="uk-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>
                @endif
            </article>
        </section>

        @if (count($gallery))
            <section class="uk-section" aria-label="Galeri">
                <p class="uk-script" data-reveal>Momen kami</p>
                <div class="uk-frames">
                    @foreach ($gallery as $photo)
                        <figure data-reveal="zoom" style="--d: {{ ($loop->index % 2) * 0.12 }}s">
                            <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $couple }} {{ $loop->iteration }}" loading="lazy" data-gallery>
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

        @if (($isPreview || $invitation->giftSetting?->is_active) && $invitation->giftSetting)
            <div data-reveal>
                @include('invitations.partials.wedding-gift')
            </div>
        @endif

        <footer class="uk-section uk-closing" data-reveal>
            <div class="uk-lamps" aria-hidden="true">
                <div class="uk-lamp"><i></i></div>
                <div class="uk-lamp"><i></i></div>
            </div>
            <p class="uk-script">Matur suksma</p>
            <h2>{{ $couple }}</h2>
            <p>Merupakan kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
            <p class="uk-eyebrow" style="margin-top: 22px">Om Shanti, Shanti, Shanti Om</p>
            @unless ($isPreview)
                <button class="uk-button is-quiet" type="button" data-share>Bagikan undangan</button>
            @endunless
            <p class="uk-credit">@include('invitations.partials.app-credit')</p>
        </footer>
    </main>
    @include('invitations.templates.premium.parts.behaviour')
</body>
</html>
