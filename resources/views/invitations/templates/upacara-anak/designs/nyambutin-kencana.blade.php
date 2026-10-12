{{-- Nyambutin Kencana: warm terracotta and gold; a gebogan between two umbul-umbul, above a red brick wall. --}}
<header class="ub-hero">
    @include($ornaments.'umbul', ['class' => 'is-left'])
    @include($ornaments.'umbul', ['class' => 'is-right is-mirrored'])
    <p class="ub-eyebrow">Om Swastyastu</p>
    @include($ornaments.'gebogan')
    @include($parts.'title')
    <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
    <div class="ub-bata" aria-hidden="true"></div>
</header>
<section class="ub-section ub-greeting" data-reveal>
    <p>{{ $greeting }}</p>
    @include($parts.'meaning')
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Buah hati kami</p>
    <article class="ub-card ub-child">
        @include($parts.'photo')
        @include($parts.'child')
    </article>
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Waktu dan tempat</p>
    <h2>{{ $ceremonyName }}</h2>
    <div class="ub-strip">
        <p><span>Hari</span><strong>{{ $invitation->event_date?->translatedFormat('l') }}</strong></p>
        <p><span>Tanggal</span><strong>{{ $invitation->event_date?->translatedFormat('d M Y') }}</strong></p>
        <p><span>Pukul</span><strong>{{ substr((string) $invitation->start_time, 0, 5) }}{{ $invitation->end_time ? ' - '.substr($invitation->end_time, 0, 5) : '' }}</strong></p>
    </div>
    <p class="ub-place"><strong>{{ $invitation->venue_name }}</strong>{{ $invitation->venue_address }}</p>
    @include($parts.'maps')
</section>
@include($parts.'tail', ['closingOrnament' => 'gebogan'])
