{{-- Music button, share button and scroll reveal, shared by every child ceremony page. --}}
@if($musicPath)
    <div class="ua-music"><audio data-audio loop preload="none" src="{{ Storage::url($musicPath) }}"></audio><button type="button" data-audio-toggle aria-label="Putar musik">Play</button><span data-audio-message></span></div>
@endif
<script>
    (() => {
        const audio = document.querySelector('[data-audio]');
        const toggle = document.querySelector('[data-audio-toggle]');
        toggle?.addEventListener('click', async () => {
            if (!audio.paused) return audio.pause();
            try { await audio.play(); } catch { document.querySelector('[data-audio-message]').textContent = 'Musik belum dapat diputar.'; }
        });
        document.querySelector('[data-ua-share]').addEventListener('click', async () => {
            const text = {{ Illuminate\Support\Js::from($shareText) }};
            if (navigator.share) { try { await navigator.share({ text }); } catch {} }
            else { window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank', 'noopener'); }
        });
        if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
                if (entry.isIntersecting) { entry.target.classList.add('is-revealed'); observer.unobserve(entry.target); }
            }), { threshold: .1 });
            document.querySelectorAll('[data-reveal]').forEach((section) => observer.observe(section));
        }
    })();
</script>
