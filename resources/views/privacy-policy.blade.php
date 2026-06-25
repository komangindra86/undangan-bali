<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - Undangan Pernikahan Bali</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100">
    <main class="mx-auto max-w-4xl px-6 py-12 md:py-16">
        <a href="{{ url('/') }}" class="text-sm text-amber-200 underline underline-offset-4">Kembali ke beranda</a>
        <p class="mt-8 text-xs uppercase tracking-[0.32em] text-amber-300">Privacy Policy</p>
        <h1 class="mt-4 font-serif text-4xl leading-tight md:text-5xl">Kebijakan Privasi Undangan Pernikahan Bali</h1>
        <p class="mt-4 text-stone-300">Terakhir diperbarui: 2 Juni 2026</p>

        <section class="mt-10 space-y-6 text-stone-200 leading-7">
            <p>
                Undangan Pernikahan Bali membantu pasangan membuat, menyimpan, dan membagikan undangan digital.
                Kebijakan ini menjelaskan data yang kami kumpulkan, cara penggunaannya, dan pilihan Anda saat memakai aplikasi mobile
                maupun halaman web undangan di <strong>platform.balisantih.com</strong>.
            </p>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Data Yang Kami Kumpulkan</h2>
                <ul class="mt-3 list-disc space-y-2 pl-6">
                    <li>Data akun: nama, email, password terenkripsi, dan token sesi.</li>
                    <li>Data undangan: nama mempelai, data keluarga, tanggal dan lokasi acara, alamat, link Google Maps, latitude, longitude, template, musik, dan status publish.</li>
                    <li>Media yang Anda unggah: foto mempelai, foto galeri, dan file musik pilihan sendiri.</li>
                    <li>Data Wedding Gift yang diatur pasangan: status aktif, nama penerima, catatan penerima, minimum gift, dan preferensi ucapan.</li>
                    <li>Data Wedding Gift dari tamu pada halaman web undangan: nama tamu, nomor HP opsional, nominal gift, biaya layanan, total bayar, ucapan opsional, order ID, status transaksi, dan respons pembayaran dari Midtrans.</li>
                    <li>Data pencairan: nama bank, nomor rekening, nama pemilik rekening, nominal pencairan, status, dan referensi transfer manual admin.</li>
                    <li>Data teknis: alamat IP, user agent, waktu akses, log server, dan jumlah view undangan publik.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Cara Kami Menggunakan Data</h2>
                <ul class="mt-3 list-disc space-y-2 pl-6">
                    <li>Membuat draft dan undangan publik yang dapat dibuka melalui link unik.</li>
                    <li>Menyimpan draft lokal di perangkat sebelum login, lalu menyinkronkannya ke backend setelah login atau register.</li>
                    <li>Menampilkan template, foto, musik, lokasi, tombol peta, tombol share, dan Wedding Gift pada halaman web undangan.</li>
                    <li>Memproses QRIS Wedding Gift melalui Midtrans dari halaman web undangan, bukan dari aplikasi mobile.</li>
                    <li>Memverifikasi status pembayaran hanya melalui webhook Midtrans atau pengecekan status backend.</li>
                    <li>Menyediakan dashboard transaksi dan proses pencairan manual untuk pasangan dan admin.</li>
                    <li>Menjaga keamanan, mencegah penyalahgunaan, memperbaiki bug, dan memenuhi kewajiban hukum.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Wedding Gift Dan Pembayaran</h2>
                <p class="mt-3">
                    Aplikasi mobile tidak menyediakan checkout, QRIS, pembelian template premium, atau pembelian fitur digital menggunakan Midtrans.
                    Mobile app hanya dipakai pasangan untuk mengaktifkan Wedding Gift, mengatur penerima, dan melihat dashboard transaksi.
                    Tamu melakukan pembayaran Wedding Gift melalui browser pada halaman web undangan publik.
                </p>
                <p class="mt-3">
                    Nominal gift, biaya layanan, dan total bayar ditampilkan transparan sebelum QRIS dibuat. Status <em>paid</em> tidak dipercaya dari callback frontend;
                    status hanya diperbarui dari webhook Midtrans yang tervalidasi atau pengecekan status dari backend.
                </p>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Pihak Ketiga</h2>
                <ul class="mt-3 list-disc space-y-2 pl-6">
                    <li>Midtrans untuk memproses QRIS Wedding Gift dan mengirim notifikasi status transaksi.</li>
                    <li>Google Maps atau link peta yang Anda masukkan untuk membantu tamu membuka lokasi acara.</li>
                    <li>WhatsApp atau fitur share perangkat saat Anda memilih membagikan link undangan.</li>
                    <li>Penyedia hosting/server untuk menjalankan backend, database, storage, dan log keamanan.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Izin Aplikasi Mobile</h2>
                <p class="mt-3">
                    Aplikasi dapat meminta akses foto atau dokumen agar Anda bisa memilih foto mempelai, foto galeri, atau file musik dari perangkat.
                    Aplikasi tidak meminta akses kamera, kontak, lokasi real-time, mikrofon, SMS, telepon, atau notifikasi push untuk MVP ini.
                </p>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Penyimpanan Dan Keamanan</h2>
                <p class="mt-3">
                    Password disimpan dalam bentuk hash. Koneksi production menggunakan HTTPS. Midtrans Server Key hanya disimpan di backend Laravel dan tidak disematkan ke aplikasi mobile.
                    Data draft lokal dapat tersimpan sementara di perangkat sampai Anda login, publish, atau menghapus draft.
                </p>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Hak Pengguna</h2>
                <p class="mt-3">
                    Anda dapat meminta akses, perbaikan, penghapusan data, atau penonaktifan undangan dengan menghubungi pengelola aplikasi.
                    Penghapusan data tertentu dapat dibatasi bila masih diperlukan untuk keamanan, audit transaksi, penyelesaian pencairan, atau kewajiban hukum.
                </p>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Kontak</h2>
                <p class="mt-3">
                    Untuk pertanyaan privasi, hubungi: <a class="text-amber-200 underline underline-offset-4" href="mailto:admin.balisantih@gmail.com">admin.balisantih@gmail.com</a>.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
