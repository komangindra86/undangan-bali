{{-- Bale Gading: ivory and gold, symmetric; the bale where the mepandes is held stands under the title. --}}
<header class="ub-hero">
    @include($ornaments.'bayang')
    <p class="ub-eyebrow">Om Swastyastu</p>
    @include($parts.'title')
    <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
    @include($ornaments.'bale')
</header>
<section class="ub-section ub-greeting" data-reveal>
    @include($ornaments.'patra')
    <p>{{ $greeting }}</p>
    @include($parts.'meaning')
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Yang mepandes</p>
    @include($parts.'participants')
</section>
<section class="ub-section" data-reveal>
    <article class="ub-card ub-event">
        @include($ornaments.'patra')
        <p class="ub-eyebrow">Waktu dan tempat</p>
        <h2>{{ $ceremonyName }}</h2>
        @include($parts.'details')
    </article>
</section>
@include($parts.'tail', ['closingOrnament' => 'bale'])
