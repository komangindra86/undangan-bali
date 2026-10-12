{{-- Surya Alit: morning orange, modern; the sun rises beside Gunung Agung over a meru and rice terraces. --}}
<header class="ub-hero">
    <div class="ub-hero-text">
        <p class="ub-eyebrow">Om Swastyastu</p>
        @include($parts.'title')
        <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
    </div>
    @include($ornaments.'fajar')
</header>
<section class="ub-section ub-greeting" data-reveal>
    <p>{{ $greeting }}</p>
    @include($parts.'meaning')
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Buah hati kami</p>
    <article class="ub-card ub-child">
        @include($parts.'photo')
        <div>@include($parts.'child')</div>
    </article>
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Waktu dan tempat</p>
    <h2>{{ $ceremonyName }}</h2>
    <article class="ub-card ub-event">
        <p class="ub-sun">
            <span>{{ $invitation->event_date?->translatedFormat('D') }}</span>
            <strong>{{ $invitation->event_date?->translatedFormat('d') }}</strong>
            <span>{{ $invitation->event_date?->translatedFormat('M') }}</span>
        </p>
        <dl class="ub-details">
            <div><dt>Hari, tanggal</dt><dd>{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</dd></div>
            <div><dt>Waktu</dt><dd>{{ $timeText }}</dd></div>
            <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
        </dl>
    </article>
    @include($parts.'maps')
</section>
@include($parts.'tail', ['closingOrnament' => 'fajar'])
