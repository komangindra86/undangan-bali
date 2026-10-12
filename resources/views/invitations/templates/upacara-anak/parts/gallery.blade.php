{{-- Gallery photos, or three labelled placeholders in a template preview. --}}
<div class="ub-gallery">
    @foreach($gallery as $image)
        <img src="{{ Storage::url($image) }}" alt="Foto {{ $invitation->display_name }} {{ $loop->iteration }}" loading="lazy" decoding="async">
    @endforeach
    @if($isPreview && !count($gallery))
        @foreach(['Senyum pertama', 'Bersama ayah dan ibu', 'Keluarga besar'] as $caption)
            <figure><div class="ub-placeholder" aria-hidden="true"></div><figcaption>{{ $caption }} · foto Anda di sini</figcaption></figure>
        @endforeach
    @endif
</div>
