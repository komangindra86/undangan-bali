<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <radialGradient id="bg" cx=".5" cy=".28" r=".85"><stop offset="0" stop-color="#fffdf5"/><stop offset=".6" stop-color="#fbf2dd"/><stop offset="1" stop-color="#f0dfbb"/></radialGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    @include($ornaments.'lamak', ['attrs' => 'x="92" y="-4" width="57" height="300"'])
    @include($ornaments.'lamak', ['attrs' => 'x="451" y="-4" width="57" height="300"'])
    <use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(300 150) rotate(0) scale(1)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(232 902) rotate(30) scale(0.8)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(300 930) rotate(-14) scale(1)"/><use href="#ub-jepun" filter="url(#ub-soft)" transform="translate(368 904) rotate(52) scale(0.7)"/>
</svg>
