{{-- Jepun Kirana: morning light. Jepun branches frame the couple's photo in a tall capsule, a candi bentar stands in
     the haze behind, the couple sit on two cards, the date is a calendar page and the gallery slides by itself. --}}
@php
    extract(App\Support\PremiumWedding::data($invitation, $isPreview ?? false));
    $ornaments = 'invitations.templates.upacara-anak.ornaments.';
    $branch = 'invitations.templates.pernikahan.ornaments.ranting-jepun';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#fdf6ea">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : 'Undangan '.$couple }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('invitations.templates.premium.parts.base')
    <style>
        :root {
            --jk-ivory: #fffaf1;
            --jk-cream: #f8eedd;
            --jk-sand: #ecdcc0;
            --jk-gold: #b8914a;
            --jk-gold-soft: #e4cd98;
            --jk-leaf: #5f8a45;
            --jk-ink: #3e3226;
            --jk-muted: #806f5d;
            --orn: #c2a063;
            --mote: #fff6dc;
            --pm-accent: #e4cd98;
            --pm-accent-ink: #8a6a2c;
            --jk-serif: 'Cormorant Garamond', Georgia, serif;
            --jk-script: 'Alex Brush', 'Brush Script MT', cursive;
        }
        html { background: #efe3cd; }
        body { background: linear-gradient(170deg, #eadbc0, #fbf5ea 50%, #e5d3b4); color: var(--jk-ink); font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .jk-page { background: var(--jk-ivory); box-shadow: 0 30px 80px rgba(98, 74, 40, .2); }
        .jk-eyebrow { color: var(--jk-gold); font-size: 11px; font-weight: 700; letter-spacing: .34em; margin: 0; text-transform: uppercase; }
        .jk-script { color: var(--jk-gold); font-family: var(--jk-script); font-size: 44px; font-weight: 400; line-height: 1.1; margin: 0; }
        .jk-section { padding: 70px 24px; position: relative; text-align: center; }
        .jk-section h2 { font-family: var(--jk-serif); font-size: 34px; font-weight: 600; line-height: 1.15; margin: 8px 0 0; }
        .jk-button { background: linear-gradient(135deg, #d0aa5e, #a9823c); border: 0; border-radius: 999px; box-shadow: 0 12px 26px rgba(150, 110, 40, .28); color: #fffaf0; cursor: pointer; display: inline-flex; font: 600 13px Inter, system-ui, sans-serif; justify-content: center; letter-spacing: .06em; min-height: 48px; overflow: hidden; padding: 14px 26px; position: relative; text-decoration: none; }
        .jk-button::after { animation: jk-sheen 4.4s ease-in-out infinite; background: linear-gradient(110deg, transparent 35%, rgba(255, 255, 255, .5) 50%, transparent 65%); content: ''; inset: 0; position: absolute; transform: translateX(-120%); }
        .jk-button.is-quiet { background: transparent; border: 1px solid var(--jk-gold-soft); box-shadow: none; color: var(--jk-gold); }

        /* First screen: morning sun and soft bokeh, a gate in the haze, branches of jepun around the photo. */
        .jk-hero {
            background:
                radial-gradient(circle at 82% 8%, rgba(255, 226, 160, .9), rgba(255, 226, 160, 0) 34%),
                radial-gradient(circle at 12% 30%, rgba(255, 255, 255, .55) 0 2.4%, transparent 3.2%),
                radial-gradient(circle at 26% 58%, rgba(255, 244, 214, .7) 0 2.2%, transparent 2.6%),
                radial-gradient(circle at 74% 46%, rgba(255, 255, 255, .5) 0 2%, transparent 2.8%),
                linear-gradient(180deg, #fdf0d8 0%, #fdf6ea 52%, var(--jk-ivory) 100%);
            overflow: hidden;
            padding: 64px 24px 58px;
            position: relative;
            text-align: center;
        }
        .jk-rays { animation: pm-spin 90s linear infinite; background: repeating-conic-gradient(from 0deg at 50% 50%, rgba(255, 255, 255, .26) 0 6deg, transparent 6deg 18deg); height: 900px; left: calc(82% - 450px); -webkit-mask: radial-gradient(circle, #000 0, transparent 62%); mask: radial-gradient(circle, #000 0, transparent 62%); position: absolute; top: -380px; width: 900px; }
        .jk-haze { display: flex; filter: blur(1.2px); justify-content: space-between; left: 0; opacity: .3; padding: 0 3%; position: absolute; right: 0; top: 250px; }
        .jk-haze .orn-gapura { width: 21%; }
        .jk-hero > :not(.jk-rays):not(.jk-haze):not(.pm-motes):not(.jk-petals) { position: relative; }
        .jk-hero > .orn-ranting { animation: jk-sway 8s ease-in-out infinite; left: -40px; position: absolute; top: 8px; transform-origin: 0 20%; width: 250px; z-index: 2; }
        .jk-photo-wrap { margin: 34px auto 0; width: min(64%, 260px); }
        .jk-photo { aspect-ratio: 3 / 4.3; background: linear-gradient(160deg, #fff, var(--jk-cream)); border-radius: 999px; box-shadow: 0 0 0 8px rgba(255, 255, 255, .85), 0 0 0 9px var(--jk-gold-soft), 0 34px 70px rgba(120, 90, 40, .28); overflow: hidden; }
        .jk-photo img { animation: jk-drift 18s ease-in-out infinite alternate; height: 100%; object-fit: cover; width: 100%; }
        .jk-photo span { align-items: center; color: var(--jk-gold); display: flex; font-family: var(--jk-serif); font-size: 52px; height: 100%; justify-content: center; }
        .jk-photo-wrap .orn-ranting { animation: jk-sway 9s ease-in-out -2s infinite; bottom: -38px; position: absolute; right: -88px; transform-origin: 100% 60%; width: 210px; z-index: 1; }
        .jk-hero h1 { font-family: var(--jk-serif); font-size: clamp(46px, 13vw, 64px); font-weight: 600; line-height: 1; margin: 46px 0 0; text-wrap: balance; }
        .jk-hero h1 span { color: var(--jk-gold); display: block; font-family: var(--jk-script); font-size: .62em; font-weight: 400; line-height: 1.2; }
        .jk-date { color: var(--jk-muted); font-size: 12px; font-weight: 600; letter-spacing: .26em; margin: 18px 0 0; text-transform: uppercase; }
        .jk-guest { background: rgba(255, 255, 255, .75); border: 1px solid #efe0c2; border-radius: 18px; color: var(--jk-muted); display: inline-block; font-size: 12px; margin: 24px 0 0; padding: 12px 22px; }
        .jk-guest strong { color: var(--jk-ink); display: block; font-family: var(--jk-serif); font-size: 22px; font-weight: 600; margin-top: 2px; overflow-wrap: anywhere; }
        .jk-petals { inset: 0; overflow: hidden; pointer-events: none; position: absolute; z-index: 3; }
        .jk-petals svg { animation: jk-fall linear infinite; filter: drop-shadow(0 4px 6px rgba(120, 84, 24, .18)); opacity: 0; position: absolute; top: -8%; }
        .jk-hero .pm-motes i { filter: blur(1px); height: 10px; width: 10px; }

        .jk-greeting p:not(.jk-eyebrow) { color: var(--jk-muted); font-family: var(--jk-serif); font-size: 21px; font-style: italic; line-height: 1.6; margin: 16px auto 0; max-width: 400px; }

        /* The couple, one card each with a jepun between them. */
        .jk-couple { background: linear-gradient(180deg, var(--jk-ivory), var(--jk-cream) 55%, var(--jk-ivory)); }
        .jk-person { align-items: center; background: #fff; border-radius: 26px; box-shadow: 0 18px 40px rgba(120, 90, 40, .14); display: grid; gap: 18px; grid-template-columns: 104px 1fr; margin: 26px 0 0; padding: 16px; text-align: left; }
        .jk-person.is-bride { grid-template-columns: 1fr 104px; text-align: right; }
        .jk-person.is-bride .jk-face { order: 2; }
        .jk-face { aspect-ratio: 1; border-radius: 50%; box-shadow: 0 0 0 4px #fff, 0 0 0 5px var(--jk-gold-soft); overflow: hidden; }
        .jk-face img { height: 100%; object-fit: cover; width: 100%; }
        .jk-face.is-demo-groom img { transform: scale(2.7); transform-origin: 43% 74%; }
        .jk-face.is-demo-bride img { transform: scale(2.7); transform-origin: 57% 74%; }
        .jk-face span { align-items: center; background: var(--jk-cream); color: var(--jk-gold); display: flex; font-family: var(--jk-serif); font-size: 40px; height: 100%; justify-content: center; }
        .jk-person h3 { font-family: var(--jk-serif); font-size: 23px; font-weight: 600; line-height: 1.15; margin: 4px 0 0; overflow-wrap: anywhere; }
        .jk-person p:not(.jk-eyebrow) { color: var(--jk-muted); font-size: 12.5px; line-height: 1.55; margin: 6px 0 0; }
        .jk-person p strong { color: var(--jk-ink); display: block; font-weight: 600; }
        .jk-person .jk-eyebrow { font-size: 9.5px; letter-spacing: .24em; }
        .jk-between { margin: 18px auto -6px; width: 54px; }
        .jk-between .orn-jepun { animation: pm-spin 30s linear infinite; width: 100%; }

        /* Their story, year by year. */
        .jk-years { margin: 30px auto 0; max-width: 420px; text-align: left; }
        .jk-years article { border-top: 1px solid #efe2c8; display: grid; gap: 16px; grid-template-columns: 86px 1fr; padding: 20px 0; }
        .jk-years article:last-child { border-bottom: 1px solid #efe2c8; }
        .jk-years b { color: var(--jk-gold-soft); font-family: var(--jk-serif); font-size: 34px; font-weight: 600; line-height: 1; }
        .jk-years time { color: var(--jk-gold); display: block; font-size: 10.5px; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; }
        .jk-years h3 { font-family: var(--jk-serif); font-size: 23px; font-weight: 600; margin: 4px 0 0; }
        .jk-years p { color: var(--jk-muted); font-size: 14px; line-height: 1.65; margin: 6px 0 0; }
        .jk-years img { border-radius: 16px; margin-top: 12px; }

        /* Countdown under a soft sunburst. */
        .jk-count { background: radial-gradient(circle at 50% 120%, rgba(255, 221, 150, .55), transparent 60%), linear-gradient(180deg, var(--jk-cream), #f3e3c6); overflow: hidden; }
        .jk-count .jk-rays { left: calc(50% - 450px); top: auto; bottom: -560px; }
        .jk-count > :not(.jk-rays):not(.pm-motes) { position: relative; }
        .jk-numbers { display: grid; grid-template-columns: repeat(4, 1fr); margin: 28px auto 0; max-width: 380px; }
        .jk-numbers div + div { border-left: 1px solid rgba(184, 145, 74, .35); }
        .jk-numbers strong { color: var(--jk-ink); display: block; font-family: var(--jk-serif); font-size: 44px; font-weight: 600; line-height: 1; }
        .jk-numbers span { color: var(--jk-gold); display: block; font-size: 10px; font-weight: 700; letter-spacing: .2em; margin-top: 8px; text-transform: uppercase; }
        .js .jk-numbers div { opacity: 0; transform: translateY(16px); transition: opacity .6s ease, transform .8s cubic-bezier(.2, .75, .25, 1); }
        .js .jk-count.is-revealed .jk-numbers div { opacity: 1; transform: none; }
        .js .jk-count.is-revealed .jk-numbers div:nth-child(2) { transition-delay: .12s; }
        .js .jk-count.is-revealed .jk-numbers div:nth-child(3) { transition-delay: .24s; }
        .js .jk-count.is-revealed .jk-numbers div:nth-child(4) { transition-delay: .36s; }
        .jk-count .jk-button { margin-top: 30px; }

        /* The ceremony as a calendar page with a canang tucked at its corner. */
        .jk-calendar { background: #fff; border-radius: 26px; box-shadow: 0 22px 50px rgba(120, 90, 40, .16); margin: 30px auto 0; max-width: 400px; overflow: hidden; position: relative; }
        .jk-calendar header { background: linear-gradient(135deg, #d6b26a, #ad8740); color: #fffaf0; font-size: 12px; font-weight: 700; letter-spacing: .3em; padding: 14px; text-transform: uppercase; }
        .jk-calendar header::before, .jk-calendar header::after { background: #fff; border-radius: 50%; box-shadow: 0 2px 0 rgba(0, 0, 0, .12) inset; content: ''; height: 12px; position: absolute; top: 10px; width: 12px; }
        .jk-calendar header::before { left: 9%; }
        .jk-calendar header::after { right: 9%; }
        .jk-calendar .jk-day { color: var(--jk-ink); font-family: var(--jk-serif); font-size: 96px; font-weight: 600; line-height: 1; margin: 20px 0 0; }
        .jk-calendar .jk-weekday { color: var(--jk-muted); font-size: 12px; font-weight: 600; letter-spacing: .3em; margin: 6px 0 0; text-transform: uppercase; }
        .jk-calendar dl { border-top: 1px dashed #ead9b8; margin: 24px 22px 0; padding: 18px 0 26px; }
        .jk-calendar dl div + div { margin-top: 16px; }
        .jk-calendar dt { color: var(--jk-gold); font-size: 10px; font-weight: 700; letter-spacing: .26em; text-transform: uppercase; }
        .jk-calendar dd { font-family: var(--jk-serif); font-size: 22px; font-weight: 600; line-height: 1.3; margin: 6px 0 0; }
        .jk-calendar dd small { color: var(--jk-muted); display: block; font-family: Inter, system-ui, sans-serif; font-size: 13px; font-weight: 400; line-height: 1.6; margin-top: 6px; }
        .jk-calendar .orn-canang { animation: pm-spin 50s linear infinite; bottom: -34px; opacity: .9; position: absolute; right: -34px; width: 84px; }
        .jk-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 24px; }

        /* Gallery: one photo at a time, sliding by itself; swipe or tap a dot to move. */
        .jk-slider { border-radius: 24px; box-shadow: 0 20px 44px rgba(120, 90, 40, .2); display: flex; margin: 28px 0 0; overflow-x: auto; scroll-behavior: smooth; scroll-snap-type: x mandatory; scrollbar-width: none; }
        .jk-slider::-webkit-scrollbar { display: none; }
        .jk-slider img { aspect-ratio: 4 / 5; flex: none; object-fit: cover; scroll-snap-align: center; width: 100%; }
        .jk-dots { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin: 16px 0 0; }
        .jk-dots button { background: var(--jk-sand); border: 0; border-radius: 999px; cursor: pointer; height: 8px; padding: 0; transition: width .3s ease, background .3s ease; width: 8px; }
        .jk-dots button.is-active { background: var(--jk-gold); width: 24px; }

        .jk-closing { background: linear-gradient(180deg, var(--jk-ivory), var(--jk-cream)); padding-bottom: 96px; }
        .jk-closing .orn-ranting { animation: jk-sway 8s ease-in-out infinite; margin: 0 auto 6px; transform-origin: 0 20%; width: 220px; }
        .jk-closing > p:not(.jk-script):not(.jk-eyebrow) { color: var(--jk-muted); font-size: 14px; line-height: 1.75; margin: 16px auto 0; max-width: 380px; }
        .jk-closing .jk-button { margin-top: 26px; }
        .jk-credit { color: #a8977f !important; font-size: 12px !important; margin-top: 34px !important; }

        @keyframes jk-sway { 0%, 100% { transform: rotate(0); } 50% { transform: rotate(-2.4deg); } }
        @keyframes jk-drift { from { transform: scale(1.02); } to { transform: scale(1.11) translateY(-1.5%); } }
        @keyframes jk-sheen { 0%, 55% { transform: translateX(-120%); } 100% { transform: translateX(120%); } }
        @keyframes jk-fall {
            0% { opacity: 0; top: -8%; transform: translateX(0) rotate(0); }
            10% { opacity: .9; }
            50% { transform: translateX(30px) rotate(170deg); }
            90% { opacity: .85; }
            100% { opacity: 0; top: 104%; transform: translateX(-14px) rotate(330deg); }
        }
        @media (prefers-reduced-motion: reduce) {
            .jk-petals { display: none; }
            .js .jk-numbers div { opacity: 1; transform: none; }
        }
        @media (max-width: 350px) {
            .jk-section { padding-left: 18px; padding-right: 18px; }
            .jk-person { grid-template-columns: 84px 1fr; }
            .jk-person.is-bride { grid-template-columns: 1fr 84px; }
            .jk-numbers strong { font-size: 34px; }
        }
    </style>
</head>
<body>
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $mediaUrl($heroPhoto ?: $demoPhoto),
        'openingAccent' => '#f3dca6',
        'openingText' => '#fffaf0',
        'openingShade' => 'rgba(84, 62, 30, .5)',
        'openingFont' => "'Cormorant Garamond', Georgia, serif",
        'openingHasMusic' => (bool) ($musicPath && ! $isPreview),
    ])
    @include('invitations.templates.upacara-anak.parts.defs')
    <main class="pm-page jk-page">
        @if ($isPreview)
            <p class="pm-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</p>
        @endif

        <header class="jk-hero">
            <div class="jk-rays" aria-hidden="true"></div>
            <div class="jk-haze" aria-hidden="true">
                @include($ornaments.'gapura')
                @include($ornaments.'gapura', ['class' => 'is-mirrored'])
            </div>
            <div class="pm-motes" aria-hidden="true">
                @foreach ([[10, 18], [32, 40], [58, 14], [84, 34], [18, 70], [46, 84], [72, 64], [90, 82]] as [$left, $top])
                    <i style="left: {{ $left }}%; top: {{ $top }}%; animation-delay: {{ $loop->index * 0.5 }}s"></i>
                @endforeach
            </div>
            <div class="jk-petals" aria-hidden="true">
                @foreach ([[14, 30, 12, 0], [38, 22, 15, 4], [62, 26, 13, 7], [84, 20, 16, 2], [50, 18, 18, 10]] as [$left, $size, $duration, $delay])
                    <svg viewBox="-60 -60 120 120" style="left: {{ $left }}%; width: {{ $size }}px; animation-duration: {{ $duration }}s; animation-delay: {{ $delay }}s"><use href="#ub-jepun"/></svg>
                @endforeach
            </div>
            @include($branch)
            <p class="jk-eyebrow" data-enter style="--i: 0">Om Swastyastu</p>
            <p class="jk-script" data-enter style="--i: 1">{{ $invitation->event_type }}</p>
            <div class="jk-photo-wrap" data-enter="bloom" style="--i: 2">
                <div class="jk-photo">
                    @if ($heroPhoto)
                        <img src="{{ $mediaUrl($heroPhoto) }}" alt="Foto {{ $couple }}">
                    @else
                        <span>{{ mb_substr($invitation->groom_nickname, 0, 1) }} &amp; {{ mb_substr($invitation->bride_nickname, 0, 1) }}</span>
                    @endif
                </div>
                @include($branch, ['class' => 'is-mirrored'])
            </div>
            <h1 data-enter style="--i: 4">{{ $invitation->groom_nickname }}<span>&amp;</span>{{ $invitation->bride_nickname }}</h1>
            <p class="jk-date" data-enter style="--i: 5">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
            @if ($guest !== '')
                <p class="jk-guest" data-enter style="--i: 6">Kepada Yth.<strong>{{ $guest }}</strong></p>
            @endif
        </header>

        <section class="jk-section jk-greeting" data-reveal>
            @include($ornaments.'patra')
            <p class="jk-eyebrow">Atas asung kertha wara nugraha</p>
            <p>{{ $invitation->opening_quote ?: 'Dengan restu keluarga, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu pada hari bahagia kami.' }}</p>
        </section>

        <section class="jk-section jk-couple" aria-label="Mempelai">
            <p class="jk-script" data-reveal>Mempelai</p>
            @foreach ([['Mempelai pria', 'Putra dari', 'groom', $groomPhoto], ['Mempelai wanita', 'Putri dari', 'bride', $bridePhoto]] as [$role, $childOf, $who, $photo])
                @if ($loop->last)
                    <div class="jk-between" aria-hidden="true" data-reveal="zoom">@include($ornaments.'jepun')</div>
                @endif
                <article class="jk-person is-{{ $who }}" data-reveal="{{ $loop->first ? 'left' : 'right' }}">
                    <div class="jk-face {{ $isPreview && ! $invitation->{$who.'_photo'} ? 'is-demo-'.$who : '' }}">
                        @if ($photo)
                            <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $invitation->{$who.'_full_name'} }}" loading="lazy">
                        @else
                            <span>{{ mb_substr($invitation->{$who.'_nickname'}, 0, 1) }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="jk-eyebrow">{{ $role }}</p>
                        <h3>{{ $invitation->{$who.'_full_name'} }}</h3>
                        @if ($invitation->{$who.'_father_name'} || $invitation->{$who.'_mother_name'})
                            <p>{{ $childOf }}<strong>{{ collect([$invitation->{$who.'_father_name'}, $invitation->{$who.'_mother_name'}])->filter()->implode(' & ') }}</strong></p>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        @if ($story)
            <section class="jk-section" aria-label="Kisah kami">
                <p class="jk-script" data-reveal>Kisah kami</p>
                <div class="jk-years">
                    @foreach ($story as $chapter)
                        <article data-reveal style="--d: {{ $loop->index * 0.1 }}s">
                            <b>{{ preg_match('/\d{4}/', (string) $chapter['when'], $year) ? $year[0] : $loop->iteration }}</b>
                            <div>
                                @if ($chapter['when'])<time>{{ $chapter['when'] }}</time>@endif
                                <h3>{{ $chapter['title'] }}</h3>
                                @if ($chapter['body'])<p>{{ $chapter['body'] }}</p>@endif
                                @if ($chapter['photo'])<img src="{{ $mediaUrl($chapter['photo']) }}" alt="{{ $chapter['title'] }}" loading="lazy">@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="jk-section jk-count" data-reveal aria-label="Hitung mundur">
            <div class="jk-rays" aria-hidden="true"></div>
            <p class="jk-eyebrow">Menuju hari bahagia</p>
            <h2>{{ $invitation->event_date->translatedFormat('d F Y') }}</h2>
            <div class="jk-numbers" data-countdown="{{ $eventTarget }}">
                <div><strong data-days>0</strong><span>Hari</span></div>
                <div><strong data-hours>00</strong><span>Jam</span></div>
                <div><strong data-minutes>00</strong><span>Menit</span></div>
                <div><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
            <a class="jk-button" href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer">Simpan ke kalender</a>
        </section>

        <section class="jk-section" aria-label="Waktu dan tempat">
            <p class="jk-eyebrow" data-reveal>Waktu dan tempat</p>
            <h2 data-reveal>{{ $invitation->event_type }}</h2>
            <article class="jk-calendar" data-reveal="zoom">
                <header>{{ $invitation->event_date->translatedFormat('F Y') }}</header>
                <p class="jk-day">{{ $invitation->event_date->translatedFormat('d') }}</p>
                <p class="jk-weekday">{{ $invitation->event_date->translatedFormat('l') }}</p>
                <dl>
                    <div><dt>Waktu</dt><dd>{{ $startTime }}@if ($endTime) - {{ $endTime }}@endif WITA</dd></div>
                    <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
                </dl>
                @include($ornaments.'canang')
            </article>
            @if ($invitation->google_maps_url)
                <div class="jk-actions" data-reveal>
                    <a class="jk-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>
                </div>
            @endif
        </section>

        @if (count($gallery))
            <section class="jk-section" aria-label="Galeri">
                <p class="jk-script" data-reveal>Momen kami</p>
                <div class="jk-slider" data-slider data-reveal>
                    @foreach ($gallery as $photo)
                        <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $couple }} {{ $loop->iteration }}" loading="lazy" data-gallery>
                    @endforeach
                </div>
                @if (count($gallery) > 1)
                    <div class="jk-dots" data-slider-dots>
                        @foreach ($gallery as $photo)
                            <button type="button" aria-label="Foto {{ $loop->iteration }}" @class(['is-active' => $loop->first])></button>
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

        <footer class="jk-section jk-closing" data-reveal>
            @include($branch)
            <p class="jk-script">Matur suksma</p>
            <h2>{{ $couple }}</h2>
            <p>Merupakan kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
            <p class="jk-eyebrow" style="margin-top: 22px">Om Shanti, Shanti, Shanti Om</p>
            @unless ($isPreview)
                <button class="jk-button is-quiet" type="button" data-share>Bagikan undangan</button>
            @endunless
            <p class="jk-credit">@include('invitations.partials.app-credit')</p>
        </footer>
    </main>
    @include('invitations.templates.premium.parts.behaviour')
    <script>
        (() => {
            // The gallery slides on every few seconds until someone touches it.
            const slider = document.querySelector('[data-slider]');
            const dots = [...document.querySelectorAll('[data-slider-dots] button')];
            if (!slider || dots.length < 2) return;
            const go = (index) => slider.scrollTo({ left: slider.clientWidth * index });
            const current = () => Math.round(slider.scrollLeft / slider.clientWidth);
            dots.forEach((dot, index) => dot.addEventListener('click', () => { stop(); go(index); }));
            slider.addEventListener('scroll', () => dots.forEach((dot, index) => dot.classList.toggle('is-active', index === current())), { passive: true });
            let timer = matchMedia('(prefers-reduced-motion: reduce)').matches ? null : setInterval(() => go((current() + 1) % dots.length), 4200);
            const stop = () => { clearInterval(timer); timer = null; };
            slider.addEventListener('pointerdown', stop, { once: true });
            slider.addEventListener('touchstart', stop, { once: true, passive: true });
        })();
    </script>
</body>
</html>
