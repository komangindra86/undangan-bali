{{-- Langit Kumara: a soft blue sky, left-aligned and modern; a bebean kite flies above with its tails trailing down. --}}
<header class="ub-hero">
    @include($ornaments.'mega', ['class' => 'is-a'])
    @include($ornaments.'mega', ['class' => 'is-b'])
    @include($ornaments.'mega', ['class' => 'is-c'])
    @include($ornaments.'layangan')
    <div class="ub-hero-text">
        <p class="ub-eyebrow">Om Swastyastu</p>
        @include($parts.'title')
        <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
    </div>
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
    <div class="ub-grid">
        <p class="ub-card"><span>Hari, tanggal</span><strong>{{ $invitation->event_date?->translatedFormat('l') }}</strong>{{ $invitation->event_date?->translatedFormat('d F Y') }}</p>
        <p class="ub-card"><span>Waktu</span><strong>{{ substr((string) $invitation->start_time, 0, 5) }}</strong>{{ $invitation->end_time ? 's.d. '.substr($invitation->end_time, 0, 5).' WITA' : 'WITA' }}</p>
        <p class="ub-card is-wide"><span>Tempat</span><strong>{{ $invitation->venue_name }}</strong>{{ $invitation->venue_address }}</p>
    </div>
    @include($parts.'maps')
</section>
@include($parts.'tail', ['closingOrnament' => 'mega'])
