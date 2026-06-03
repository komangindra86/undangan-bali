<style>
    html,
    body {
        min-width: 0;
        overflow-x: hidden;
    }

    @supports (min-height: 100dvh) {
        .hero,
        .cover {
            min-height: 100dvh;
        }
    }

    @media (hover: none) and (pointer: coarse) {
        .page {
            box-shadow: none !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            max-width: none !important;
            width: 100vw !important;
        }

        .banner,
        .preview-banner {
            width: 100vw;
        }
    }
</style>
