<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Undangan Diarsipkan - {{ $invitation->groom_nickname }} & {{ $invitation->bride_nickname }}</title>
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
            padding: 34px 26px;
            text-align: center;
            width: 100%;
        }
        .label {
            color: #d5a75e;
            font-size: 11px;
            letter-spacing: .32em;
            margin: 0 0 18px;
            text-transform: uppercase;
        }
        h1 {
            font-family: Georgia, serif;
            font-size: 36px;
            font-weight: 400;
            line-height: 1.2;
            margin: 0;
        }
        .date {
            color: #d8c2a0;
            margin: 18px 0 0;
        }
        .note {
            color: #bba98f;
            font-size: 15px;
            line-height: 1.8;
            margin: 28px 0 0;
        }
        .watermark {
            color: #806f5c;
            font-size: 12px;
            margin: 34px 0 0;
        }
    </style>
</head>
<body>
    <main class="card">
        <p class="label">Undangan Diarsipkan</p>
        <h1>{{ $invitation->groom_nickname ?: 'Mempelai' }} &amp; {{ $invitation->bride_nickname ?: 'Pasangan' }}</h1>
        <p class="date">{{ $invitation->event_date?->translatedFormat('l, d F Y') }}</p>
        <p class="note">
            Undangan ini sudah melewati masa aktif dan media foto/musiknya telah dibersihkan untuk menghemat storage.
            Data acara dan transaksi penting tetap disimpan oleh sistem.
        </p>
        <p class="watermark">Dibuat gratis dengan aplikasi Undangan Pernikahan Bali</p>
    </main>
</body>
</html>
