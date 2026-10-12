{{-- Ceremony name, its note and the invitation title, in the order every hero uses. --}}
<p class="ub-intro">{{ $ceremonyName }}</p>
@if($invitation->ceremony_note)<p class="ub-note">{{ $invitation->ceremony_note }}</p>@endif
<h1>{{ $invitation->display_name }}</h1>
@if($invitation->child_relation)<p class="ub-relation">{{ $invitation->child_relation }}</p>@endif
