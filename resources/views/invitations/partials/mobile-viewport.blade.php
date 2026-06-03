<style>
    html,
    body {
        min-width: 0;
        overflow-x: hidden;
        -webkit-text-size-adjust: 100%;
    }

    .page {
        width: 100%;
        max-width: min(var(--invitation-page-max, 560px), 100vw);
    }

    @supports (min-height: 100svh) {
        .hero,
        .cover {
            min-height: 100svh;
        }
    }

    @media (hover: none) and (pointer: coarse) {
        .page {
            box-shadow: none !important;
            max-width: none !important;
            width: 100vw !important;
        }

        .banner,
        .preview-banner {
            max-width: 100vw;
            width: 100vw;
        }
    }

    html.invitation-wide-viewport body {
        min-height: calc(100vh / var(--invitation-viewport-factor));
        width: calc(100vw / var(--invitation-viewport-factor));
        zoom: var(--invitation-viewport-factor);
    }
</style>
<script>
    (function () {
        function normalizeMobileViewport() {
            var screenWidth = Math.min(window.screen.width || 0, window.screen.height || 0);
            var viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;

            if (screenWidth >= 320 && screenWidth <= 540 && viewportWidth > screenWidth * 1.35) {
                var factor = Math.min(3, viewportWidth / screenWidth);
                document.documentElement.classList.add('invitation-wide-viewport');
                document.documentElement.style.setProperty('--invitation-viewport-factor', factor.toFixed(4));
                return;
            }

            document.documentElement.classList.remove('invitation-wide-viewport');
            document.documentElement.style.removeProperty('--invitation-viewport-factor');
        }

        normalizeMobileViewport();
        window.addEventListener('resize', normalizeMobileViewport);
        window.addEventListener('orientationchange', function () {
            window.setTimeout(normalizeMobileViewport, 300);
        });
    })();
</script>
