<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <radialGradient id="bg" cx=".5" cy=".28" r=".85"><stop offset="0" stop-color="#fff6ea"/><stop offset=".6" stop-color="#fbe5d2"/><stop offset="1" stop-color="#f3ccb2"/></radialGradient>
        <pattern id="bata" width="60" height="24" patternUnits="userSpaceOnUse"><rect width="60" height="24" fill="#f1dfc8"/><rect x="1" y="1" width="58" height="10" rx="1.5" fill="#d98e6c"/><rect x="-29" y="13" width="58" height="10" rx="1.5" fill="#cf8261"/><rect x="31" y="13" width="58" height="10" rx="1.5" fill="#cf8261"/></pattern>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    @include($ornaments.'umbul', ['attrs' => 'x="76" y="376" width="120" height="573"'])
    <g transform="translate(600 0) scale(-1 1)">@include($ornaments.'umbul', ['attrs' => 'x="76" y="376" width="120" height="573"'])</g>
    @include($ornaments.'gebogan', ['attrs' => 'x="252" y="96" width="96" height="221"'])
    <rect y="952" width="600" height="48" fill="url(#bata)"/><rect y="946" width="600" height="8" fill="#e9dcc6"/>
</svg>
