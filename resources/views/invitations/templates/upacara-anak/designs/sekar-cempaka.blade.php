{{-- Sekar Cempaka: soft and asymmetric. A penjor arches over the name and a canang sari rests below it. --}}
<header class="ub-hero">
    @include($ornaments.'penjor')
    <div class="ub-hero-text">
        <p class="ub-eyebrow">Om Swastyastu</p>
        <p class="ub-intro">{{ $ceremonyName }}</p>
        @if($invitation->ceremony_note)<p class="ub-note">{{ $invitation->ceremony_note }}</p>@endif
        <h1>{{ $invitation->display_name }}</h1>
        @if($invitation->child_relation)<p class="ub-relation">{{ $invitation->child_relation }}</p>@endif
        <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
    </div>
</header>
<section class="ub-section ub-greeting" data-reveal>
    @include($ornaments.'canang')
    <p>{{ $greeting }}</p>
    @if($invitation->ceremony_meaning)
        <div class="ub-meaning">
            <span class="ub-tridatu" aria-hidden="true"></span>
            <p><strong>{{ $invitation->ceremony_title }}</strong> · {{ $invitation->ceremony_meaning }}</p>
        </div>
    @endif
</section>
<section class="ub-section ub-child" data-reveal>
    @if($childPhoto)
        <img class="ub-child-photo" src="{{ $childPhoto }}" alt="Foto {{ $invitation->child_full_name ?: 'buah hati' }}" loading="lazy" decoding="async">
    @else
        <div class="ub-child-photo ub-child-photo--empty" aria-hidden="true">@include($ornaments.'canang')</div>
    @endif
    <div class="ub-child-text">
        <p class="ub-eyebrow">Buah hati kami</p>
        @if($invitation->child_full_name)<h2>{{ $invitation->child_full_name }}</h2>@endif
        <p class="ub-child-line">{{ $childLine }}</p>
        <p class="ub-parents"><strong>{{ $invitation->groom_full_name }}</strong><span aria-hidden="true">&amp;</span><strong>{{ $invitation->bride_full_name }}</strong></p>
        @if($invitation->child_birth_date)
            <p class="ub-birth">Lahir {{ $invitation->child_birth_date->translatedFormat('d F Y') }}</p>
        @endif
    </div>
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Waktu dan tempat</p>
    <h2>{{ $ceremonyName }}</h2>
    <div class="ub-event">
        <p class="ub-day">
            <span>{{ $invitation->event_date?->translatedFormat('l') }}</span>
            <strong>{{ $invitation->event_date?->translatedFormat('d') }}</strong>
            <span>{{ $invitation->event_date?->translatedFormat('F Y') }}</span>
        </p>
        <dl class="ub-details">
            <div><dt>Waktu</dt><dd>{{ $timeText }}</dd></div>
            <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
        </dl>
    </div>
    @if($invitation->google_maps_url)<a class="ub-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
</section>
@if(count($gallery) || $isPreview)
    <section class="ub-section" data-reveal>
        <p class="ub-eyebrow">{{ $galleryLabel }}</p><h2>Galeri</h2>
        @include($parts.'gallery')
    </section>
@endif
@if($invitation->giftSetting?->is_active)
    @include('invitations.partials.wedding-gift')
@endif
<footer class="ub-section ub-closing" data-reveal>
    @include($ornaments.'canang')
    @include($parts.'closing')
</footer>
