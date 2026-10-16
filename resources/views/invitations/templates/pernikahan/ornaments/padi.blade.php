{{-- A clump of rice ready for harvest: tapering stems, long blades of leaf and heavy heads of grain bending down.
     The base sits at the bottom edge; mirror it with class is-mirrored. --}}
<svg class="orn orn-padi {{ $class ?? '' }}" {!! $attrs ?? '' !!} viewBox="0 0 170 260" aria-hidden="true">
    <defs>
        <linearGradient id="pd-blade" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#4f7a33"/><stop offset=".6" stop-color="#7fa94f"/><stop offset="1" stop-color="#b9d27c"/></linearGradient>
        <linearGradient id="pd-stem" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#5b7d35"/><stop offset="1" stop-color="#a9b85a"/></linearGradient>
        <radialGradient id="pd-grain" cx=".35" cy=".3" r=".8"><stop offset="0" stop-color="#fff2b8"/><stop offset=".55" stop-color="#e2bd58"/><stop offset="1" stop-color="#b38a2f"/></radialGradient>
        <g id="pd-head">
            <path d="M0 0C10 10 18 26 22 46" fill="none" stroke="#b39445" stroke-width="1.4" stroke-linecap="round"/>
            <g fill="url(#pd-grain)">
                <ellipse cx="3" cy="6" rx="2.6" ry="4.6" transform="rotate(-30 3 6)"/><ellipse cx="8" cy="5" rx="2.6" ry="4.6" transform="rotate(30 8 5)"/>
                <ellipse cx="8" cy="14" rx="2.6" ry="4.6" transform="rotate(-24 8 14)"/><ellipse cx="14" cy="13" rx="2.6" ry="4.6" transform="rotate(34 14 13)"/>
                <ellipse cx="13" cy="23" rx="2.6" ry="4.6" transform="rotate(-18 13 23)"/><ellipse cx="19" cy="22" rx="2.6" ry="4.6" transform="rotate(38 19 22)"/>
                <ellipse cx="17" cy="32" rx="2.6" ry="4.6" transform="rotate(-12 17 32)"/><ellipse cx="23" cy="31" rx="2.6" ry="4.6" transform="rotate(40 23 31)"/>
                <ellipse cx="21" cy="41" rx="2.4" ry="4.4" transform="rotate(-6 21 41)"/>
            </g>
        </g>
    </defs>
    <g filter="url(#ub-soft)">
        <path d="M70 260C66 200 58 150 40 104L44 102C64 148 74 198 78 260Z" fill="url(#pd-stem)"/>
        <path d="M84 260C86 196 92 132 110 80L114 82C98 134 92 198 92 260Z" fill="url(#pd-stem)"/>
        <path d="M78 260C78 190 76 120 70 60L74 59C82 120 86 190 86 260Z" fill="url(#pd-stem)"/>
        <use href="#pd-head" transform="translate(42 102) rotate(150) scale(1.15)"/>
        <use href="#pd-head" transform="translate(112 80) rotate(-4) scale(1.2)"/>
        <use href="#pd-head" transform="translate(72 59) rotate(26) scale(1.25)"/>
        <g fill="url(#pd-blade)">
            <path d="M74 260C60 200 30 160 2 150C30 168 52 206 66 260Z"/>
            <path d="M86 260C104 196 138 150 168 136C140 160 112 204 96 260Z"/>
            <path d="M80 260C70 210 58 170 30 120C62 160 80 206 90 260Z" opacity=".85"/>
            <path d="M84 260C96 214 116 176 150 150C122 184 104 220 94 260Z" opacity=".85"/>
            <path d="M78 260C82 220 96 190 124 172C104 198 92 226 88 260Z" opacity=".7"/>
        </g>
        <g fill="none" stroke="#d9e8b0" stroke-width=".7" stroke-linecap="round" opacity=".6">
            <path d="M70 252C58 210 36 176 12 158"/><path d="M90 252C106 206 132 168 158 146"/>
        </g>
    </g>
</svg>
