{{-- The blurred shadow of a palm frond, as if sunlight fell across the page. --}}
<svg class="orn orn-bayang {{ $class ?? '' }}" viewBox="-40 -40 300 380" aria-hidden="true">
    <path d="M10 290L200 10" fill="none" stroke="currentColor" stroke-width="3"/>
    @for($i = 0; $i < 13; $i++)
        <g transform="translate({{ 10 + $i * 15.8 }} {{ 290 - $i * 23.3 }}) scale({{ 1 - $i * 0.04 }})" fill="currentColor">
            <path transform="rotate(-106)" d="M0 0C22-7 52-7 78 0C52 7 22 7 0 0Z"/>
            <path transform="rotate(-6)" d="M0 0C22-7 52-7 78 0C52 7 22 7 0 0Z"/>
        </g>
    @endfor
</svg>
