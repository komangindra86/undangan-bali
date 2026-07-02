<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Undangan {{ $invitation->groom_nickname }} & {{ $invitation->bride_nickname }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #19140f; }
        .pattern {
            background-color: #231b13;
            background-image: radial-gradient(#b7924b22 1px, transparent 1px);
            background-size: 18px 18px;
        }
    </style>
</head>
<body class="pattern text-stone-100">
    @php
        $musicPath = $invitation->music_type === 'default' && $invitation->music
            ? $invitation->music->file_path
            : $invitation->music_file;
        $shareText = 'Kepada Yth. Bapak/Ibu/Saudara/i, kami mengundang untuk hadir di acara pernikahan kami. Buka undangan: '.url()->current();
    @endphp
    <main class="mx-auto max-w-xl min-h-screen bg-stone-900/85 shadow-2xl">
        <section class="min-h-screen flex flex-col justify-center text-center px-7 py-16 border-b border-amber-700/30">
            <p class="uppercase text-xs tracking-[0.4em] text-amber-300 mb-10">The Wedding Of</p>
            <h1 class="font-serif text-5xl text-amber-100">{{ $invitation->groom_nickname }}</h1>
            <p class="font-serif text-3xl my-4 text-amber-400">&</p>
            <h1 class="font-serif text-5xl text-amber-100">{{ $invitation->bride_nickname }}</h1>
            <p class="mt-10 tracking-widest text-stone-300">
                {{ $invitation->event_date->translatedFormat('d F Y') }}
            </p>
            <div class="mx-auto mt-10 w-16 h-px bg-amber-500"></div>
        </section>

        @if ($invitation->opening_quote)
            <section class="px-8 py-14 text-center border-b border-amber-700/30">
                <p class="text-stone-300 italic leading-relaxed">"{{ $invitation->opening_quote }}"</p>
            </section>
        @endif

        <section class="px-8 py-14 text-center border-b border-amber-700/30">
            <p class="text-amber-300 uppercase tracking-widest text-xs mb-8">Mempelai</p>
            <div class="space-y-10">
                <article>
                    @if ($invitation->groom_photo)
                        <img src="{{ Storage::url($invitation->groom_photo) }}" alt="{{ $invitation->groom_full_name }}" class="mx-auto w-32 h-32 rounded-2xl object-cover border-2 border-amber-500 mb-5">
                    @endif
                    <h2 class="font-serif text-3xl text-amber-100">{{ $invitation->groom_full_name }}</h2>
                    @if ($invitation->groom_father_name || $invitation->groom_mother_name)
                        <p class="text-sm text-stone-300 mt-3">Putra dari<br>{{ $invitation->groom_father_name }} & {{ $invitation->groom_mother_name }}</p>
                    @endif
                </article>
                <article>
                    @if ($invitation->bride_photo)
                        <img src="{{ Storage::url($invitation->bride_photo) }}" alt="{{ $invitation->bride_full_name }}" class="mx-auto w-32 h-32 rounded-2xl object-cover border-2 border-amber-500 mb-5">
                    @endif
                    <h2 class="font-serif text-3xl text-amber-100">{{ $invitation->bride_full_name }}</h2>
                    @if ($invitation->bride_father_name || $invitation->bride_mother_name)
                        <p class="text-sm text-stone-300 mt-3">Putri dari<br>{{ $invitation->bride_father_name }} & {{ $invitation->bride_mother_name }}</p>
                    @endif
                </article>
            </div>
        </section>

        <section class="px-8 py-14 text-center border-b border-amber-700/30">
            <p class="text-amber-300 uppercase tracking-widest text-xs mb-8">{{ $invitation->event_type }}</p>
            <div class="rounded-2xl border border-amber-700/40 bg-stone-950/30 px-6 py-8">
                <p class="font-serif text-2xl text-amber-100">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
                <p class="mt-4 text-stone-300">{{ substr($invitation->start_time, 0, 5) }}@if($invitation->end_time) - {{ substr($invitation->end_time, 0, 5) }}@endif WITA</p>
                <h3 class="mt-7 font-semibold text-lg">{{ $invitation->venue_name }}</h3>
                <p class="mt-2 text-sm text-stone-300 leading-relaxed">{{ $invitation->venue_address }}</p>
                @if ($invitation->google_maps_url)
                    <a class="inline-flex mt-7 rounded-full bg-amber-600 hover:bg-amber-500 transition px-6 py-3 font-medium" href="{{ $invitation->google_maps_url }}" target="_blank" rel="noopener">
                        Buka Google Maps
                    </a>
                @endif
            </div>
        </section>

        <section class="px-8 py-14 text-center">
            <p class="font-serif text-2xl text-amber-100">Terima Kasih</p>
            <p class="text-sm text-stone-300 mt-4">Merupakan kehormatan bagi kami apabila Anda berkenan hadir.</p>
            <button id="share" class="mt-9 rounded-full border border-amber-500 text-amber-200 px-7 py-3">Bagikan Undangan</button>
            <p class="mt-16 text-xs text-stone-500">@include('invitations.partials.app-credit')</p>
        </section>
    </main>

    @if ($musicPath)
        <audio controls autoplay loop class="fixed bottom-4 right-4 w-52 opacity-80">
            <source src="{{ Storage::url($musicPath) }}">
        </audio>
    @endif

    <script>
        document.getElementById('share').addEventListener('click', async function () {
            const text = @json($shareText);
            if (navigator.share) {
                await navigator.share({ text: text, url: window.location.href });
                return;
            }
            await navigator.clipboard.writeText(text);
            this.textContent = 'Link tersalin';
        });
    </script>
</body>
</html>
