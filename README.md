# Undangan Bali Santih - Backend MVP

Backend Laravel untuk aplikasi pembuat undangan pernikahan Bali dan ulang tahun. Backend menyediakan API mobile, autentikasi token, sinkronisasi draft setelah login, publish dengan slug unik, dan halaman undangan publik.

## Stack

- PHP 8.2
- Laravel 12 + Laravel Sanctum
- MySQL 8
- Blade + Tailwind CDN untuk template publik MVP
- File publik melalui `storage/app/public`

## Fitur

- Register, login, logout, dan profil user API.
- Daftar template aktif (lima pernikahan, tiga ulang tahun; lihat [`docs/UNDANGAN-ULANG-TAHUN.md`](docs/UNDANGAN-ULANG-TAHUN.md)) dan katalog musik berlisensi (lihat [`docs/KATALOG-MUSIK.md`](docs/KATALOG-MUSIK.md)).
- Draft undangan milik user setelah login.
- Endpoint `sync-local-draft` untuk menerima draft AsyncStorage dari mobile.
- Publish hanya jika data pasangan dan acara minimum lengkap.
- Perubahan pada undangan published mengembalikannya menjadi draft hingga dipublish ulang.
- Slug publik unik otomatis, contoh `/u/undangan-wira-ayu`.
- Template pernikahan: `Bali Classic`, `Pura Sunset`, `Ubud Garden`, `Royal Kamasan`, dan `Puspa Kencana` (animasi). Template ulang tahun: `Ceria Confetti`, `Ruang Putih`, dan `Bali Pradnyan`.
- Preview dummy template sebelum dipilih, lengkap dengan foto, galeri, animasi, tombol Maps/share, dan watermark.
- Upload foto mempelai dan maksimal enam foto galeri milik user; foto dummy hanya tampil pada preview template.
- Musik dari katalog bawaan atau upload musik sendiri (MP3/WAV/M4A maksimal 10 MB, wajib persetujuan hak cipta) yang diputar saat cover undangan dibuka.
- Pencatatan setiap view halaman publik.
- Wedding Gift melalui Midtrans QRIS atau Xendit Invoice (`WEDDING_GIFT_PAYMENT_PROVIDER`): pembayaran hanya terjadi di halaman web undangan, sedangkan aplikasi mobile mengatur dan memonitor.
- Tamu tidak dikenakan biaya layanan; fee platform dipotong saat pasangan mencairkan dana. Status paid hanya bersumber dari webhook terverifikasi atau Get Status API provider.
- Wizard mobile menawarkan Wedding Gift sesudah langkah musik, sebelum konfirmasi/publish; pilihan user baru disimpan sebagai draft lokal sampai login.
- Pencairan Wedding Gift MVP: pasangan menyimpan rekening dan mengajukan klaim di mobile; admin mentransfer manual dan mencatat referensi dari dashboard Blade (`/admin`).
- Feed sosial Moment: undangan dapat tampil di feed, menerima reaksi, komentar, dan permintaan undangan; notifikasi push melalui Firebase Cloud Messaging.
- Link undangan personal per tamu melalui parameter `?to=Nama%20Tamu`.
- Jadwal harian: arsip undangan lewat tanggal, pembersihan media, dan penghapusan draft kedaluwarsa (`routes/console.php`).

Aplikasi Expo tersedia di folder `mobile`.

## Struktur Penting

```text
app/
  Http/Controllers/Api/       # Auth, template, musik, invitation REST
  Services/MidtransService.php # Charge QRIS, verifikasi webhook, sinkron status
  Http/Controllers/            # PublicInvitationController
  Http/Requests/               # Validasi draft invitation
  Models/                      # User, Invitation, Template, Music, View
database/
  migrations/                  # Schema MySQL
  seeders/DatabaseSeeder.php   # Admin, 3 template, 3 musik
resources/views/
  landing.blade.php
  invitations/templates/bali-experience.blade.php
  invitations/partials/wedding-gift.blade.php
routes/
  api.php
  web.php
mobile/
  src/screens/                # Wizard, auth gate, publish/share
  src/context/                # Draft AsyncStorage dan sesi login
  src/services/api.js         # Klien REST terpusat
  src/services/imageService.js # Resize dan kompres foto sebelum upload
tests/Feature/
  InvitationPublishingTest.php
```

## Menjalankan Dari Awal

Untuk deploy ke VPS, pull dari GitHub, dan memasang SSL Let's Encrypt, ikuti [`DEPLOYMENT.md`](DEPLOYMENT.md).

### Cara paling mudah di laptop

Klik dua kali file:

```text
JALANKAN-DI-LAPTOP.bat
```

Tunggu sekitar 10 detik. Browser akan membuka tampilan aplikasi mobile. Jangan tutup dua jendela terminal yang muncul selama aplikasi masih digunakan.

