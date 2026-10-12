<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#fbe3e8"/><stop offset="1" stop-color="#fff7f2"/></linearGradient>
        <radialGradient id="bokeh"><stop offset="0" stop-color="#fff" stop-opacity=".85"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
        <radialGradient id="blush"><stop offset="0" stop-color="#f9c9d4" stop-opacity=".7"/><stop offset="1" stop-color="#f9c9d4" stop-opacity="0"/></radialGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <circle cx="150" cy="240" r="120" fill="url(#bokeh)"/><circle cx="430" cy="560" r="170" fill="url(#blush)"/><circle cx="190" cy="760" r="140" fill="url(#bokeh)"/><circle cx="470" cy="150" r="90" fill="url(#bokeh)"/>
    @include($ornaments.'penjor', ['attrs' => 'x="262" y="40" width="300" height="930"'])
    <g transform="translate(600 0) scale(-1 1)">@include($ornaments.'penjor', ['attrs' => 'x="262" y="40" width="300" height="930"'])</g>
    <path d="M520 960V1000M80 960V1000" stroke="#b99a5e" stroke-width="8" stroke-linecap="round"/>
    <use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(300 930) rotate(0) scale(0.9)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(236 958) rotate(40) scale(0.6)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(366 962) rotate(-30) scale(0.66)"/>
</svg>
