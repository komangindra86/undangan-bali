{{-- Roda pawukon: 30 wuku on the rim, the seven and five day weeks inside; 30 x 7 = 210 days, one oton. --}}
<svg class="orn orn-pawukon {{ $class ?? '' }}" viewBox="-100 -100 200 200" aria-hidden="true">
    <circle class="orn-line" r="97"/><circle class="orn-line" r="80"/>
    @for($i = 0; $i < 30; $i++)
        <path class="orn-line" transform="rotate({{ $i * 12 }})" d="M0-80V-97"/>
        <circle class="{{ $i % 5 === 0 ? 'orn-dot' : 'orn-line' }}" transform="rotate({{ $i * 12 + 6 }})" cy="-88.5" r="3"/>
    @endfor
    <circle class="orn-line" r="66" stroke-dasharray="2 6"/>
    @for($i = 0; $i < 7; $i++)<circle class="orn-dot" transform="rotate({{ $i * 51.43 }})" cy="-66" r="4"/>@endfor
    @for($i = 0; $i < 5; $i++)<path class="orn-cloth" transform="rotate({{ $i * 72 + 36 }})" d="M0-58l5 7-5 7-5-7z"/>@endfor
    <circle class="orn-moon" r="42"/>
</svg>