### Cara manual

Pastikan Laragon memakai PHP `8.2.27` atau PHP 8.2 lain, lalu jalankan:

```powershell
cd C:\laragon\www\undangan-bali
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Buat database MySQL:

```sql
CREATE DATABASE undangan_bali CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE undangan_bali_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sesuaikan kredensial `DB_*` pada `.env`, kemudian:

```powershell
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Aplikasi akan tersedia di `http://127.0.0.1:8000`. Akun admin seed untuk pengembangan adalah `admin@undanganbali.test` dengan password `password`; ubah sebelum lingkungan nyata.

File audio berlisensi yang dipakai sebagai musik bawaan dapat ditempatkan di:

```text
storage/app/public/musics/bali-romantis.mp3
storage/app/public/musics/janji-suci.mp3
storage/app/public/musics/senja-bahagia.mp3
```

## API MVP

Endpoint publik:

```text
POST /api/register
POST /api/login
GET  /api/templates
GET  /api/templates/{id}
GET  /api/musics
GET  /u/{slug}
GET  /preview/templates/{template-slug}
```

Endpoint dengan header `Authorization: Bearer {token}`:

```text
POST   /api/logout
GET    /api/me
GET    /api/invitations
POST   /api/invitations
GET    /api/invitations/{id}
PUT    /api/invitations/{id}
DELETE /api/invitations/{id}
POST   /api/invitations/sync-local-draft
POST   /api/invitations/{id}/publish
GET    /api/invitations/{id}/gift-setting
POST   /api/invitations/{id}/gift-setting
GET    /api/invitations/{id}/gifts
GET    /api/payout-account
POST   /api/payout-account
GET    /api/invitations/{id}/payout-requests
POST   /api/invitations/{id}/payout-requests
```

Endpoint publik Wedding Gift:

```text
POST /api/public/invitations/{slug}/wedding-gift/create
GET  /api/public/wedding-gift/{order_id}/status
POST /api/midtrans/webhook
```

Contoh payload sinkronisasi draft mobile:

```json
{
  "selected_template": 1,
  "groom_data": {
    "groom_full_name": "I Made Wira",
    "groom_nickname": "Wira"
  },
  "bride_data": {
    "bride_full_name": "Ni Putu Ayu",
    "bride_nickname": "Ayu"
  },
  "event_data": {
    "event_type": "Pawiwahan",
    "event_date": "2026-08-18",
    "start_time": "10:00",
    "end_time": "13:00",
    "venue_name": "Bale Banjar Ubud",
    "venue_address": "Jalan Raya Ubud, Gianyar, Bali"
  },
  "location_data": {
    "google_maps_url": "https://maps.google.com/?q=-8.5069,115.2625",
    "latitude": -8.5069,
    "longitude": 115.2625
  },
  "music_data": {
    "music_type": "none"
  }
}
```

## Wedding Gift

Pilih provider dan isi key Sandbox/development pada `.env`:

```dotenv
WEDDING_GIFT_PAYMENT_PROVIDER=midtrans   # atau xendit

MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxx
MIDTRANS_IS_PRODUCTION=false

XENDIT_SECRET_KEY=xnd_development_xxxxxxxx
XENDIT_WEBHOOK_TOKEN=token-verifikasi-callback

WEDDING_GIFT_MINIMUM_AMOUNT=10000
WEDDING_GIFT_PAYOUT_MINIMUM_AMOUNT=50000
WEDDING_GIFT_PAYOUT_FEE_PERCENT=1
```

Fee adalah konfigurasi sistem dan tidak dapat diubah oleh payload mobile. Tamu membayar tepat sebesar nominal gift (`service_fee` 0, `total_amount` = `gift_amount`). Fee platform `WEDDING_GIFT_PAYOUT_FEE_PERCENT` (default 1%, dibulatkan ke atas) dipotong saat pasangan mengajukan pencairan. Contoh: pencairan Rp150.000 menghasilkan `platform_fee` Rp1.500 dan `net_amount` Rp148.500 yang ditransfer ke rekening pasangan.

Contoh mengaktifkan Wedding Gift untuk undangan milik user:

```http
POST /api/invitations/12/gift-setting
Authorization: Bearer {token}
Content-Type: application/json

{
  "is_active": true,
  "receiver_name": "Wira & Ayu",
  "receiver_note": "Matur suksma atas tanda kasih Anda.",
  "minimum_amount": 10000,
  "show_amount_public": false,
  "allow_message": true
}
```

Contoh tamu membuat QRIS dari halaman web:

```http
POST /api/public/invitations/undangan-wira-ayu/wedding-gift/create
Content-Type: application/json

{
  "guest_name": "Komang",
  "guest_phone": "08123456789",
  "gift_amount": 100000,
  "message": "Selamat berbahagia."
}
```

Contoh respons:

