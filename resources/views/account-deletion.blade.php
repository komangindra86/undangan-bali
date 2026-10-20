<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hapus Akun - Undangan Bali Santih</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #150f0b; color: #f7ead2; font-family: Arial, sans-serif; margin: 0; min-height: 100vh; padding: 24px 16px 48px; }
        .card { background: linear-gradient(145deg, #251913, #17100d); border: 1px solid #9d7444; border-radius: 28px; box-shadow: 0 24px 80px #0008; margin: 0 auto; max-width: 600px; padding: 28px 22px; }
        .label { color: #d5a75e; font-size: 11px; letter-spacing: .32em; margin: 0 0 12px; text-transform: uppercase; }
        h1 { font-family: Georgia, serif; font-size: 30px; font-weight: 400; line-height: 1.25; margin: 0; }
        h2 { color: #e9d3ab; font-family: Georgia, serif; font-size: 20px; font-weight: 400; margin: 28px 0 0; }
        p, li { color: #bba98f; font-size: 15px; line-height: 1.7; }
        ol, ul { margin: 10px 0 0; padding-left: 20px; }
        label { color: #d8c2a0; display: block; font-size: 14px; margin: 16px 0 8px; }
        input[type="email"], textarea { background: #120c09; border: 1px solid #4a382a; border-radius: 14px; color: #f7ead2; font: inherit; font-size: 16px; padding: 12px 14px; width: 100%; }
        textarea { min-height: 96px; resize: vertical; }
        button { background: #d5a75e; border: 0; border-radius: 999px; color: #1b120c; cursor: pointer; font: inherit; font-weight: 700; margin: 20px 0 0; min-height: 48px; padding: 12px 22px; width: 100%; }
        .notice { background: #120c09; border: 1px dashed #5d4733; border-radius: 16px; margin: 18px 0 0; padding: 14px 16px; }
        .error { background: #3b1512; border: 1px solid #8f3a32; border-radius: 14px; color: #f6cfc9; margin: 16px 0 0; padding: 12px 14px; }
        .trap { height: 0; left: -9999px; overflow: hidden; position: absolute; width: 0; }
        a { color: #d5a75e; }
    </style>
</head>
<body>
    <main class="card">
        <p class="label">Undangan Bali Santih</p>
        <h1>Hapus akun</h1>

        <h2>Dari aplikasi</h2>
        <ol>
            <li>Buka aplikasi Undangan Bali Santih dan masuk dengan akun Google Anda.</li>
            <li>Buka tab <strong>Profil</strong>, lalu pilih <strong>Hapus Akun</strong>.</li>
            <li>Baca ringkasan data yang akan dihapus, ketik <strong>HAPUS</strong>, lalu konfirmasi.</li>
        </ol>

        <h2>Yang dihapus dan yang disimpan</h2>
        <ul>
            <li>Dihapus: nama dan email akun, undangan beserta foto dan musiknya, Moment, komentar, reaksi, rekening pencairan tersimpan, dan perangkat yang terhubung.</li>
            <li>Disimpan untuk pembukuan: catatan tanda kasih dari tamu dan riwayat pencairan, tanpa nama atau email akun Anda.</li>
            <li>Saldo tanda kasih yang belum dicairkan hangus saat akun dihapus. Cairkan dulu bila ingin menerimanya.</li>
        </ul>

        <h2>Tidak bisa membuka aplikasi?</h2>
        @if (session('requested'))
            <p class="notice">Permintaan Anda sudah kami terima. Admin akan memeriksa dan menghapus akun dengan email tersebut, lalu mengabari Anda bila perlu.</p>
        @else
            <p>Kirim permintaan di bawah. Admin akan memeriksa dan menghapus akun dengan email yang Anda tulis.</p>
            @if ($errors->any())
                <p class="error">{{ $errors->first() }}</p>
            @endif
            <form method="POST" action="{{ route('account-deletion.store') }}">
                @csrf
                <label for="email">Email akun Google yang dipakai di aplikasi</label>
                <input type="email" id="email" name="email" required maxlength="255" value="{{ old('email') }}" autocomplete="email">
                <label for="note">Catatan (opsional)</label>
                <textarea id="note" name="note" maxlength="500">{{ old('note') }}</textarea>
                <div class="trap" aria-hidden="true"><label for="website">Website</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
                <button type="submit">Kirim permintaan hapus akun</button>
            </form>
        @endif

        <p style="margin-top: 24px"><a href="{{ route('privacy-policy') }}">Kebijakan Privasi</a></p>
    </main>
</body>
</html>
