{{-- The child's photo, when there is one. --}}
@if($childPhoto)
    <img class="ub-child-photo" src="{{ $childPhoto }}" alt="Foto {{ $invitation->child_full_name ?: 'buah hati' }}" loading="lazy" decoding="async">
@endif
