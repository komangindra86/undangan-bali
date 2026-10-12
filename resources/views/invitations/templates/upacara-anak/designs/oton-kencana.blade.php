{{-- Oton Kencana: symmetric, in warm morning light. The date stands in a paras stone candi bentar flanked by tedung. --}}
<header class="ub-hero">
    @include($ornaments.'bayang')
    @include($ornaments.'jepun', ['class' => 'is-a'])
    @include($ornaments.'jepun', ['class' => 'is-b'])
    @include($ornaments.'jepun', ['class' => 'is-c'])
    <div class="ub-ider" aria-hidden="true"></div>
    <p class="ub-eyebrow">Om Swastyastu</p>
    @include($ornaments.'banten')
    <p class="ub-intro">{{ $ceremonyName }}</p>
    @if($invitation->ceremony_note)<p class="ub-note">{{ $invitation->ceremony_note }}</p>@endif
    <h1>{{ $invitation->display_name }}</h1>
    @if($invitation->child_relation)<p class="ub-relation">{{ $invitation->child_relation }}</p>@endif
    <div class="ub-gate">
        @include($ornaments.'gapura')
        <p class="ub-gate-date">
            <span>{{ $invitation->event_date?->translatedFormat('l') }}</span>
            <strong>{{ $invitation->event_date?->translatedFormat('d') }}</strong>
            <span>{{ $invitation->event_date?->translatedFormat('F Y') }}</span>
        </p>
        @include($ornaments.'gapura', ['class' => 'is-mirrored'])
    </div>
</header>
<section class="ub-section ub-greeting" data-reveal>
    @include($ornaments.'patra')
    <p>{{ $greeting }}</p>
    @include($parts.'meaning')
</section>
<section class="ub-section" data-reveal>
    <p class="ub-eyebrow">Buah hati kami</p>
    <article class="ub-child">
        @if($childPhoto)
            <img class="ub-child-photo" src="{{ $childPhoto }}" alt="Foto {{ $invitation->child_full_name ?: 'buah hati' }}" loading="lazy" decoding="async">
        @endif
        @if($invitation->child_full_name)<h2>{{ $invitation->child_full_name }}</h2>@endif
        <p class="ub-child-line">{{ $childLine }}</p>
        <div class="ub-parents">
            <p><span>Ayah</span><strong>{{ $invitation->groom_full_name }}</strong></p>
            <p><span>Ibu</span><strong>{{ $invitation->bride_full_name }}</strong></p>
        </div>
        @if($invitation->child_birth_date)
            <p class="ub-birth">Lahir {{ $invitation->child_birth_date->translatedFormat('d F Y') }}</p>
        @endif
    </article>
</section>
<section class="ub-section" data-reveal>
    <div class="ub-event">
        @include($ornaments.'patra')
        <p class="ub-eyebrow">Waktu dan tempat</p>
        <h2>{{ $ceremonyName }}</h2>
        <dl class="ub-details">
            <div><dt>Hari, tanggal</dt><dd>{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</dd></div>
            <div><dt>Waktu</dt><dd>{{ $timeText }}</dd></div>
            <div><dt>Tempat</dt><dd>{{ $invitation->venue_name }}<small>{{ $invitation->venue_address }}</small></dd></div>
        </dl>
        @if($invitation->google_maps_url)<a class="ub-button" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
    </div>
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
    <div class="ub-gate ub-gate--small" aria-hidden="true">
        @include($ornaments.'gapura')
        @include($ornaments.'gapura', ['class' => 'is-mirrored'])
    </div>
    @include($parts.'closing')
</footer>
