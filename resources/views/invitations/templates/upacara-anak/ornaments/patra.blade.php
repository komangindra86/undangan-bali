{{-- Patra divider: a small lotus between two scrolls. --}}
<svg class="orn orn-patra {{ $class ?? '' }}" viewBox="0 0 240 28" aria-hidden="true">
    <path class="orn-cloth" d="M120 3c5 5 5 14 0 20c-5-6-5-15 0-20z"/>
    <path class="orn-line" d="M120 23c-8-2-12-8-11-15c7 1 11 8 11 15zM120 23c8-2 12-8 11-15c-7 1-11 8-11 15z"/>
    @foreach([false, true] as $mirrored)
        <g @if($mirrored) transform="translate(240 0) scale(-1 1)" @endif>
            <path class="orn-line" d="M104 18C90 18 84 7 72 7c-9 0-14 7-11 13c2 5 9 5 10 0c1-3-3-5-5-3M58 18H12M86 18c-4 5-10 6-14 4"/>
            <circle class="orn-dot" cx="7" cy="18" r="2.5"/>
        </g>
    @endforeach
</svg>
