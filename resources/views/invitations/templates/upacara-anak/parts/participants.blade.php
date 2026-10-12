{{-- The people a mepandes is held for. With several families each one names their own parents. --}}
<ul class="ub-people">
    @foreach($invitation->participant_list as $person)
        @php
            $personLabel = ['putra' => 'Putra', 'putri' => 'Putri'][$person['gender'] ?? ''] ?? null;
            $personFacts = array_filter([$personLabel, ($person['age'] ?? null) ? $person['age'].' tahun' : null]);
        @endphp
        <li class="ub-person">
            @if($person['photo'] ?? null)
                <img class="ub-person-photo" src="{{ Storage::url($person['photo']) }}" alt="Foto {{ $person['full_name'] }}" loading="lazy" decoding="async">
            @else
                <span class="ub-person-photo ub-person-photo--empty" aria-hidden="true">{{ mb_strtoupper(mb_substr($person['nickname'] ?: $person['full_name'], 0, 1)) }}</span>
            @endif
            <div class="ub-person-text">
                <h3>{{ $person['full_name'] }}</h3>
                @if($personFacts)<p class="ub-person-line">{{ implode(' · ', $personFacts) }}</p>@endif
                @if($invitation->isMultiFamily() && ($person['father_name'] ?? null))
                    <p class="ub-person-parents">{{ $personLabel ?: 'Ananda' }} dari {{ $person['father_name'] }} &amp; {{ $person['mother_name'] }}</p>
                @endif
            </div>
        </li>
    @endforeach
</ul>
@unless($invitation->isMultiFamily())
    <p class="ub-people-parents">
        <span>{{ $invitation->child_relation ? Str::before($invitation->child_relation, ' dari ') : 'Ananda' }} dari pasangan</span>
        <strong>{{ $invitation->groom_full_name }}</strong><em aria-hidden="true">&amp;</em><strong>{{ $invitation->bride_full_name }}</strong>
    </p>
@endunless
