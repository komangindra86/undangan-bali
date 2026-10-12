{{-- Kolam tunjung: white lotus flowers and leaves on still water. --}}
<svg class="orn orn-kolam {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="0 0 400 210" aria-hidden="true">
    <ellipse cx="200" cy="168" rx="190" ry="20" fill="none" stroke="#ffffff" stroke-width="1.4" opacity=".8"/>
    <ellipse cx="200" cy="166" rx="128" ry="13" fill="none" stroke="#ffffff" stroke-width="1.2" opacity=".7"/>
    <g filter="url(#ub-soft)">
        <ellipse cx="84" cy="160" rx="66" ry="17" fill="url(#ub-leaf)"/><path d="M84 160L22 156M84 160L40 148M84 160L84 143M84 160L128 148M84 160L148 158" fill="none" stroke="#e6f2d3" stroke-width=".8" opacity=".8"/>
        <ellipse cx="322" cy="170" rx="58" ry="15" fill="url(#ub-leaf)"/><path d="M322 170L268 166M322 170L286 158M322 170L322 155M322 170L360 158M322 170L378 168" fill="none" stroke="#e6f2d3" stroke-width=".8" opacity=".8"/>
        <ellipse cx="214" cy="184" rx="74" ry="16" fill="url(#ub-leaf)"/>
        <use href="#ub-tunjung" transform="translate(90 148) scale(.5)"/>
        <use href="#ub-tunjung" transform="translate(326 160) scale(.42)"/>
        <use href="#ub-tunjung" transform="translate(204 172) scale(1.08)"/>
    </g>
</svg>
