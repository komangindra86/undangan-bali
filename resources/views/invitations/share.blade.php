<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Bagikan Undangan - {{ $invitation->display_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            background: #150f0b;
            color: #f7ead2;
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            padding: 24px 16px 48px;
        }
        .card {
            background: linear-gradient(145deg, #251913, #17100d);
            border: 1px solid #9d7444;
            border-radius: 28px;
            box-shadow: 0 24px 80px #0008;
            margin: 0 auto;
            max-width: 560px;
            padding: 28px 22px;
        }
        .label {
            color: #d5a75e;
            font-size: 11px;
            letter-spacing: .32em;
            margin: 0 0 12px;
            text-transform: uppercase;
        }
        h1 {
            font-family: Georgia, serif;
            font-size: 28px;
            font-weight: 400;
            line-height: 1.25;
            margin: 0;
        }
        h2 {
            color: #e9d3ab;
            font-family: Georgia, serif;
            font-size: 19px;
            font-weight: 400;
            margin: 0;
        }
        p { color: #bba98f; font-size: 15px; line-height: 1.7; margin: 12px 0 0; }
        section { border-top: 1px solid #3a2b20; margin: 26px 0 0; padding: 24px 0 0; }
        label { color: #d8c2a0; display: block; font-size: 14px; margin: 16px 0 8px; }
        input[type="text"], textarea {
            background: #120c09;
            border: 1px solid #4a382a;
            border-radius: 14px;
            color: #f7ead2;
            font: inherit;
            font-size: 16px;
            padding: 12px 14px;
            width: 100%;
        }
        textarea { min-height: 130px; resize: vertical; }
        .link {
            background: #120c09;
            border: 1px dashed #5d4733;
            border-radius: 14px;
            color: #e9d3ab;
            font-size: 13px;
            line-height: 1.6;
            margin: 14px 0 0;
            overflow-wrap: anywhere;
            padding: 12px 14px;
        }
        .actions { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; margin: 14px 0 0; }
        button, .button {
            align-items: center;
            background: #d5a75e;
            border: 0;
            border-radius: 999px;
            color: #1b120c;
            cursor: pointer;
            display: flex;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            justify-content: center;
            min-height: 46px;
            padding: 10px 16px;
            text-align: center;
            text-decoration: none;
        }
        .ghost { background: transparent; border: 1px solid #9d7444; color: #e9d3ab; }
        .wide { margin: 14px 0 0; width: 100%; }
        .guest {
            align-items: center;
            border: 1px solid #3a2b20;
            border-radius: 16px;
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr auto auto;
            margin: 10px 0 0;
            padding: 10px 12px;
        }
        .guest span { font-size: 15px; overflow-wrap: anywhere; }
        .guest button, .guest .button { font-size: 12px; min-height: 40px; padding: 8px 14px; }
        .hint { color: #806f5c; font-size: 12px; line-height: 1.6; margin: 8px 0 0; }
        .toast { color: #b8d6a4; font-size: 13px; margin: 10px 0 0; min-height: 18px; }
        .back { color: #d5a75e; display: inline-block; font-size: 14px; margin: 26px 0 0; }
        @media (max-width: 380px) {
            .actions { grid-template-columns: 1fr; }
            .guest { grid-template-columns: 1fr 1fr; }
            .guest span { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>
    <main class="card" data-url="{{ $invitation->public_url }}" data-occasion="{{ $invitation->occasion_phrase }}">
        <p class="label">Bagikan Undangan</p>
        <h1>{{ $invitation->display_name }}</h1>
        <p>Tulis nama tamu, lalu kirim link-nya. Nama itu akan tampil sebagai sapaan di sampul undangan.</p>

        <section>
            <h2>Satu tamu</h2>
            <label for="guest">Nama tamu</label>
            <input type="text" id="guest" maxlength="80" placeholder="Contoh: Bapak Wayan Sudarta" autocomplete="off">
            <p class="link" id="link"></p>
            <div class="actions">
                <button type="button" id="copy" class="ghost">Salin link</button>
                <a id="whatsapp" class="button" target="_blank" rel="noopener">Kirim via WhatsApp</a>
            </div>
            <p class="toast" id="toast" role="status"></p>
        </section>

        <section>
            <h2>Banyak tamu sekaligus</h2>
            <label for="guests">Daftar nama, satu nama per baris</label>
            <textarea id="guests" placeholder="Bapak Wayan Sudarta&#10;Ibu Made Rini&#10;Keluarga Bapak Ketut Darma"></textarea>
            <button type="button" id="build" class="ghost wide">Buat link untuk semua nama</button>
            <p class="hint">Daftar nama hanya diproses di ponsel ini dan tidak dikirim ke server.</p>
            <div id="list"></div>
        </section>

        <a class="back" href="{{ $invitation->public_url }}">Lihat undangan</a>
    </main>

    <script>
        (() => {
            const root = document.querySelector('main');
            const baseUrl = root.dataset.url;
            const occasion = root.dataset.occasion;
            const guest = document.getElementById('guest');
            const link = document.getElementById('link');
            const whatsapp = document.getElementById('whatsapp');
            const toast = document.getElementById('toast');
            const list = document.getElementById('list');

            const clean = (name) => name.replace(/[<>]/g, '').trim().replace(/\s+/g, ' ').slice(0, 80);
            const urlFor = (name) => (name ? `${baseUrl}?to=${encodeURIComponent(name)}` : baseUrl);
            const textFor = (name) => `Kepada Yth. ${name || 'Bapak/Ibu/Saudara/i'}, kami mengundang untuk hadir di ${occasion}. Buka undangan: ${urlFor(name)}`;
            const whatsappFor = (name) => `https://wa.me/?text=${encodeURIComponent(textFor(name))}`;

            async function copy(text, message) {
                try {
                    await navigator.clipboard.writeText(text);
                } catch {
                    const field = document.createElement('textarea');
                    field.value = text;
                    document.body.appendChild(field);
                    field.select();
                    document.execCommand('copy');
                    field.remove();
                }
                toast.textContent = message;
            }

            function refresh() {
                const name = clean(guest.value);
                link.textContent = urlFor(name);
                whatsapp.href = whatsappFor(name);
                toast.textContent = '';
            }

            guest.addEventListener('input', refresh);
            document.getElementById('copy').addEventListener('click', () => {
                const name = clean(guest.value);
                copy(urlFor(name), name ? `Link untuk ${name} tersalin.` : 'Link undangan umum tersalin.');
            });

            document.getElementById('build').addEventListener('click', () => {
                const names = [...new Set(document.getElementById('guests').value.split('\n').map(clean).filter(Boolean))].slice(0, 300);
                list.replaceChildren(...names.map((name) => {
                    const row = document.createElement('div');
                    row.className = 'guest';
                    const label = document.createElement('span');
                    label.textContent = name;
                    const copyButton = document.createElement('button');
                    copyButton.type = 'button';
                    copyButton.className = 'ghost';
                    copyButton.textContent = 'Salin';
                    copyButton.addEventListener('click', () => {
                        copy(urlFor(name), '');
                        copyButton.textContent = 'Tersalin';
                    });
                    const send = document.createElement('a');
                    send.className = 'button';
                    send.target = '_blank';
                    send.rel = 'noopener';
                    send.href = whatsappFor(name);
                    send.textContent = 'WhatsApp';
                    send.addEventListener('click', () => { send.textContent = 'Terkirim'; send.classList.add('ghost'); });
                    row.append(label, copyButton, send);
                    return row;
                }));
            });

            refresh();
        })();
    </script>
</body>
</html>
