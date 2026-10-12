<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <radialGradient id="bg" cx=".5" cy=".28" r=".85"><stop offset="0" stop-color="#fffef9"/><stop offset=".6" stop-color="#fbf3df"/><stop offset="1" stop-color="#efdfb9"/></radialGradient>
        <linearGradient id="ground" x2="0" y2="1"><stop offset="0" stop-color="#e6d2a8" stop-opacity="0"/><stop offset="1" stop-color="#e0c996"/></linearGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <rect y="880" width="600" height="120" fill="url(#ground)"/>
    @include($ornaments.'bale', ['attrs' => 'x="130" y="660" width="340" height="266"'])
    <use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(500 130) rotate(18) scale(1.1)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(108 150) rotate(44) scale(0.8)"/>
</svg>
