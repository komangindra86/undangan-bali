@php
    $openingTheme = $openingTheme ?? 'Pawiwahan Bali';
    $openingImage = $openingImage ?? Storage::url('templates/bali-preview/hero-couple.jpg');
    $openingAccent = $openingAccent ?? '#d4ad61';
    $openingText = $openingText ?? '#fff8e8';
    $openingShade = $openingShade ?? 'rgba(17, 11, 8, .68)';
    $openingFont = $openingFont ?? 'Georgia, serif';
    $openingHasMusic = $openingHasMusic ?? false;
    $openingGuestInput = request()->query('to', 'Bapak/Ibu/Saudara/i');
    $openingGuest = is_scalar($openingGuestInput)
        ? trim(strip_tags((string) $openingGuestInput))
        : 'Bapak/Ibu/Saudara/i';
    $openingGuest = $openingGuest !== '' ? Illuminate\Support\Str::limit($openingGuest, 80) : 'Bapak/Ibu/Saudara/i';
    $openingCouple = trim(($invitation->groom_nickname ?: 'Mempelai').' & '.($invitation->bride_nickname ?: 'Pasangan'));
@endphp

<style>
    html.invitation-pending,
    html.invitation-pending body { height: 100%; overflow: hidden !important; overscroll-behavior: none; }
    .invitation-opening {
        align-items: center;
        color: var(--opening-text);
        display: flex;
        inset: 0;
        justify-content: center;
        min-height: 100dvh;
        overflow: hidden;
        position: fixed;
        text-align: center;
        transition: opacity .65s ease, visibility .65s ease;
        width: 100%;
        z-index: 2147483000;
    }
    .invitation-opening.is-opening { opacity: 0; pointer-events: none; visibility: hidden; }
    .invitation-opening__photo,
    .invitation-opening__shade { height: 100%; inset: 0; position: absolute; width: 100%; }
    .invitation-opening__photo { object-fit: cover; transform: scale(1.025); transition: transform 1.1s ease; }
    .invitation-opening.is-opening .invitation-opening__photo { transform: scale(1.09); }
    .invitation-opening__shade {
        background:
            linear-gradient(180deg, rgba(0, 0, 0, .2), transparent 32%),
            linear-gradient(0deg, rgba(0, 0, 0, .62), transparent 54%),
            var(--opening-shade);
    }
    .invitation-opening__content {
        align-items: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 100dvh;
        padding: max(42px, env(safe-area-inset-top)) 24px max(36px, env(safe-area-inset-bottom));
        position: relative;
        width: min(100%, 680px);
        z-index: 1;
    }
    .invitation-opening__eyebrow { color: var(--opening-accent); font-size: 11px; font-weight: 700; letter-spacing: .42em; margin: 0; text-transform: uppercase; }
    .invitation-opening__center { margin-block: auto; padding-block: 34px; }
    .invitation-opening__label { font-size: 13px; letter-spacing: .2em; margin: 0 0 18px; text-transform: uppercase; }
    .invitation-opening__names { color: var(--opening-text); font-family: var(--opening-font); font-size: clamp(44px, 12vw, 82px); font-weight: 500; line-height: .98; margin: 0; text-wrap: balance; }
    .invitation-opening__date { color: var(--opening-accent); font-size: 12px; letter-spacing: .28em; margin: 24px 0 0; text-transform: uppercase; }
    .invitation-opening__guest-label { font-size: 12px; margin: 0 0 7px; opacity: .82; }
    .invitation-opening__guest { font-family: var(--opening-font); font-size: 21px; margin: 0 0 20px; }
    .invitation-opening__button {
        align-items: center;
        background: var(--opening-accent);
        border: 1px solid rgba(255, 255, 255, .42);
        border-radius: 999px;
        box-shadow: 0 14px 34px rgba(0, 0, 0, .28);
        color: #21170f;
        cursor: pointer;
        display: inline-flex;
        font: 700 14px/1 Arial, sans-serif;
        gap: 10px;
        justify-content: center;
        min-height: 50px;
        padding: 0 24px;
        transition: transform .2s ease, filter .2s ease;
    }
    .invitation-opening__button:hover { filter: brightness(1.06); transform: translateY(-2px); }
    .invitation-opening__button:focus-visible { outline: 3px solid var(--opening-text); outline-offset: 4px; }
    .invitation-opening__button svg { height: 18px; width: 18px; }
    .invitation-opening__hint { font-size: 11px; margin: 13px 0 0; opacity: .72; }
    @media (max-height: 620px) {
        .invitation-opening__content { padding-bottom: 22px; padding-top: 24px; }
        .invitation-opening__center { padding-block: 20px; }
        .invitation-opening__names { font-size: clamp(38px, 10vw, 60px); }
    }
    @media (prefers-reduced-motion: reduce) {
        .invitation-opening,
        .invitation-opening__photo,
        .invitation-opening__button { transition: none; }
    }
