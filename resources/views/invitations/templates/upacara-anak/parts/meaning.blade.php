{{-- The ceremony's own emblem and one line on what the ceremony is. --}}
@if($invitation->ceremony_meaning)
    <div class="ub-meaning">
        @include($ornaments.'emblem-'.$invitation->ceremony_emblem)
        <p><strong>{{ $invitation->ceremony_title }}</strong> · {{ $invitation->ceremony_meaning }}</p>
    </div>
@endif
