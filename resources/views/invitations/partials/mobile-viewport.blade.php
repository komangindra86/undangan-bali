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

    html.invitation-wide-viewport .page {
        max-width: none !important;
        width: 100vw !important;
    }
</style>
<script>
    (function () {
        function normalizeMobileViewport() {
            var screenWidth = Math.min(window.screen.width || 0, window.screen.height || 0);
            var viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;

            if (screenWidth >= 320 && screenWidth <= 540 && viewportWidth > screenWidth * 1.35) {
                var meta = document.querySelector('meta[name="viewport"]');

                if (meta && meta.getAttribute('content').indexOf('width=' + screenWidth) === -1) {
                    meta.setAttribute('content', 'width=' + screenWidth + ', initial-scale=1, viewport-fit=cover');
                }

                document.documentElement.classList.add('invitation-wide-viewport');
                return;
            }

            document.documentElement.classList.remove('invitation-wide-viewport');
        }

        normalizeMobileViewport();
        window.addEventListener('resize', normalizeMobileViewport);
        window.addEventListener('orientationchange', function () {
            window.setTimeout(normalizeMobileViewport, 300);
        });
    })();
</script>
