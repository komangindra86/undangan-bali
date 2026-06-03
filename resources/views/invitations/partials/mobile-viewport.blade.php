<style>
    html,
    body {
        min-width: 0;
        overflow-x: hidden;
    }

    @media (hover: none) and (pointer: coarse) {
        .page {
            margin-left: auto !important;
            margin-right: auto !important;
            max-width: 360px !important;
            width: min(360px, calc(100vw - 28px)) !important;
        }

        .hero,
        .cover {
            min-height: auto !important;
        }

        .banner,
        .preview-banner {
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%);
            width: min(360px, calc(100vw - 28px));
        }
    }
</style>
