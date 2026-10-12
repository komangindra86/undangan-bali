{{-- A wreath of jepun flowers and leaves; the middle is left open for a photo. --}}
<svg class="orn orn-karangan {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="-170 -170 340 340" aria-hidden="true">
    <g filter="url(#ub-soft)">
        @for($i = 0; $i < 18; $i++)
            <g transform="rotate({{ $i * 20 }}) translate(0 -124) rotate({{ $i % 2 ? 52 : 128 }})">
                <path d="M0 0C12-15 38-17 58-4C40 9 14 11 0 0Z" fill="url(#ub-leaf)" stroke="#6f9d58" stroke-width=".5"/>
                <path d="M4 0C20-4 38-5 54-4" fill="none" stroke="#e6f2d3" stroke-width=".8"/>
            </g>
        @endfor
        @for($i = 0; $i < 9; $i++)
            <use href="#ub-jepun" transform="rotate({{ $i * 40 + 8 }}) translate(0 -126) scale({{ $i % 2 ? .44 : .6 }}) rotate({{ $i * 37 }})"/>
        @endfor
    </g>
</svg>
