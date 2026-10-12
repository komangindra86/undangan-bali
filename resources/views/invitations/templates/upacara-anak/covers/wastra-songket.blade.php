<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice">
    @include($parts.'defs')
    <defs>
        <linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#f3e6ef"/><stop offset="1" stop-color="#fbf7fa"/></linearGradient>
        <pattern id="songket" width="40" height="40" patternUnits="userSpaceOnUse"><rect width="40" height="40" fill="#7a4168"/><g fill="none" stroke="#e9c877" stroke-width="1.2"><path d="M20 6l14 14-14 14-14-14z"/><path d="M20 13l7 7-7 7-7-7z"/></g><g fill="#e9c877"><circle cx="20" cy="20" r="1.6"/><circle r="2"/><circle cx="40" r="2"/><circle cy="40" r="2"/><circle cx="40" cy="40" r="2"/></g></pattern>
        <linearGradient id="fold"><stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".2" stop-color="#fff" stop-opacity=".22"/><stop offset=".45" stop-color="#000" stop-opacity=".14"/><stop offset=".7" stop-color="#fff" stop-opacity=".16"/><stop offset="1" stop-color="#000" stop-opacity=".1"/></linearGradient>
        <radialGradient id="bokeh"><stop offset="0" stop-color="#fff" stop-opacity=".85"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
    </defs>
    <rect width="600" height="1000" fill="url(#bg)"/>
    <rect width="600" height="150" fill="url(#songket)"/><rect width="600" height="150" fill="url(#fold)"/><rect y="150" width="600" height="8" fill="#e9c877"/>
    <rect y="900" width="600" height="100" fill="url(#songket)"/><rect y="900" width="600" height="100" fill="url(#fold)"/><rect y="892" width="600" height="8" fill="#e9c877"/>
    <circle cx="140" cy="420" r="150" fill="url(#bokeh)"/><circle cx="470" cy="680" r="140" fill="url(#bokeh)"/>
</svg>
