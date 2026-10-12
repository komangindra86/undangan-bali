{{-- Patra divider: a small jepun between two scrolls. --}}
<svg class="orn orn-patra {{ $class ?? '' }}" viewBox="0 0 240 30" aria-hidden="true">
    @foreach([false, true] as $mirrored)
        <g @if($mirrored) transform="translate(240 0) scale(-1 1)" @endif>
            <path class="orn-line" d="M102 18C90 18 84 7 72 7c-9 0-14 7-11 13c2 5 9 5 10 0c1-3-3-5-5-3M58 18H14M86 18c-4 5-10 6-14 4"/>
            <circle class="orn-dot" cx="9" cy="18" r="2"/>
        </g>
    @endforeach
    <use href="#ub-jepun" transform="translate(120 16) scale(.24)"/>
</svg>
