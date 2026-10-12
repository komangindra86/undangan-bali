{{-- Roda pawukon around the full moon: 30 wuku on the rim and the seven-day week inside; 30 x 7 = 210 days, one oton. --}}
<svg class="orn orn-pawukon {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="-112 -112 224 224" aria-hidden="true">
    <circle r="110" fill="url(#ub-glow)"/>
    <g class="orn-wheel">
        <circle class="orn-line" r="97"/><circle class="orn-line" r="82"/>
        @for($i = 0; $i < 30; $i++)
            <path class="orn-line" transform="rotate({{ $i * 12 }})" d="M0-82V-97"/>
            <circle class="orn-dot" transform="rotate({{ $i * 12 + 6 }})" cy="-89.5" r="{{ $i % 5 === 0 ? 3 : 1.6 }}"/>
        @endfor
        <circle class="orn-line" r="68" stroke-dasharray="1.5 6"/>
        @for($i = 0; $i < 7; $i++)<circle class="orn-dot" transform="rotate({{ $i * 51.43 }})" cy="-68" r="3.6"/>@endfor
    </g>
    <circle r="46" fill="url(#ub-moon)"/>
    <g filter="url(#ub-haze)" fill="#d9c684" opacity=".5">
        <ellipse cx="-15" cy="-11" rx="10" ry="7"/><ellipse cx="13" cy="9" rx="13" ry="8"/><ellipse cx="9" cy="-22" rx="5" ry="4"/><ellipse cx="-17" cy="17" rx="6" ry="4"/>
    </g>
</svg>
