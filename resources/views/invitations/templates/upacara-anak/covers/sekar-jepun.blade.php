<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#fffef7"/><stop offset="1" stop-color="#fcefc4"/></linearGradient>
        <radialGradient id="bokeh"><stop offset="0" stop-color="#fff" stop-opacity=".85"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <circle cx="140" cy="170" r="130" fill="url(#bokeh)"/><circle cx="470" cy="820" r="150" fill="url(#bokeh)"/>
    @include($ornaments.'karangan', ['attrs' => 'x="-30" y="125" width="660" height="660"'])
</svg>
