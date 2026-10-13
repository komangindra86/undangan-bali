<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Laporkan Undangan - {{ $invitation->display_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            align-items: center;
            background: #150f0b;
            color: #f7ead2;
            display: flex;
            font-family: Arial, sans-serif;
            justify-content: center;
            margin: 0;
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background: linear-gradient(145deg, #251913, #17100d);
            border: 1px solid #9d7444;
            border-radius: 28px;
            box-shadow: 0 24px 80px #0008;
            max-width: 520px;
            padding: 30px 24px;
            width: 100%;
        }
        .label {
            color: #d5a75e;
            font-size: 11px;
            letter-spacing: .32em;
            margin: 0 0 14px;
            text-transform: uppercase;
        }
        h1 {
            font-family: Georgia, serif;
            font-size: 28px;
            font-weight: 400;
            line-height: 1.25;
            margin: 0;
        }
        .intro, .done {
            color: #bba98f;
            font-size: 15px;
            line-height: 1.7;
            margin: 14px 0 0;
        }
        fieldset { border: 0; margin: 22px 0 0; padding: 0; }
        legend, label.field {
            color: #d8c2a0;
            display: block;
            font-size: 14px;
            margin: 0 0 8px;
            padding: 0;
        }
        .choice {
            align-items: center;
            border: 1px solid #4a382a;
            border-radius: 14px;
            cursor: pointer;
            display: flex;
            font-size: 15px;
            gap: 10px;
            margin: 0 0 8px;
            min-height: 48px;
            padding: 10px 14px;
        }
        .choice input { accent-color: #d5a75e; height: 18px; width: 18px; }
        textarea, input[type="text"] {
            background: #120c09;
            border: 1px solid #4a382a;
            border-radius: 14px;
            color: #f7ead2;
            font: inherit;
            font-size: 16px;
            padding: 12px 14px;
            width: 100%;
        }
        textarea { min-height: 110px; resize: vertical; }
        .group { margin: 18px 0 0; }
        .hint { color: #806f5c; font-size: 12px; line-height: 1.6; margin: 6px 0 0; }
        .error {
            background: #3b1512;
            border: 1px solid #8f3a32;
            border-radius: 14px;
            color: #f6cfc9;
            font-size: 14px;
            line-height: 1.6;
            margin: 18px 0 0;
            padding: 12px 14px;
        }
        button {
            background: #d5a75e;
            border: 0;
            border-radius: 999px;
            color: #1b120c;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            margin: 22px 0 0;
            min-height: 48px;
            padding: 12px 22px;
            width: 100%;
        }
        .back { color: #d5a75e; display: inline-block; font-size: 14px; margin: 20px 0 0; }
        .trap { height: 0; left: -9999px; overflow: hidden; position: absolute; width: 0; }
    </style>
</head>
<body>
    <main class="card">
        <p class="label">Laporkan Undangan</p>
        <h1>{{ $invitation->display_name }}</h1>

        @if (session('reported'))
            <p class="done">
                Terima kasih, laporan Anda sudah kami terima dan akan ditinjau admin.
                Bila undangan ini terbukti palsu, link dan halaman hadiahnya akan dinonaktifkan.
            </p>
        @else
            <p class="intro">
                Gunakan formulir ini bila undangan ini palsu, memakai nama atau foto Anda tanpa izin, atau dipakai untuk menipu.
                Laporan ditinjau oleh admin Undangan Bali Santih.
            </p>

            @if ($errors->any())
                <p class="error">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('invitations.report.store', $invitation->slug) }}">
                @csrf
                <fieldset>
                    <legend>Alasan laporan</legend>
                    @foreach ($reasons as $value => $text)
                        <label class="choice">
                            <input type="radio" name="reason" value="{{ $value }}" required @checked(old('reason', 'fake') === $value)>
                            <span>{{ $text }}</span>
                        </label>
                    @endforeach
                </fieldset>

                <div class="group">
                    <label class="field" for="note">Penjelasan singkat</label>
                    <textarea id="note" name="note" required minlength="10" maxlength="500" placeholder="Contoh: acara ini tidak ada, nama keluarga kami dipakai tanpa izin.">{{ old('note') }}</textarea>
                </div>

                <div class="group">
                    <label class="field" for="reporter_contact">Nomor WhatsApp atau email (opsional)</label>
                    <input type="text" id="reporter_contact" name="reporter_contact" maxlength="80" value="{{ old('reporter_contact') }}" autocomplete="off">
                    <p class="hint">Hanya dipakai admin bila perlu menanyakan laporan ini. Tidak ditampilkan ke pemilik undangan.</p>
                </div>

                <div class="trap" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <button type="submit">Kirim laporan</button>
            </form>
        @endif

        <a class="back" href="{{ route('invitations.public', $invitation->slug) }}">Kembali ke undangan</a>
    </main>
</body>
</html>
