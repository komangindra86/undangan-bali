{{-- The child's name, place in the family, parents and birth date. --}}
@if($invitation->child_full_name)<h2>{{ $invitation->child_full_name }}</h2>@endif
<p class="ub-child-line">{{ $childLine }}</p>
<p class="ub-parents"><strong>{{ $invitation->groom_full_name }}</strong><span aria-hidden="true">&amp;</span><strong>{{ $invitation->bride_full_name }}</strong></p>
@if($invitation->child_birth_date)
    <p class="ub-birth">Lahir {{ $invitation->child_birth_date->translatedFormat('d F Y') }}</p>
@endif
