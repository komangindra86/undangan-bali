{{-- Sekar emas: the fan of golden flowers worn as a crown in payas agung, the attire of a mepandes. --}}
<svg class="orn orn-sekar-emas {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="-170 -150 340 176" aria-hidden="true">
    <g filter="url(#ub-soft)">
        @foreach([-78, -65, -52, -39, -26, -13, 0, 13, 26, 39, 52, 65, 78] as $index => $turn)
            <g transform="rotate({{ $turn }}) scale({{ $index % 2 ? .82 : 1 }})">
                <path d="M0 0C-10-32-9-78 0-118C9-78 10-32 0 0Z" fill="url(#ub-gold)" stroke="#fff3cc" stroke-width=".8"/>
                <path d="M0-12V-104" fill="none" stroke="#fff6d6" stroke-width=".8" opacity=".7"/>
                <circle cy="-124" r="5" fill="#fff8e0" stroke="#d7b05a" stroke-width="1"/>
            </g>
        @endforeach
        <path d="M-96 6Q0-34 96 6" fill="none" stroke="url(#ub-gold)" stroke-width="9" stroke-linecap="round"/>
        @foreach([[-78, -4, .26], [-40, -15, .3], [0, -19, .36], [40, -15, .3], [78, -4, .26]] as [$x, $y, $scale])
            <use href="#ub-jepun" transform="translate({{ $x }} {{ $y }}) scale({{ $scale }}) rotate({{ $x }})"/>
        @endforeach
    </g>
</svg>
