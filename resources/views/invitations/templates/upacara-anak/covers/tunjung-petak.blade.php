<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset=".6" stop-color="#f4faf2"/><stop offset="1" stop-color="#e3f0ea"/></linearGradient>
        <linearGradient id="air" x2="0" y2="1"><stop offset="0" stop-color="#cfe9ec" stop-opacity="0"/><stop offset="1" stop-color="#b9dde4"/></linearGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <rect y="690" width="600" height="310" fill="url(#air)"/>
    @include($ornaments.'tunjung', ['attrs' => 'x="166" y="130" width="268" height="158"'])
    <g opacity=".7">@include($ornaments.'kolam', ['attrs' => 'x="0" y="700" width="600" height="315"'])</g>
</svg>
