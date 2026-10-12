<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#fbe6d6"/><stop offset="1" stop-color="#fff9f4"/></linearGradient>
        <radialGradient id="bokeh"><stop offset="0" stop-color="#fff" stop-opacity=".85"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <circle cx="130" cy="700" r="150" fill="url(#bokeh)"/><circle cx="480" cy="560" r="130" fill="url(#bokeh)"/>
    @include($ornaments.'sekar-emas', ['attrs' => 'x="90" y="96" width="420" height="217"'])
    <use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(232 912) rotate(30) scale(0.7)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(300 936) rotate(-14) scale(0.9)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(368 914) rotate(52) scale(0.62)"/>
</svg>
