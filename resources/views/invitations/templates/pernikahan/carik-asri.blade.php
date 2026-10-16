{{-- Carik Asri: morning over the rice terraces. The names sit in the sky above Gunung Agung, the couple's photo is a
     wide print resting on the fields with rice swaying in front, each of them gets a banner, and the gallery is a
     scatter of prints. Free designs leave out the couple's story, which only premium designs tell. --}}
@php
    extract(App\Support\PremiumWedding::data($invitation, $isPreview ?? false));
    $ornaments = 'invitations.templates.upacara-anak.ornaments.';
    $padi = 'invitations.templates.pernikahan.ornaments.padi';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#eef4e4">
    <title>{{ $isPreview ? 'Preview '.$invitation->template->name : 'Undangan '.$couple }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,500;0,600;1,500&family=Sacramento&display=swap" rel="stylesheet">
    @include('invitations.templates.premium.parts.base')
    <style>
        :root {
            --ca-sky: #f6f3e2;
            --ca-paper: #fbfaf2;
            --ca-field: #e5eed2;
            --ca-green: #557a3a;
            --ca-deep: #2f4b26;
            --ca-straw: #c9a24e;
            --ca-ink: #2f3a29;
            --ca-muted: #6c7860;
            --orn: #8ea96a;
            --mote: #fffbe0;
            --pm-accent: #b7cc93;
            --pm-accent-ink: #2f4b26;
            --ca-serif: Lora, Georgia, serif;
            --ca-script: Sacramento, 'Brush Script MT', cursive;
        }
        html { background: #d9e4c6; }
        body { background: linear-gradient(170deg, #d3dfbd, #f3f6ea 50%, #cfdbb6); color: var(--ca-ink); font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .ca-page { background: var(--ca-paper); box-shadow: 0 30px 80px rgba(47, 75, 38, .22); }
        .ca-eyebrow { color: var(--ca-green); font-size: 11px; font-weight: 700; letter-spacing: .32em; margin: 0; text-transform: uppercase; }
        .ca-script { color: var(--ca-straw); font-family: var(--ca-script); font-size: 50px; line-height: 1; margin: 0; }
        .ca-section { padding: 70px 24px; position: relative; text-align: center; }
        .ca-section h2 { font-family: var(--ca-serif); font-size: 30px; font-weight: 600; line-height: 1.2; margin: 8px 0 0; }
        .ca-button { background: linear-gradient(135deg, #6f9a4c, var(--ca-deep)); border: 0; border-radius: 999px; box-shadow: 0 12px 26px rgba(47, 75, 38, .3); color: #f7fbef; cursor: pointer; display: inline-flex; font: 600 13px Inter, system-ui, sans-serif; justify-content: center; letter-spacing: .06em; min-height: 48px; overflow: hidden; padding: 14px 26px; position: relative; text-decoration: none; }
        .ca-button::after { animation: ca-sheen 4.4s ease-in-out infinite; background: linear-gradient(110deg, transparent 35%, rgba(255, 255, 255, .42) 50%, transparent 65%); content: ''; inset: 0; position: absolute; transform: translateX(-120%); }
        .ca-button.is-quiet { background: transparent; border: 1px solid var(--pm-accent); box-shadow: none; color: var(--ca-deep); }

        /* First screen: the names in the morning sky, the terraces below, birds crossing, mist drifting. */
        .ca-hero { background: linear-gradient(180deg, #fbe9c7 0%, #f8f1dc 34%, var(--ca-sky) 52%, var(--ca-paper) 100%); overflow: hidden; padding: 54px 0 46px; position: relative; text-align: center; }
        .ca-hero > :not(.ca-scene):not(.pm-motes):not(.ca-birds) { position: relative; z-index: 2; }
        .ca-hero h1 { font-family: var(--ca-serif); font-size: clamp(42px, 12vw, 58px); font-weight: 600; line-height: 1.02; margin: 10px 0 0; padding: 0 20px; text-wrap: balance; }
        .ca-hero h1 span { color: var(--ca-straw); display: block; font-family: var(--ca-script); font-size: .78em; font-weight: 400; line-height: 1; }
        .ca-date { color: var(--ca-muted); font-size: 12px; font-weight: 600; letter-spacing: .26em; margin: 16px 0 0; text-transform: uppercase; }
        .ca-scene { margin: 18px 0 0; position: relative; }
        .ca-scene .orn-fajar { position: relative; width: 100%; }
        .ca-sun { animation: ca-glow 6s ease-in-out infinite; background: radial-gradient(circle, rgba(255, 223, 150, .95), rgba(255, 223, 150, 0) 64%); border-radius: 50%; height: 220px; left: calc(28% - 110px); position: absolute; top: -40px; width: 220px; }
        .ca-mist { animation: ca-mist 26s linear infinite; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .75), transparent); bottom: 22%; height: 34px; left: -60%; position: absolute; width: 90%; }
        .ca-mist.is-b { animation-delay: -13s; bottom: 40%; height: 24px; opacity: .7; }
        .ca-birds { left: 0; pointer-events: none; position: absolute; right: 0; top: 150px; z-index: 1; }
        .ca-birds svg { animation: ca-fly linear infinite; position: absolute; width: 22px; }
        .ca-birds path { animation: ca-flap .7s ease-in-out infinite alternate; transform-origin: 50% 70%; }
        .ca-print { background: #fff; border-radius: 10px; box-shadow: 0 26px 54px rgba(47, 75, 38, .3); margin: -34px auto 0; padding: 10px 10px 14px; width: min(84%, 380px); }
        .ca-print img, .ca-print .ca-blank { aspect-ratio: 4 / 3; border-radius: 6px; display: block; object-fit: cover; width: 100%; }
        .ca-print img { animation: ca-drift 18s ease-in-out infinite alternate; }
        .ca-print .ca-blank { align-items: center; background: var(--ca-field); color: var(--ca-green); display: flex; font-family: var(--ca-serif); font-size: 44px; justify-content: center; }
        .ca-print-frame { border-radius: 6px; overflow: hidden; }
        .ca-print figcaption { color: var(--ca-muted); font-family: var(--ca-script); font-size: 26px; line-height: 1; margin-top: 10px; }
        .ca-stalks { bottom: 18px; height: 0; left: 0; position: absolute; right: 0; z-index: 3; }
        .ca-stalks .orn-padi { animation: ca-sway 5s ease-in-out infinite; bottom: 0; position: absolute; transform-origin: 50% 100%; width: 104px; }
        .ca-stalks .orn-padi.is-left { left: -18px; }
        .ca-stalks .orn-padi.is-right { animation-delay: -2.5s; right: -18px; }
        .ca-guest { color: var(--ca-muted); font-size: 12px; margin: 30px 0 0; }
        .ca-guest strong { color: var(--ca-ink); display: block; font-family: var(--ca-serif); font-size: 22px; font-weight: 600; margin-top: 2px; overflow-wrap: anywhere; padding: 0 20px; }

        .ca-greeting p:not(.ca-eyebrow) { color: var(--ca-muted); font-family: var(--ca-serif); font-size: 19px; font-style: italic; line-height: 1.7; margin: 16px auto 0; max-width: 400px; }

        /* The couple: a banner each, the photo melting into the colour of the fields. */
        .ca-couple { padding-left: 0; padding-right: 0; }
        .ca-banner { align-items: stretch; display: grid; grid-template-columns: 44% 1fr; margin: 26px 0 0; min-height: 210px; position: relative; text-align: left; }
        .ca-banner.is-bride { grid-template-columns: 1fr 44%; text-align: right; }
        .ca-banner.is-bride .ca-banner-photo { order: 2; }
        .ca-banner-photo { overflow: hidden; position: relative; }
        .ca-banner-photo img { height: 100%; object-fit: cover; position: absolute; width: 100%; }
        .ca-banner-photo.is-demo-groom img { transform: scale(2.6); transform-origin: 43% 74%; }
        .ca-banner-photo.is-demo-bride img { transform: scale(2.6); transform-origin: 57% 74%; }
        .ca-banner-photo span { align-items: center; color: var(--ca-green); display: flex; font-family: var(--ca-serif); font-size: 56px; height: 100%; justify-content: center; }
        .ca-banner-photo::after { background: linear-gradient(90deg, rgba(229, 238, 210, 0) 40%, var(--ca-field)); content: ''; inset: 0; position: absolute; }
        .ca-banner.is-bride .ca-banner-photo::after { background: linear-gradient(270deg, rgba(229, 238, 210, 0) 40%, var(--ca-field)); }
        .ca-banner-text { align-self: center; background: var(--ca-field); padding: 22px 22px 22px 6px; }
        .ca-banner.is-bride .ca-banner-text { padding: 22px 6px 22px 22px; }
        .ca-banner { background: var(--ca-field); }
        .ca-banner h3 { font-family: var(--ca-serif); font-size: 22px; font-weight: 600; line-height: 1.2; margin: 6px 0 0; overflow-wrap: anywhere; }
        .ca-banner p:not(.ca-eyebrow) { color: var(--ca-muted); font-size: 12.5px; line-height: 1.55; margin: 8px 0 0; }
        .ca-banner p strong { color: var(--ca-ink); display: block; font-weight: 600; }
        .ca-banner .ca-eyebrow { font-size: 9.5px; letter-spacing: .24em; }
        .ca-amp { color: var(--ca-straw); font-family: var(--ca-script); font-size: 54px; line-height: 1; margin: 16px 0 -10px; }

        /* Countdown on four leaves. */
        .ca-count { background: linear-gradient(180deg, var(--ca-paper), #eaf1dc); }
        .ca-leaves { display: grid; gap: 14px; grid-template-columns: repeat(4, 1fr); margin: 30px auto 0; max-width: 380px; }
        .ca-leaves div { align-content: center; aspect-ratio: 1; background: linear-gradient(145deg, #93b86a, var(--ca-green)); border-radius: 0 60% 0 60%; box-shadow: 0 12px 24px rgba(47, 75, 38, .26); color: #f7fbef; display: grid; position: relative; }
        .ca-leaves div::before { border-top: 1px solid rgba(247, 251, 239, .45); content: ''; left: 12%; position: absolute; right: 12%; top: 50%; transform: rotate(-45deg); }
        .js .ca-leaves div { opacity: 0; transform: scale(.4) rotate(-40deg); transition: opacity .6s ease, transform .9s cubic-bezier(.3, 1.4, .5, 1); }
        .js .ca-count.is-revealed .ca-leaves div { opacity: 1; transform: none; }
        .js .ca-count.is-revealed .ca-leaves div:nth-child(2) { transition-delay: .12s; }
        .js .ca-count.is-revealed .ca-leaves div:nth-child(3) { transition-delay: .24s; }
        .js .ca-count.is-revealed .ca-leaves div:nth-child(4) { transition-delay: .36s; }
        .ca-leaves strong { font-family: var(--ca-serif); font-size: 26px; font-weight: 600; line-height: 1; position: relative; }
        .ca-leaves span { font-size: 9px; font-weight: 700; letter-spacing: .14em; margin-top: 4px; position: relative; text-transform: uppercase; }
        .ca-count .ca-button { margin-top: 30px; }

        /* The ceremony on a board framed in bamboo. */
        .ca-board { background: #fffdf6; border: 10px solid transparent; border-image: repeating-linear-gradient(90deg, #d9c27e 0 46px, #b79a4f 46px 49px) 10; box-shadow: 0 20px 44px rgba(47, 75, 38, .16); margin: 28px auto 0; max-width: 400px; padding: 26px 20px; }
        .ca-board .ca-day { color: var(--ca-deep); font-family: var(--ca-serif); font-size: 64px; font-weight: 600; line-height: 1; margin: 0; }
        .ca-board .ca-month { color: var(--ca-muted); font-size: 12px; font-weight: 600; letter-spacing: .26em; margin: 8px 0 0; text-transform: uppercase; }
        .ca-board dl { border-top: 1px solid #e6ebd6; margin: 22px 0 0; padding-top: 18px; }
        .ca-board dl div + div { margin-top: 16px; }
        .ca-board dt { color: var(--ca-green); font-size: 10px; font-weight: 700; letter-spacing: .26em; text-transform: uppercase; }
        .ca-board dd { font-family: var(--ca-serif); font-size: 21px; font-weight: 600; line-height: 1.3; margin: 6px 0 0; }
        .ca-board dd small { color: var(--ca-muted); display: block; font-family: Inter, system-ui, sans-serif; font-size: 13px; font-weight: 400; line-height: 1.6; margin-top: 6px; }
        .ca-board .ca-button { margin-top: 22px; }

        /* Gallery: prints scattered on the table. */
        .ca-prints { columns: 2; column-gap: 14px; margin: 30px 0 0; }
        .ca-prints > div { break-inside: avoid; margin: 0 0 16px; }
        .ca-prints figure { background: #fff; border-radius: 6px; box-shadow: 0 14px 28px rgba(47, 75, 38, .2); margin: 0; padding: 7px 7px 18px; transform: rotate(var(--r, 0deg)); }
        .ca-prints img { border-radius: 3px; width: 100%; }

        .ca-closing { background: linear-gradient(180deg, var(--ca-paper), #e7efd7); padding-bottom: 96px; }
        .ca-closing .orn-padi { animation: ca-sway 5s ease-in-out infinite; margin: 0 auto 4px; transform-origin: 50% 100%; width: 92px; }
        .ca-closing > p:not(.ca-script):not(.ca-eyebrow) { color: var(--ca-muted); font-size: 14px; line-height: 1.75; margin: 16px auto 0; max-width: 380px; }
        .ca-closing .ca-button { margin-top: 26px; }
        .ca-credit { color: #8d9a80 !important; font-size: 12px !important; margin-top: 34px !important; }

        @keyframes ca-glow { 0%, 100% { opacity: .75; transform: scale(.94); } 50% { opacity: 1; transform: scale(1.06); } }
        @keyframes ca-mist { from { transform: translateX(0); } to { transform: translateX(240%); } }
        @keyframes ca-fly { from { transform: translate(-60px, 0); } 50% { transform: translate(210px, -26px); } to { transform: translate(470px, 6px); } }
        @keyframes ca-flap { from { transform: scaleY(1); } to { transform: scaleY(.35); } }
        @keyframes ca-sway { 0%, 100% { transform: rotate(-2deg); } 50% { transform: rotate(3deg); } }
        @keyframes ca-drift { from { transform: scale(1.02); } to { transform: scale(1.1); } }
        @keyframes ca-sheen { 0%, 55% { transform: translateX(-120%); } 100% { transform: translateX(120%); } }
        @media (prefers-reduced-motion: reduce) {
            .ca-birds, .ca-mist { display: none; }
            .js .ca-leaves div { opacity: 1; transform: none; }
        }
        @media (max-width: 350px) {
            .ca-section { padding-left: 18px; padding-right: 18px; }
            .ca-couple { padding-left: 0; padding-right: 0; }
            .ca-banner h3 { font-size: 19px; }
            .ca-leaves strong { font-size: 21px; }
        }
    </style>
</head>
<body>
    @include('invitations.partials.opening-cover', [
        'openingTheme' => 'Om Swastyastu',
        'openingImage' => $mediaUrl($heroPhoto ?: $demoPhoto),
        'openingAccent' => '#e9d58f',
        'openingText' => '#fbfaf2',
        'openingShade' => 'rgba(40, 62, 30, .52)',
        'openingFont' => 'Lora, Georgia, serif',
        'openingHasMusic' => (bool) ($musicPath && ! $isPreview),
    ])
    @include('invitations.templates.upacara-anak.parts.defs')
    <main class="pm-page ca-page">
        @if ($isPreview)
            <p class="pm-demo">Preview {{ $invitation->template->name }} · Data contoh, bukan undangan asli</p>
        @endif

        <header class="ca-hero">
            <div class="pm-motes" aria-hidden="true">
                @foreach ([[12, 8], [34, 16], [62, 6], [86, 14], [20, 34], [74, 30]] as [$left, $top])
                    <i style="left: {{ $left }}%; top: {{ $top }}%; animation-delay: {{ $loop->index * 0.6 }}s"></i>
                @endforeach
            </div>
            <div class="ca-birds" aria-hidden="true">
                @foreach ([[0, 14, 0], [18, 17, 6], [-6, 15, 11]] as [$top, $duration, $delay])
                    <svg viewBox="0 0 24 10" style="top: {{ $top }}px; animation-duration: {{ $duration }}s; animation-delay: {{ $delay }}s"><path d="M1 7Q6 1 12 7Q18 1 23 7" fill="none" stroke="#5b5a48" stroke-width="1.6" stroke-linecap="round"/></svg>
                @endforeach
            </div>
            <p class="ca-eyebrow" data-enter style="--i: 0">Om Swastyastu</p>
            <h1 data-enter style="--i: 1">{{ $invitation->groom_nickname }}<span>&amp;</span>{{ $invitation->bride_nickname }}</h1>
            <p class="ca-date" data-enter style="--i: 2">{{ $invitation->event_type }} · {{ $invitation->event_date->translatedFormat('d F Y') }}</p>
            <div class="ca-scene" aria-hidden="true">
                <div class="ca-sun"></div>
                @include($ornaments.'fajar')
                <div class="ca-mist"></div>
                <div class="ca-mist is-b"></div>
            </div>
            <figure class="ca-print" data-enter="bloom" style="--i: 3">
                <div class="ca-print-frame">
                    @if ($heroPhoto)
                        <img src="{{ $mediaUrl($heroPhoto) }}" alt="Foto {{ $couple }}">
                    @else
                        <div class="ca-blank">{{ mb_substr($invitation->groom_nickname, 0, 1) }} &amp; {{ mb_substr($invitation->bride_nickname, 0, 1) }}</div>
                    @endif
                </div>
                <figcaption>{{ $couple }}</figcaption>
            </figure>
            @if ($guest !== '')
                <p class="ca-guest" data-enter style="--i: 5">Kepada Yth.<strong>{{ $guest }}</strong></p>
            @endif
            <div class="ca-stalks" aria-hidden="true">
                @include($padi, ['class' => 'is-left'])
                @include($padi, ['class' => 'is-right is-mirrored'])
            </div>
        </header>

        <section class="ca-section ca-greeting" data-reveal>
            @include($ornaments.'patra')
            <p class="ca-eyebrow">Atas asung kertha wara nugraha</p>
            <p>{{ $invitation->opening_quote ?: 'Dengan restu keluarga, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu pada hari bahagia kami.' }}</p>
        </section>

        <section class="ca-section ca-couple" aria-label="Mempelai">
            <p class="ca-script" data-reveal>Mempelai</p>
            @foreach ([['Mempelai pria', 'Putra dari', 'groom', $groomPhoto], ['Mempelai wanita', 'Putri dari', 'bride', $bridePhoto]] as [$role, $childOf, $who, $photo])
                @if ($loop->last)
                    <p class="ca-amp" aria-hidden="true" data-reveal="zoom">&amp;</p>
                @endif
                <article class="ca-banner is-{{ $who }}" data-reveal="{{ $loop->first ? 'left' : 'right' }}">
                    <div class="ca-banner-photo {{ $isPreview && ! $invitation->{$who.'_photo'} ? 'is-demo-'.$who : '' }}">
                        @if ($photo)
                            <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $invitation->{$who.'_full_name'} }}" loading="lazy">
                        @else
                            <span>{{ mb_substr($invitation->{$who.'_nickname'}, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="ca-banner-text">
                        <p class="ca-eyebrow">{{ $role }}</p>
                        <h3>{{ $invitation->{$who.'_full_name'} }}</h3>
                        @if ($invitation->{$who.'_father_name'} || $invitation->{$who.'_mother_name'})
                            <p>{{ $childOf }}<strong>{{ collect([$invitation->{$who.'_father_name'}, $invitation->{$who.'_mother_name'}])->filter()->implode(' & ') }}</strong></p>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        <section class="ca-section ca-count" data-reveal aria-label="Hitung mundur">
            <p class="ca-eyebrow">Menuju hari bahagia</p>
            <h2>{{ $invitation->event_date->translatedFormat('d F Y') }}</h2>
            <div class="ca-leaves" data-countdown="{{ $eventTarget }}">
                <div><strong data-days>0</strong><span>Hari</span></div>
                <div><strong data-hours>00</strong><span>Jam</span></div>
                <div><strong data-minutes>00</strong><span>Menit</span></div>
                <div><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
            <a class="ca-button" href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer">Simpan ke kalender</a>
        </section>

        <section class="ca-section" aria-label="Waktu dan tempat">
            <p class="ca-eyebrow" data-reveal>Waktu dan tempat</p>
            <h2 data-reveal>{{ $invitation->event_type }}</h2>
            <article class="ca-board" data-reveal="zoom">
                <p class="ca-day">{{ $invitation->event_date->translatedFormat('d') }}</p>
                <p class="ca-month">{{ $invitation->event_date->translatedFormat('l · F Y') }}</p>
                <dl>
                    <div><dt>Waktu</dt><dd>{{ $startTime }}@if ($endTime) - {{ $endTime }}@endif WITA</dd></div>
                    <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
                </dl>
                @if ($invitation->google_maps_url)
                    <a class="ca-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>
                @endif
            </article>
        </section>

        @if (count($gallery))
            <section class="ca-section" aria-label="Galeri">
                <p class="ca-script" data-reveal>Momen kami</p>
                <div class="ca-prints">
                    @foreach ($gallery as $photo)
                        <div data-reveal="zoom" style="--d: {{ ($loop->index % 2) * 0.12 }}s">
                            <figure style="--r: {{ [-2.5, 2, -1.5, 3, -3, 1.5][$loop->index % 6] }}deg">
                                <img src="{{ $mediaUrl($photo) }}" alt="Foto {{ $couple }} {{ $loop->iteration }}" loading="lazy" data-gallery>
                            </figure>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if (($isPreview || $invitation->giftSetting?->is_active) && $invitation->giftSetting)
            <div data-reveal>
                @include('invitations.partials.wedding-gift')
            </div>
        @endif

        <footer class="ca-section ca-closing" data-reveal>
            @include($padi)
            <p class="ca-script">Matur suksma</p>
            <h2>{{ $couple }}</h2>
            <p>Merupakan kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
            <p class="ca-eyebrow" style="margin-top: 22px">Om Shanti, Shanti, Shanti Om</p>
            @unless ($isPreview)
                <button class="ca-button is-quiet" type="button" data-share>Bagikan undangan</button>
            @endunless
            <p class="ca-credit">@include('invitations.partials.app-credit')</p>
        </footer>
    </main>
    @include('invitations.templates.premium.parts.behaviour')
</body>
</html>
