<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#2f3069"/><stop offset=".45" stop-color="#4a4789"/><stop offset=".8" stop-color="#77669f"/><stop offset="1" stop-color="#9b80b3"/></linearGradient>
        <pattern id="poleng" width="40" height="40" patternUnits="userSpaceOnUse"><rect width="40" height="40" fill="#f8f5ee"/><rect width="20" height="40" fill="#2c2c44" fill-opacity=".5"/><rect width="40" height="20" fill="#2c2c44" fill-opacity=".5"/></pattern>
        <linearGradient id="fold"><stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".18" stop-color="#fff" stop-opacity=".35"/><stop offset=".42" stop-color="#000" stop-opacity=".12"/><stop offset=".68" stop-color="#fff" stop-opacity=".25"/><stop offset="1" stop-color="#000" stop-opacity=".1"/></linearGradient>
    </defs>
    <style>.orn-line{fill:none;stroke:#f8ebbd;stroke-width:1.1}.orn-dot{fill:#f8ebbd}.orn-wheel{opacity:.75}</style>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <g fill="#fff" opacity=".8"><circle cx="120" cy="190" r="2"/><circle cx="470" cy="150" r="2.4"/><circle cx="520" cy="420" r="1.8"/><circle cx="90" cy="500" r="2"/><circle cx="440" cy="640" r="1.6"/><circle cx="170" cy="700" r="1.8"/></g>
    @include($ornaments.'pawukon', ['attrs' => 'x="143" y="79" width="314" height="314"'])
    @include($ornaments.'awan', ['attrs' => 'x="29" y="262" width="238" height="92"'])
    <g transform="translate(600 0) scale(-1 1)">@include($ornaments.'awan', ['attrs' => 'x="38" y="128" width="204" height="79"'])</g>
    <g opacity=".6">@include($ornaments.'awan', ['attrs' => 'x="340" y="590" width="187" height="73"'])</g>
    <rect width="600" height="56" fill="url(#poleng)"/><rect width="600" height="56" fill="url(#fold)"/>
    <rect y="944" width="600" height="56" fill="url(#poleng)"/><rect y="944" width="600" height="56" fill="url(#fold)"/>
</svg>
