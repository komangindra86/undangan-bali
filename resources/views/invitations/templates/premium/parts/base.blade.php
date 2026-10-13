{{-- Styles every premium design shares: ornaments, entrances after the cover opens, scroll reveals, photo viewer. --}}
<script>document.documentElement.classList.add('js');</script>
<style>
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    html, body { min-width: 0; overflow-x: hidden; }
    body { margin: 0; }
    img { display: block; max-width: 100%; }
    .ub-defs { position: absolute; }
    .orn { display: block; height: auto; overflow: visible; }
    .orn-line { fill: none; stroke: var(--orn, #b3863a); stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.1; }
    .orn-dot { fill: var(--orn, #b3863a); }
    .orn.is-mirrored { transform: scaleX(-1); }
    .orn-patra { margin: 0 auto 22px; max-width: 72%; width: 220px; }
    .pm-page { margin: 0 auto; max-width: 520px; overflow: hidden; position: relative; }
    .pm-demo { background: #fff4d6; border-bottom: 1px solid #ecd9a8; color: #6c5320; font: 12px/1.5 system-ui, sans-serif; margin: 0; padding: 9px 16px; position: relative; text-align: center; z-index: 3; }
    .pm-motes { inset: 0; overflow: hidden; pointer-events: none; position: absolute; }
    .pm-motes i { animation: pm-twinkle 3.6s ease-in-out infinite; background: radial-gradient(circle, #fffbe6, var(--mote, #e7c574) 55%, transparent 72%); border-radius: 50%; height: 6px; opacity: 0; position: absolute; width: 6px; }

    /* Pieces of the first screen wait for the opening cover, then arrive in the order --i gives them. */
    .js [data-enter] { opacity: 0; }
    html.js:not(.invitation-pending) [data-enter] { animation: pm-rise .95s cubic-bezier(.2, .75, .25, 1) calc(var(--i, 0) * .24s + .2s) both; }
    html.js:not(.invitation-pending) [data-enter="bloom"] { animation-name: pm-bloom; animation-duration: 1.4s; }
    html.js:not(.invitation-pending) [data-enter="drop"] { animation-name: pm-drop; animation-duration: 1.5s; }

    .js [data-reveal] { opacity: 0; transform: translateY(26px); transition: opacity .8s ease var(--d, 0s), transform .9s cubic-bezier(.2, .75, .25, 1) var(--d, 0s); }
    .js [data-reveal="left"] { transform: translateX(-46px); }
    .js [data-reveal="right"] { transform: translateX(46px); }
    .js [data-reveal="zoom"] { transform: scale(.84); }
    .js [data-reveal="flip"] { transform: perspective(900px) rotateY(70deg); }
    .js [data-reveal].is-revealed { opacity: 1; transform: none; }
    [data-countdown] strong.is-tick { animation: pm-tick .5s ease; display: inline-block; }
    [data-gallery], [data-gallery-ref] { cursor: zoom-in; }

    .pm-lightbox { align-items: center; background: rgba(24, 14, 8, .94); display: flex; inset: 0; justify-content: center; position: fixed; z-index: 60; }
    .pm-lightbox[hidden] { display: none; }
    .pm-lightbox img { animation: pm-bloom .45s cubic-bezier(.2, .75, .25, 1) both; border-radius: 14px; box-shadow: 0 30px 80px #000a; max-height: 82vh; max-width: 92vw; }
    .pm-lightbox button { background: rgba(255, 248, 232, .14); border: 1px solid rgba(240, 215, 159, .5); border-radius: 999px; color: #fff5e1; cursor: pointer; font: 400 26px/1 Georgia, serif; height: 46px; position: absolute; width: 46px; }
    .pm-lightbox [data-lightbox-close] { right: 16px; top: 16px; }
    .pm-lightbox [data-lightbox-prev] { left: 12px; top: calc(50% - 23px); }
    .pm-lightbox [data-lightbox-next] { right: 12px; top: calc(50% - 23px); }
    .pm-lightbox p { bottom: 18px; color: #f0d79f; font: 12px system-ui, sans-serif; left: 0; letter-spacing: .2em; margin: 0; position: absolute; right: 0; text-align: center; }
    .pm-player { bottom: 18px; position: fixed; right: 18px; z-index: 30; }
    .pm-player button { background: #fffaf0; border: 1px solid var(--pm-accent, #d9bb78); border-radius: 999px; box-shadow: 0 10px 24px rgba(40, 40, 30, .22); color: var(--pm-accent-ink, #8a672a); cursor: pointer; font: 700 12px system-ui, sans-serif; min-height: 44px; padding: 0 18px; }
    .pm-player button.is-playing { animation: pm-pulse 1.9s ease-out infinite; }

    @keyframes pm-rise { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: none; } }
    @keyframes pm-bloom { from { filter: blur(8px); opacity: 0; transform: scale(.84); } to { filter: blur(0); opacity: 1; transform: none; } }
    @keyframes pm-drop { from { opacity: 0; transform: translateY(-60px); } to { opacity: 1; transform: none; } }
    @keyframes pm-tick { from { opacity: .35; transform: translateY(-7px); } to { opacity: 1; transform: none; } }
    @keyframes pm-twinkle { 0%, 100% { opacity: 0; transform: scale(.4); } 50% { opacity: .95; transform: scale(1.35); } }
    @keyframes pm-pulse { 0% { box-shadow: 0 10px 24px rgba(40, 40, 30, .22), 0 0 0 0 rgba(201, 162, 83, .55); } 100% { box-shadow: 0 10px 24px rgba(40, 40, 30, .22), 0 0 0 16px rgba(201, 162, 83, 0); } }
    @keyframes pm-spin { to { transform: rotate(360deg); } }
    @keyframes pm-bob { 50% { translate: 0 -8px; } }
    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        .pm-page *, .pm-page *::before, .pm-page *::after, .pm-lightbox img { animation: none !important; transition: none !important; }
        .pm-motes { display: none; }
        .js [data-enter], .js [data-reveal] { opacity: 1; transform: none; }
    }
</style>
