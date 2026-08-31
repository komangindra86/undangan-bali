<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kebijakan Privasi - Undangan Bali Santih</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100">
    <main class="mx-auto max-w-4xl px-6 py-12 md:py-16">
        <a href="{{ url('/') }}" class="text-sm text-amber-200 underline underline-offset-4">Kembali ke beranda</a>
        <p class="mt-8 text-xs uppercase tracking-[0.32em] text-amber-300">Privacy Policy</p>
        <h1 class="mt-4 font-serif text-4xl leading-tight md:text-5xl">Kebijakan Privasi Undangan Bali Santih</h1>
        <p class="mt-4 text-stone-300">Terakhir diperbarui: 31 Agustus 2026</p>

        <section class="mt-10 space-y-6 text-stone-200 leading-7">
            <p>
                Undangan Bali Santih membantu pengguna membuat, menyimpan, dan membagikan undangan digital untuk berbagai momen penting.
                Kebijakan ini menjelaskan data yang kami kumpulkan, cara penggunaannya, dan pilihan Anda saat memakai aplikasi mobile
                maupun halaman web undangan di <strong>undangan.balisantih.com</strong>.
            </p>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Data Yang Kami Kumpulkan</h2>
                <ul class="mt-3 list-disc space-y-2 pl-6">
                    <li>Data akun: nama, email, password yang disimpan sebagai hash, dan token sesi. Jika Anda memilih Login Google, nama dan email yang terverifikasi digunakan untuk membuat atau mengakses akun; kami tidak meminta password Google Anda.</li>
                    <li>Data undangan: jenis undangan, nama mempelai dan data keluarga untuk pernikahan; nama lengkap, nama panggilan, usia opsional, dan nama pengundang untuk ulang tahun; serta tanggal dan lokasi acara, alamat, link Google Maps, latitude, longitude, judul acara, dress code, template, musik, dan status publish. Form ulang tahun tidak meminta tanggal lahir lengkap.</li>
                    <li>Media yang Anda unggah: foto mempelai atau orang yang berulang tahun, foto galeri, foto Moment, dan file musik pilihan sendiri.</li>
                    <li>Data Moment dan interaksi: caption, komentar, reaksi suka/love, serta nama dan nomor WhatsApp yang Anda kirim saat meminta undangan.</li>
                    <li>Data Wedding Gift atau Kado Digital yang diatur pemilik: status aktif, nama penerima, catatan penerima, minimum gift, dan preferensi ucapan.</li>
                    <li>Data gift dari tamu pada halaman web undangan: nama tamu, nomor HP opsional, nominal gift, biaya layanan, total bayar, ucapan opsional, order ID, status transaksi, dan respons penyedia pembayaran Xendit atau Midtrans sesuai layanan yang digunakan.</li>
                    <li>Data pencairan: nama bank, nomor rekening, nama pemilik rekening, nominal pencairan, status, dan referensi transfer manual admin.</li>
                    <li>Data teknis: alamat IP, user agent, waktu akses, log server, dan jumlah view undangan publik.</li>
                    <li>Data notifikasi perangkat: token Firebase Cloud Messaging (FCM), platform, nama perangkat jika tersedia, versi aplikasi, dan waktu perangkat terakhir terdaftar untuk pengiriman notifikasi.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Cara Kami Menggunakan Data</h2>
                <ul class="mt-3 list-disc space-y-2 pl-6">
                    <li>Membuat draft dan undangan publik yang dapat dibuka melalui link unik.</li>
                    <li>Menyimpan draft lokal di perangkat sebelum login, lalu menyinkronkannya ke backend setelah login atau register.</li>
                    <li>Menampilkan template, foto, musik, lokasi, tombol peta, tombol share, dan gift pada halaman web undangan.</li>
                    <li>Memproses Wedding Gift atau Kado Digital melalui penyedia pembayaran dari halaman web undangan, bukan dari aplikasi mobile.</li>
                    <li>Memverifikasi status pembayaran hanya melalui webhook penyedia yang tervalidasi atau pengecekan status backend.</li>
                    <li>Menyediakan dashboard transaksi dan proses pencairan manual untuk pemilik undangan dan admin.</li>
                    <li>Menampilkan Moment publik dan interaksi, meneruskan permintaan undangan kepada pemilik, serta mengirim notifikasi permintaan, komentar, reaksi, dan gift yang berhasil.</li>
                    <li>Menjaga keamanan, mencegah penyalahgunaan, memperbaiki bug, dan memenuhi kewajiban hukum.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Undangan, Feed Publik, Dan Foto Anak</h2>
                <p class="mt-3">
                    Undangan pernikahan yang dipublish dapat tampil pada feed Moment dengan nama panggilan, foto, caption, dan interaksi.
                    Undangan ulang tahun tidak otomatis masuk feed; pemilik harus memberikan persetujuan eksplisit untuk menampilkannya.
                    Feed tidak menampilkan tanggal acara, alamat, koordinat, nama lengkap, atau usia dari form undangan.
                    Namun, foto, caption, dan komentar yang Anda kirim dapat memuat informasi pribadi; periksa konten sebelum membagikannya.
                </p>
                <p class="mt-3">
                    Halaman undangan yang dipublish dapat dibuka siapa pun yang memperoleh linknya, termasuk data acara dan media di dalamnya.
                    Menyembunyikan undangan dari feed tidak menjadikan link tersebut berpassword.
                    Nama dan nomor WhatsApp pada permintaan undangan diteruskan kepada pemilik, bukan ditampilkan sebagai komentar publik.
                    Pengelolaan undangan dan gift untuk anak dilakukan oleh orang tua atau wali; pastikan Anda memiliki izin untuk mengunggah foto dan data anak maupun orang lain.
                </p>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Wedding Gift, Kado Digital, Dan Pembayaran</h2>
                <p class="mt-3">
                    Aplikasi mobile tidak menyediakan checkout gift, QRIS pembayaran, atau pembelian fitur digital menggunakan Xendit maupun Midtrans.
                    Untuk gift, mobile app hanya digunakan pemilik untuk mengaktifkan fitur, mengatur penerima, dan melihat dashboard transaksi serta pencairan.
                    Tamu melakukan pembayaran melalui browser pada halaman web undangan. Gift tidak membuka fitur digital dalam aplikasi.
                </p>
                <p class="mt-3">
                    Nominal gift, biaya layanan, dan total bayar ditampilkan transparan sebelum pembayaran. Status <em>paid</em> tidak dipercaya dari callback frontend;
                    status hanya diperbarui dari webhook penyedia yang tervalidasi atau pengecekan status dari backend.
                </p>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Pihak Ketiga</h2>
                <ul class="mt-3 list-disc space-y-2 pl-6">
                    <li>Xendit atau Midtrans untuk memproses gift dan mengirim status transaksi sesuai layanan yang digunakan.</li>
                    <li>Google untuk Login Google yang Anda pilih, serta Firebase Cloud Messaging untuk pengiriman push notification ke perangkat. Token perangkat dan isi notifikasi yang diperlukan diteruskan ke Firebase.</li>
                    <li>Google Maps atau link peta yang Anda masukkan untuk membantu tamu membuka lokasi acara.</li>
                    <li>WhatsApp atau fitur share perangkat saat Anda memilih membagikan link undangan.</li>
                    <li>Penyedia hosting/server untuk menjalankan backend, database, storage, dan log keamanan.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Izin Aplikasi Mobile</h2>
                <p class="mt-3">
                    Aplikasi dapat meminta akses foto atau dokumen agar Anda bisa memilih foto dan file musik dari perangkat.
                    Izin notifikasi bersifat opsional dan dapat dinonaktifkan melalui pengaturan perangkat.
                    Aplikasi tidak meminta akses kamera, kontak, lokasi real-time, mikrofon, SMS, atau telepon.
                </p>
            </div>

            <div>
                <h2 class="font-serif text-2xl text-amber-100">Penyimpanan Dan Keamanan</h2>
                <p class="mt-3">
                    Password disimpan dalam bentuk hash. Koneksi production menggunakan HTTPS. Midtrans Server Key hanya disimpan di backend Laravel; kunci rahasia Xendit, Google OAuth, dan Firebase juga tidak disematkan ke aplikasi mobile.
                    Data draft lokal dapat tersimpan sementara di perangkat sampai Anda login, publish, atau menghapus draft.
                </p>
                <p class="mt-3">
                    Draft yang kedaluwarsa dan media undangan yang telah diarsipkan dapat dihapus otomatis sesuai jadwal retensi layanan.
                    Data transaksi dan pencairan dapat tetap disimpan untuk rekonsiliasi, keamanan, dan penyelesaian permintaan terkait gift.
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
