<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <radialGradient id="bg" cx=".5" cy=".28" r=".85"><stop offset="0" stop-color="#fffef9"/><stop offset=".6" stop-color="#fbf1dc"/><stop offset="1" stop-color="#eedbb2"/></radialGradient>
        <pattern id="prada" width="56" height="56" patternUnits="userSpaceOnUse"><g fill="none" stroke="#c9a04e" stroke-opacity=".18"><path d="M28 14c6 6 6 22 0 28c-6-6-6-22 0-28zM14 28c6-6 22-6 28 0c-6 6-22 6-28 0z"/><circle r="5"/><circle cx="56" r="5"/><circle cy="56" r="5"/><circle cx="56" cy="56" r="5"/></g></pattern>
        <linearGradient id="ground" x2="0" y2="1"><stop offset="0" stop-color="#e6d2a8" stop-opacity="0"/><stop offset="1" stop-color="#e0c996"/></linearGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <rect width="600" height="1000" fill="url(#prada)"/><rect y="900" width="600" height="100" fill="url(#ground)"/>
    @include($ornaments.'gapura', ['attrs' => 'x="70" y="648" width="165" height="352"'])
    <g transform="translate(600 0) scale(-1 1)">@include($ornaments.'gapura', ['attrs' => 'x="70" y="648" width="165" height="352"'])</g>
    <use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(500 120) rotate(18) scale(1.15)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(430 200) rotate(-26) scale(0.62)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(104 136) rotate(44) scale(0.86)"/>
</svg>
