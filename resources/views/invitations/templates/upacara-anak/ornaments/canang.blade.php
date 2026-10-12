{{-- Canang sari seen from above: flowers of four colours on a janur tray, shredded pandan in the middle. --}}
<svg class="orn orn-canang {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="-74 -74 148 148" aria-hidden="true">
    <g filter="url(#ub-soft)">
        <rect x="-60" y="-60" width="120" height="120" rx="14" fill="url(#ub-janur)" stroke="#b5aa58" stroke-width="1"/>
        <path d="M-60-22L-22-60M-60 18L18-60M-60 58L58-60M-22 60L60-22M18 60L60 18" fill="none" stroke="#a79c4e" stroke-width="1" opacity=".35"/>
        <path d="M-60-40L-40-60M-60 0L0-60M-60 40L40-60M0 60L60 0M40 60L60 40" fill="none" stroke="#fffbe0" stroke-width="1" opacity=".6"/>
        <circle r="48" fill="url(#ub-tray)" stroke="#cfc47a" stroke-width="1"/>
        @for($i = 0; $i < 24; $i++)<path transform="rotate({{ $i * 15 }})" d="M0-48V-38" stroke="#cfc47a" stroke-width=".8"/>@endfor
        @foreach([[0, 'blue'], [90, 'white'], [180, 'red'], [270, 'yellow']] as [$turn, $colour])
            <g transform="rotate({{ $turn }}) translate(0 -26)">
                @for($i = 0; $i < 6; $i++)<ellipse transform="rotate({{ $i * 60 }})" cy="-8.5" rx="5.4" ry="9.2" fill="url(#ub-flower-{{ $colour }})" stroke="#00000022" stroke-width=".5"/>@endfor
                <circle r="3.2" fill="#f6d34a" stroke="#c9a02a" stroke-width=".5"/>
            </g>
        @endforeach
        @for($i = 0; $i < 16; $i++)<path transform="rotate({{ $i * 22.5 }})" d="M0-2V-{{ 10 + ($i % 3) * 3 }}" stroke="{{ $i % 2 ? '#5f8f45' : '#7fae5c' }}" stroke-width="2" stroke-linecap="round"/>@endfor
    </g>
</svg>
