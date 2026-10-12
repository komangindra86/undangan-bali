{{-- Purnama Alit: dusk and modern. The pawukon wheel turns around the full moon among Kamasan clouds, edged in poleng. --}}
<header class="ub-hero">
    <div class="ub-dusk">
        <div class="ub-poleng" aria-hidden="true"></div>
        <p class="ub-eyebrow">Om Swastyastu</p>
        <div class="ub-sky" aria-hidden="true">
            @include($ornaments.'pawukon')
            @include($ornaments.'awan', ['class' => 'is-left'])
            @include($ornaments.'awan', ['class' => 'is-right'])
        </div>
    </div>
    <p class="ub-intro">{{ $ceremonyName }}</p>
    @if($invitation->ceremony_note)<p class="ub-note">{{ $invitation->ceremony_note }}</p>@endif
    <h1>{{ $invitation->display_name }}</h1>
    @if($invitation->child_relation)<p class="ub-relation">{{ $invitation->child_relation }}</p>@endif
    <p class="ub-date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
</header>
<div class="ub-wuku" data-reveal>
    <p>30 wuku × 7 hari = 210 hari</p>
    @include($parts.'meaning')
</div>
<section class="ub-section ub-greeting" data-reveal>
    <p>{{ $greeting }}</p>
</section>
<div class="ub-timeline">
    <section class="ub-step" data-reveal>
        <p class="ub-eyebrow">Buah hati kami</p>
        <div class="ub-child">
            @if($childPhoto)
                <img class="ub-child-photo" src="{{ $childPhoto }}" alt="Foto {{ $invitation->child_full_name ?: 'buah hati' }}" loading="lazy" decoding="async">
            @endif
            <div>
                @if($invitation->child_full_name)<h2>{{ $invitation->child_full_name }}</h2>@endif
                <p class="ub-child-line">{{ $childLine }}</p>
                <p class="ub-parents"><strong>{{ $invitation->groom_full_name }}</strong><span aria-hidden="true">&amp;</span><strong>{{ $invitation->bride_full_name }}</strong></p>
                @if($invitation->child_birth_date)
                    <p class="ub-birth">Lahir {{ $invitation->child_birth_date->translatedFormat('d F Y') }}</p>
                @endif
            </div>
        </div>
    </section>
    <section class="ub-step" data-reveal>
        <p class="ub-eyebrow">Waktu</p>
        <h2>{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</h2>
        <p class="ub-time">{{ $timeText }}</p>
        <p class="ub-step-note">{{ $ceremonyName }}</p>
    </section>
    <section class="ub-step" data-reveal>
        <p class="ub-eyebrow">Tempat</p>
        <h2>{{ $invitation->venue_name }}</h2>
        <p class="ub-step-note">{{ $invitation->venue_address }}</p>
        @if($invitation->google_maps_url)<a class="ub-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
    </section>
</div>
@if(count($gallery) || $isPreview)
    <section class="ub-section ub-gallery-section" data-reveal>
        <p class="ub-eyebrow">{{ $galleryLabel }}</p><h2>Galeri</h2>
        @include($parts.'gallery')
    </section>
@endif
@if($invitation->giftSetting?->is_active)
    @include('invitations.partials.wedding-gift')
@endif
<footer class="ub-section ub-closing" data-reveal>
    @include($ornaments.'awan')
    @include($parts.'closing')
    <div class="ub-poleng" aria-hidden="true"></div>
</footer>
