{{-- Photo viewer, music button and the script every premium design shares. --}}
<div class="pm-lightbox" data-lightbox hidden>
    <img alt="">
    <button type="button" data-lightbox-close aria-label="Tutup">&times;</button>
    <button type="button" data-lightbox-prev aria-label="Foto sebelumnya">&lsaquo;</button>
    <button type="button" data-lightbox-next aria-label="Foto berikutnya">&rsaquo;</button>
    <p data-lightbox-count></p>
</div>

@if ($musicPath && ! $isPreview)
    <div class="pm-player">
        <button type="button" data-audio-toggle aria-label="Putar musik">Musik</button>
        <audio data-audio loop preload="none" src="{{ $mediaUrl($musicPath) }}"></audio>
    </div>
@endif

<script>
    (() => {
        const reveal = document.querySelectorAll('[data-reveal]');
        if (!('IntersectionObserver' in window) || matchMedia('(prefers-reduced-motion: reduce)').matches) {
            reveal.forEach((item) => item.classList.add('is-revealed'));
        } else {
            const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
                if (entry.isIntersecting) { entry.target.classList.add('is-revealed'); observer.unobserve(entry.target); }
            }), { threshold: .1 });
            reveal.forEach((item) => observer.observe(item));
        }

        const countdown = document.querySelector('[data-countdown]');
        if (countdown) {
            const target = new Date(countdown.dataset.countdown).getTime();
            const field = (name) => countdown.querySelector(`[data-${name}]`);
            const tick = () => {
                const left = Math.max(0, target - Date.now());
                field('days').textContent = Math.floor(left / 86400000);
                field('hours').textContent = String(Math.floor(left / 3600000) % 24).padStart(2, '0');
                field('minutes').textContent = String(Math.floor(left / 60000) % 60).padStart(2, '0');
                field('seconds').textContent = String(Math.floor(left / 1000) % 60).padStart(2, '0');
                // Restart the little drop on the seconds each time they change.
                field('seconds').classList.remove('is-tick');
                void field('seconds').offsetWidth;
                field('seconds').classList.add('is-tick');
            };
            tick();
            setInterval(tick, 1000);
        }

        const audio = document.querySelector('[data-audio]');
        const toggle = document.querySelector('[data-audio-toggle]');
        toggle?.addEventListener('click', async () => {
            if (!audio.paused) { audio.pause(); toggle.textContent = 'Musik'; toggle.classList.remove('is-playing'); return; }
            try { await audio.play(); toggle.textContent = 'Jeda'; toggle.classList.add('is-playing'); } catch { toggle.textContent = 'Musik'; }
        });

        document.querySelector('[data-share]')?.addEventListener('click', async (event) => {
            const text = {{ Illuminate\Support\Js::from($shareText) }};
            if (navigator.share) { try { await navigator.share({ text }); } catch {} return; }
            await navigator.clipboard.writeText(text);
            event.currentTarget.textContent = 'Link tersalin';
        });

        // [data-gallery] photos open in the viewer; [data-gallery-ref] is a repeated copy pointing at one of them.
        const lightbox = document.querySelector('[data-lightbox]');
        const photos = [...document.querySelectorAll('[data-gallery]')];
        let current = 0;
        const show = (index) => {
            current = (index + photos.length) % photos.length;
            lightbox.querySelector('img').src = photos[current].src;
            lightbox.querySelector('[data-lightbox-count]').textContent = `${current + 1} / ${photos.length}`;
            lightbox.hidden = false;
        };
        photos.forEach((photo, index) => photo.addEventListener('click', () => show(index)));
        document.querySelectorAll('[data-gallery-ref]').forEach((copy) => copy.addEventListener('click', () => show(Number(copy.dataset.galleryRef))));
        lightbox.querySelector('[data-lightbox-prev]').addEventListener('click', (event) => { event.stopPropagation(); show(current - 1); });
        lightbox.querySelector('[data-lightbox-next]').addEventListener('click', (event) => { event.stopPropagation(); show(current + 1); });
        lightbox.addEventListener('click', () => { lightbox.hidden = true; });
        document.addEventListener('keydown', (event) => {
            if (lightbox.hidden) return;
            if (event.key === 'Escape') lightbox.hidden = true;
            if (event.key === 'ArrowLeft') show(current - 1);
            if (event.key === 'ArrowRight') show(current + 1);
        });
    })();
</script>
