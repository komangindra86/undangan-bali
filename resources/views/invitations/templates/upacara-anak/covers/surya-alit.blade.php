<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#ffe4c4"/><stop offset=".5" stop-color="#fff1dc"/><stop offset="1" stop-color="#fff8ee"/></linearGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <g opacity=".92">@include($ornaments.'fajar', ['attrs' => 'x="0" y="607" width="600" height="393"'])</g>
</svg>
