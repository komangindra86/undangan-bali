{{-- A jepun branch drawn to look natural: a tapering grey-brown twig, leaves in two shades with their midribs, a
     cluster of open flowers turned at different angles, and two buds. Mirror it with class is-mirrored. --}}
<svg class="orn orn-ranting {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="0 0 320 230" aria-hidden="true">
    <defs>
        <linearGradient id="rj-leaf" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#a9cc7c"/><stop offset=".55" stop-color="#6f9a4c"/><stop offset="1" stop-color="#4c7536"/></linearGradient>
        <linearGradient id="rj-leaf-back" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#86ab62"/><stop offset="1" stop-color="#3f6430"/></linearGradient>
        <linearGradient id="rj-twig" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#a49380"/><stop offset="1" stop-color="#6e5d4c"/></linearGradient>
        <radialGradient id="rj-bud" cx=".4" cy=".3" r=".8"><stop offset="0" stop-color="#fffdf6"/><stop offset=".7" stop-color="#f6e7d0"/><stop offset="1" stop-color="#e8c9a6"/></radialGradient>
        <path id="rj-leaf-shape" d="M0 0C18-15 64-19 98-4C66 11 20 14 0 0Z"/>
    </defs>
    <g filter="url(#ub-soft)">
        <path d="M-6 38C40 36 84 46 126 70C160 90 196 112 238 118C252 120 262 118 270 114L271 119C262 124 251 126 237 125C193 120 157 98 122 78C82 55 40 45-6 46Z" fill="url(#rj-twig)"/>
        <path d="M128 76C140 60 150 44 168 36L170 40C154 48 144 62 134 80Z" fill="url(#rj-twig)"/>
        <g fill="url(#rj-leaf-back)">
            <use href="#rj-leaf-shape" transform="translate(150 84) rotate(-62) scale(.92)"/>
            <use href="#rj-leaf-shape" transform="translate(236 118) rotate(-24) scale(.86)"/>
            <use href="#rj-leaf-shape" transform="translate(98 62) rotate(-118) scale(.8)"/>
        </g>
        <g fill="url(#rj-leaf)">
            <use href="#rj-leaf-shape" transform="translate(152 88) rotate(28) scale(.98)"/>
            <use href="#rj-leaf-shape" transform="translate(240 120) rotate(46) scale(.82)"/>
            <use href="#rj-leaf-shape" transform="translate(166 40) rotate(-14) scale(.74)"/>
            <use href="#rj-leaf-shape" transform="translate(100 64) rotate(74) scale(.78)"/>
        </g>
        <g fill="none" stroke="#e7f1d6" stroke-width=".9" stroke-linecap="round" opacity=".75">
            <path d="M154 90Q196 104 236 132"/><path d="M242 122Q268 140 290 170"/><path d="M168 42Q206 34 238 22"/><path d="M102 66Q112 108 124 138"/>
        </g>
        <path d="M266 116C278 108 286 100 290 88" stroke="#7e6c58" stroke-width="2.4" fill="none" stroke-linecap="round"/>
        <ellipse cx="292" cy="80" rx="5.5" ry="13" transform="rotate(24 292 80)" fill="url(#rj-bud)"/>
        <ellipse cx="282" cy="96" rx="4.5" ry="11" transform="rotate(62 282 96)" fill="url(#rj-bud)"/>
        <use href="#ub-jepun" transform="translate(212 90) rotate(14) scale(.5)"/>
        <use href="#ub-jepun" transform="translate(252 80) rotate(-30) scale(.42)"/>
        <use href="#ub-jepun" transform="translate(242 144) rotate(40) scale(.46)"/>
        <use href="#ub-jepun" transform="translate(276 128) rotate(8) scale(.36)"/>
        <use href="#ub-jepun" transform="translate(186 56) rotate(-18) scale(.34)"/>
    </g>
</svg>
