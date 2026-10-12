{{-- Rare Kencana: cream and gold, centred between two hanging lamak; cards are edged like ider-ider. --}}
<header class="ub-hero">
    @include($ornaments.'lamak', ['class' => 'is-left'])
    @include($ornaments.'lamak', ['class' => 'is-right'])
    <p class="ub-eyebrow">Om Swastyastu</p>
    @include($ornaments.'jepun')
    @include($parts.'title')
    <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
</header>
<section class="ub-section ub-greeting" data-reveal>
    @include($ornaments.'patra')
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
    <article class="ub-card ub-event">
        <p class="ub-eyebrow">Waktu dan tempat</p>
        <h2>{{ $ceremonyName }}</h2>
        @include($parts.'details')
    </article>
</section>
@include($parts.'tail', ['closingOrnament' => 'patra'])
