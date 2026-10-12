<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#bfe0f4"/><stop offset=".7" stop-color="#e6f4fb"/><stop offset="1" stop-color="#f7fcfe"/></linearGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    @include($ornaments.'mega', ['attrs' => 'x="60" y="150" width="220" height="108"'])
    @include($ornaments.'mega', ['attrs' => 'x="330" y="640" width="190" height="93"'])
    <g opacity=".8">@include($ornaments.'mega', ['attrs' => 'x="90" y="800" width="150" height="73"'])</g>
    @include($ornaments.'layangan', ['attrs' => 'x="372" y="50" width="168" height="560"'])
</svg>
