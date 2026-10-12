{{-- Gebogan: the tall offering of fruit on a dulang, crowned with janur and a flower. --}}
<svg class="orn orn-gebogan {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="0 0 120 276" aria-hidden="true">
    <g filter="url(#ub-soft)">
        <g transform="translate(60 76)" fill="url(#ub-janur)" stroke="#b9ae5c" stroke-width=".5">
            @foreach([-60, -40, -20, 0, 20, 40, 60] as $turn)
                <path transform="rotate({{ $turn }})" d="M0 0C-5-20-4-42 0-{{ 62 - abs($turn) / 6 }}C4-42 5-20 0 0Z"/>
            @endforeach
        </g>
        <path d="M36 70H84L108 222H12Z" fill="#f3ead0"/>
        @foreach([5, 5, 4, 4, 3, 3, 2] as $row => $count)
            @for($i = 0; $i < $count; $i++)
                <circle cx="{{ 60 + ($i - ($count - 1) / 2) * 20.5 }}" cy="{{ 210 - $row * 22 }}" r="11.5" fill="url(#ub-fruit-{{ ['orange', 'red', 'green'][($row + $i) % 3] }})"/>
            @endfor
        @endforeach
        <path d="M8 222H112L100 236H20Z" fill="url(#ub-gold)"/><path d="M40 236H80L88 268H32Z" fill="url(#ub-gold)"/>
        <path d="M9 222.7H111" stroke="#fff6d6" stroke-width="1.3" opacity=".85"/>
        <use href="#ub-jepun" transform="translate(60 70) scale(.3)"/>
    </g>
</svg>
