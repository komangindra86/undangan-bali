{{-- Sunrise beside Gunung Agung, with a meru, a coconut palm and rice terraces in the mist. --}}
<svg class="orn orn-fajar {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="0 0 400 262" aria-hidden="true">
    <circle cx="266" cy="126" r="122" fill="url(#ub-sunglow)"/>
    <circle cx="266" cy="126" r="44" fill="url(#ub-sun)"/>
    <path d="M0 216L70 190L128 134L160 110L182 116L204 102L232 124L296 188L400 224V262H0Z" fill="url(#ub-mount)"/>
    <rect y="170" width="400" height="60" fill="url(#ub-mist)"/>
    <path d="M0 228C70 206 130 218 200 210C270 202 330 216 400 204V262H0Z" fill="url(#ub-hill)" opacity=".8"/>
    <g opacity=".85">
        <rect x="58" y="206" width="32" height="12" fill="#b99c82"/>
        @for($i = 0; $i < 5; $i++)
            <rect x="{{ 74 - (11 - $i * 2) }}" y="{{ 191 - $i * 15 }}" width="{{ 2 * (11 - $i * 2) }}" height="8" fill="#a98a70"/>
            <path d="M{{ 74 - (21 - $i * 3) }} {{ 206 - $i * 15 }}H{{ 74 + (21 - $i * 3) }}L{{ 74 + (15 - $i * 3) }} {{ 198 - $i * 15 }}H{{ 74 - (15 - $i * 3) }}Z" fill="#7d5d4a"/>
        @endfor
        <path d="M74 131V122" stroke="#7d5d4a" stroke-width="1.6" stroke-linecap="round"/>
    </g>
    <g fill="none" stroke-linecap="round">
        <path d="M342 240C340 216 346 198 352 182" stroke="#9a7a5c" stroke-width="3.2"/>
        <path d="M352 182Q332 162 308 174M352 182Q338 152 316 148M352 182Q354 154 342 138M352 182Q368 158 390 160M352 182Q374 168 394 184M352 182Q362 180 374 198" stroke="#7ba25a" stroke-width="3.6"/>
    </g>
    <path d="M0 246C80 228 150 242 220 234C290 226 340 240 400 232V262H0Z" fill="url(#ub-hill)"/>
    <path d="M0 252C80 236 150 249 220 242C290 235 340 247 400 240M0 258C90 244 150 255 220 249C290 243 340 253 400 247" fill="none" stroke="#e8f2d0" stroke-width="1.1" opacity=".85"/>
</svg>
