{{-- Sekar Jepun: white and frangipani yellow, everything round; the photo sits inside a wreath of jepun. --}}
<header class="ub-hero">
    <p class="ub-eyebrow">Om Swastyastu</p>
    <div class="ub-wreath">
        @include($ornaments.'karangan')
        @if($childPhoto)
            <img class="ub-wreath-photo" src="{{ $childPhoto }}" alt="Foto {{ $invitation->child_full_name ?: 'buah hati' }}" decoding="async">
        @else
            <div class="ub-wreath-photo" aria-hidden="true">@include($ornaments.'jepun')</div>
        @endif
    </div>
    @include($parts.'title')
    <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
</header>
<section class="ub-section ub-greeting" data-reveal>
    <p>{{ $greeting }}</p>
    @include($parts.'meaning')
</section>
<section class="ub-section" data-reveal>
    <article class="ub-card ub-child">
        <p class="ub-eyebrow">Buah hati kami</p>
        @include($parts.'child')
    </article>
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
@include($parts.'tail', ['closingOrnament' => 'jepun'])
