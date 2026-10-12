{{-- Canang sari seen from above: flowers of four colours on a janur tray, shredded pandan in the middle. --}}
<svg class="orn orn-canang {{ $class ?? '' }}" viewBox="-70 -70 140 140" aria-hidden="true">
    <rect x="-58" y="-58" width="116" height="116" rx="12" fill="#e4d993" stroke="#9b8d3f" stroke-width="2"/>
    <path d="M-58-20L-20-58M-58 20L20-58M-58 58L58-58M-20 58L58-20M20 58L58 20" stroke="#9b8d3f" stroke-width="1" opacity=".45"/>
    <circle r="46" fill="#f6f0c6" stroke="#b9ab57" stroke-width="1.5"/>
    @for($i = 0; $i < 18; $i++)<path transform="rotate({{ $i * 20 }})" d="M0-46V-36" stroke="#b9ab57" stroke-width="1"/>@endfor
    @foreach([[0, '#4a5fa8'], [90, '#ffffff'], [180, '#c6352d'], [270, '#f2c230']] as [$turn, $colour])
        <g transform="rotate({{ $turn }}) translate(0 -26)">
            @for($i = 0; $i < 5; $i++)<ellipse transform="rotate({{ $i * 72 }})" cy="-8" rx="5.6" ry="9" fill="{{ $colour }}" stroke="#7d6a2a" stroke-width=".8"/>@endfor
            <circle r="3.2" fill="#f6d34a" stroke="#7d6a2a" stroke-width=".6"/>
        </g>
    @endforeach
    @for($i = 0; $i < 14; $i++)<path transform="rotate({{ $i * 25.7 }})" d="M0-3V-13" stroke="#4f7d3a" stroke-width="2" stroke-linecap="round"/>@endfor
</svg>
