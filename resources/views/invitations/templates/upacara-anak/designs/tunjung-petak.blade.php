{{-- Tunjung Petak: white and soft green, calm and centred above a pond of white lotus; sections rise like ripples. --}}
<header class="ub-hero">
    <p class="ub-eyebrow">Om Swastyastu</p>
    @include($parts.'title')
    <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
    @include($ornaments.'kolam')
</header>
<section class="ub-section ub-greeting" data-reveal>
    <p>{{ $greeting }}</p>
    @include($parts.'meaning')
</section>
<section class="ub-section ub-wave" data-reveal>
    <p class="ub-eyebrow">Buah hati kami</p>
    <div class="ub-child">
        @include($parts.'photo')
        @include($parts.'child')
    </div>
</section>
<section class="ub-section ub-wave" data-reveal>
    <p class="ub-eyebrow">Waktu dan tempat</p>
    <h2>{{ $ceremonyName }}</h2>
    @include($parts.'details')
</section>
@include($parts.'tail', ['closingOrnament' => 'tunjung'])
