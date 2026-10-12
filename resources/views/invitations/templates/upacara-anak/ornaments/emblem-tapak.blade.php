{{-- Tigang sasih: two small footprints, the child's first steps on Ibu Pertiwi. --}}
<svg class="orn orn-emblem" viewBox="0 0 120 100" aria-hidden="true">
    <ellipse cx="60" cy="84" rx="46" ry="9" fill="#c9a06a" opacity=".2"/>
    <g filter="url(#ub-soft)" fill="url(#ub-earth)">
        @foreach([[42, 54, -12, 1], [78, 46, 12, -1]] as [$x, $y, $turn, $side])
            <g transform="translate({{ $x }} {{ $y }}) rotate({{ $turn }}) scale({{ $side }} 1)">
                <path d="M0-16C9-16 12-7 11 5C10 17 6 26 0 26C-6 26-10 17-11 5C-12-7-9-16 0-16Z"/>
                <circle cx="-9" cy="-23" r="3.8"/><circle cx="-3" cy="-27" r="3.1"/><circle cx="3" cy="-27.5" r="2.8"/><circle cx="8" cy="-25.5" r="2.5"/><circle cx="12" cy="-22" r="2.2"/>
            </g>
        @endforeach
    </g>
</svg>
