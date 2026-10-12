{{-- Sekar Emas: champagne and bronze, soft and festive; a crown of golden flowers fans out over the title. --}}
<header class="ub-hero">
    <p class="ub-eyebrow">Om Swastyastu</p>
    @include($ornaments.'sekar-emas')
    @include($parts.'title')
    <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
</header>
<section class="ub-section ub-greeting" data-reveal>
    <p>{{ $greeting }}</p>
    @include($parts.'meaning')
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Yang mepandes</p>
    @include($parts.'participants')
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Waktu dan tempat</p>
    <h2>{{ $ceremonyName }}</h2>
    <div class="ub-rounds">
        <p><span>{{ $invitation->event_date?->translatedFormat('l') }}</span><strong>{{ $invitation->event_date?->translatedFormat('d') }}</strong><span>{{ $invitation->event_date?->translatedFormat('M Y') }}</span></p>
        <p><span>Pukul</span><strong>{{ substr((string) $invitation->start_time, 0, 5) }}</strong><span>{{ $invitation->end_time ? 's.d. '.substr($invitation->end_time, 0, 5) : 'WITA' }}</span></p>
    </div>
    <p class="ub-place"><strong>{{ $invitation->venue_name }}</strong>{{ $invitation->venue_address }}</p>
    @include($parts.'maps')
</section>
@include($parts.'tail', ['closingOrnament' => 'sekar-emas'])
