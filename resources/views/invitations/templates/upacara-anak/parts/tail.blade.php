{{-- Gallery, gift and closing, the same in most designs; $closingOrnament names an ornament for the closing. --}}
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
    @include($ornaments.$closingOrnament, ['class' => 'is-closing'])
    @include($parts.'closing')
</footer>