</style>

<script>document.documentElement.classList.add('invitation-pending');</script>
<section
    class="invitation-opening"
    data-invitation-opening
    role="dialog"
    aria-modal="true"
    aria-labelledby="invitation-opening-title"
    style="--opening-accent: {{ $openingAccent }}; --opening-text: {{ $openingText }}; --opening-shade: {{ $openingShade }}; --opening-font: {{ $openingFont }};"
>
    <img class="invitation-opening__photo" src="{{ $openingImage }}" alt="Foto pembuka {{ $openingCouple }}">
    <div class="invitation-opening__shade" aria-hidden="true"></div>
    <div class="invitation-opening__content">
        <p class="invitation-opening__eyebrow">{{ $openingTheme }}</p>
        <div class="invitation-opening__center">
            <p class="invitation-opening__label">The Wedding of</p>
            <h1 class="invitation-opening__names" id="invitation-opening-title">{{ $openingCouple }}</h1>
            <p class="invitation-opening__date">{{ $invitation->event_date?->translatedFormat('d F Y') }}</p>
        </div>
        <div>
            <p class="invitation-opening__guest-label">Kepada Yth. Bapak/Ibu/Saudara/i</p>
            <p class="invitation-opening__guest">{{ $openingGuest }}</p>
            <button class="invitation-opening__button" type="button" data-open-invitation>
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7.5 12 13l8-5.5M5.5 19h13A1.5 1.5 0 0 0 20 17.5v-11A1.5 1.5 0 0 0 18.5 5h-13A1.5 1.5 0 0 0 4 6.5v11A1.5 1.5 0 0 0 5.5 19Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Buka Undangan</span>
            </button>
            <p class="invitation-opening__hint">{{ $openingHasMusic ? 'Buka undangan dan mulai musik' : 'Ketuk untuk melihat undangan' }}</p>
        </div>
    </div>
</section>

<script>
    (() => {
        const opening = document.querySelector('[data-invitation-opening]');
        const button = opening?.querySelector('[data-open-invitation]');
        if (!opening || !button) return;

        let pageContent = [];
        const refreshPageContent = () => {
            pageContent = Array.from(document.body.children).filter((node) => node !== opening && node.tagName !== 'SCRIPT' && node.tagName !== 'STYLE');
            pageContent.forEach((node) => node.setAttribute('inert', ''));
        };
        refreshPageContent();

        const syncAudioButton = (audio) => {
            document.querySelectorAll('[data-audio-toggle]').forEach((toggle) => {
                toggle.textContent = audio.paused ? 'Play' : 'Pause';
                toggle.setAttribute('aria-label', audio.paused ? 'Putar musik' : 'Jeda musik');
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            refreshPageContent();
            const audio = document.querySelector('[data-audio]');
            if (audio) {
                audio.addEventListener('play', () => syncAudioButton(audio));
                audio.addEventListener('pause', () => syncAudioButton(audio));
                syncAudioButton(audio);
            }
            button.focus({ preventScroll: true });
        });

        button.addEventListener('click', () => {
            refreshPageContent();
            const audio = document.querySelector('[data-audio]');
            if (audio) {
                const playback = audio.play();
                if (playback?.catch) playback.catch(() => syncAudioButton(audio));
            }

            opening.classList.add('is-opening');
            document.documentElement.classList.remove('invitation-pending');
            pageContent.forEach((node) => node.removeAttribute('inert'));
            window.setTimeout(() => opening.remove(), 700);
        }, { once: true });
    })();
</script>