```json
{
  "message": "QRIS siap dipindai.",
  "data": {
    "order_id": "WGIFT-12-20260527143000-A1B2C3",
    "gift_amount": 100000,
    "service_fee": 0,
    "total_amount": 100000,
    "payment_type": "qris",
    "qr_image_url": "https://api.sandbox.midtrans.com/...",
    "transaction_status": "pending"
  }
}
```

### Testing Sandbox Midtrans

1. Buat akun Midtrans Sandbox dan isi Server Key/Client Key Sandbox pada `.env`.
2. Jalankan migrasi dan backend:

```powershell
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

3. Di dashboard Midtrans Sandbox, atur Payment Notification URL ke URL publik HTTPS yang meneruskan ke:

```text
https://DOMAIN-HTTPS-ANDA/api/midtrans/webhook
```

`localhost` tidak bisa menerima webhook Midtrans; saat pengembangan gunakan tunnel HTTPS seperti ngrok atau Cloudflare Tunnel.

4. Aktifkan Wedding Gift dari mobile, buka link `/u/{slug}` di browser, isi form, dan scan/simulasikan pembayaran QRIS pada Sandbox.
5. Tombol **Cek Status Pembayaran** memanggil backend, lalu backend memanggil Get Status API Midtrans. Webhook dan pengecekan status sama-sama idempotent; callback browser tidak pernah menetapkan status `paid`.

Untuk Xendit, respons `create` berisi `payment_url` (halaman Invoice Xendit) alih-alih QRIS. Atur callback URL Invoice ke `https://DOMAIN-HTTPS-ANDA/api/xendit/webhook`; backend menolak callback tanpa header `x-callback-token` yang cocok dengan `XENDIT_WEBHOOK_TOKEN`.

### Catatan Play Store

Aplikasi mobile tidak berisi tombol pembayaran Wedding Gift atau checkout QRIS; ia hanya mengatur dan memonitor gift. Pembayaran tamu dilakukan di web publik dan tidak membuka fitur digital. Namun, karena model ini mengenakan fee platform saat pencairan, evaluasi kebijakan Play Store kembali sebelum rilis produksi; pembelian template premium atau penghapusan watermark di dalam aplikasi tetap harus memakai Google Play Billing.

## Klaim Dan Pencairan Gift

MVP memakai pencairan manual admin. Dana gift diterima merchant aplikasi melalui Midtrans atau Xendit; pasangan tidak otomatis menerima transfer saat tamu membayar.

Alur pasangan:

1. Buka `Dashboard Gift` pada mobile setelah ada transaksi berstatus `paid`.
2. Pilih **Kelola Rekening** dan simpan bank, nomor rekening, serta nama pemilik.
3. Pilih **Ajukan Pencairan**. Minimum default adalah `Rp50.000`; fee platform 1% dipotong dari nominal pencairan.
4. Pantau status pada **Riwayat Pencairan**: menunggu, diproses, terkirim, atau ditolak.

Alur admin:

1. Buka `http://127.0.0.1:8000/admin/login` atau port backend yang sedang dipakai.
2. Pada lingkungan pengembangan, login seed: `admin@undanganbali.test` / `password`.
3. Buka daftar payout, periksa snapshot rekening yang disimpan saat pengajuan.
4. Transfer manual melalui kanal bank merchant.
5. Pilih status `Selesai dibayar`, isi referensi transfer, lalu simpan.

Saldo aman dari pencairan ganda: gift berstatus `paid` dialokasikan ke permintaan payout di dalam transaksi database. Pengajuan `pending`, `approved`, `processing`, atau `paid` mengunci nominal tersebut; pengajuan `rejected` mengembalikan saldo tersedia.

Tabel tambahan:

```text
gift_payout_accounts
gift_payout_requests
gift_payout_items
```

Untuk produksi, siapkan proses verifikasi identitas/rekening dan tinjau kewajiban kepatuhan penyaluran dana pengguna. Otomatisasi payout dapat dipertimbangkan kemudian melalui layanan disbursement seperti Midtrans Iris.

## Verifikasi

Pengujian menggunakan database `undangan_bali_test` agar database pengembangan tidak dibersihkan:

```powershell
php artisan test
vendor\bin\pint --test
```

Pastikan `php` yang dipakai adalah PHP 8.2, misalnya `C:\laragon\bin\php\php-8.2.27-Win32-vs16-x64\php.exe`. PHP lama di PATH akan gagal pada pemeriksaan platform Composer.

Tes mobile dijalankan dari folder `mobile`:

```powershell
Get-ChildItem tests\*.test.cjs | ForEach-Object { node $_.FullName }
```

## Mobile Expo

Lihat panduan mobile pada [`mobile/README.md`](mobile/README.md). Mobile menggunakan Expo SDK 55 dan mengakses backend melalui nilai `EXPO_PUBLIC_API_URL`.
