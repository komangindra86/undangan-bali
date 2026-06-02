# Undangan Pernikahan Bali - Backend MVP

Backend Laravel untuk aplikasi pembuat undangan pernikahan Bali. Tahap ini menyediakan API mobile, autentikasi token, sinkronisasi draft setelah login, publish dengan slug unik, dan halaman undangan publik.

## Stack

- PHP 8.2
- Laravel 12 + Laravel Sanctum
- MySQL 8
- Blade + Tailwind CDN untuk template publik MVP
- File publik melalui `storage/app/public`

## Fitur Tahap 1

- Register, login, logout, dan profil user API.
- Daftar template aktif dan tiga metadata musik bawaan.
- Draft undangan milik user setelah login.
- Endpoint `sync-local-draft` untuk menerima draft AsyncStorage dari mobile.
- Publish hanya jika data pasangan dan acara minimum lengkap.
- Perubahan pada undangan published mengembalikannya menjadi draft hingga dipublish ulang.
- Slug publik unik otomatis, contoh `/u/undangan-wira-ayu`.
- Tiga template publik dengan identitas berbeda: `Bali Classic` gelap ceremonial, `Pura Sunset` sinematik dengan countdown, dan `Ubud Garden` editorial terang.
- Preview dummy template sebelum dipilih, lengkap dengan foto, galeri, animasi, tombol Maps/share, dan watermark.
- Upload foto mempelai dan maksimal enam foto galeri milik user; foto dummy hanya tampil pada preview template.
- Tiga cuplikan musik lokal ringan dan upload musik sendiri (MP3/WAV/M4A maksimal 10 MB) dengan tombol putar manual pada undangan browser.
- Pencatatan setiap view halaman publik.
- Wedding Gift melalui Midtrans QRIS: pembayaran hanya terjadi di halaman web undangan, sedangkan aplikasi mobile mengatur dan memonitor.
- Fee layanan tampil transparan kepada tamu, dan status paid hanya bersumber dari webhook bertanda tangan atau Get Status API Midtrans.
- Wizard mobile menawarkan Wedding Gift sesudah langkah musik, sebelum konfirmasi/publish; pilihan user baru disimpan sebagai draft lokal sampai login.
- Pencairan Wedding Gift MVP: pasangan menyimpan rekening dan mengajukan klaim di mobile; admin mentransfer manual dan mencatat referensi dari dashboard Blade.

Dashboard admin dikerjakan pada tahap berikutnya. Aplikasi Expo MVP tersedia di folder `mobile`.

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

## Wedding Gift QRIS

Tambahkan key Sandbox Midtrans ke `.env`:

```dotenv
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxx
MIDTRANS_IS_PRODUCTION=false
WEDDING_GIFT_FEE_TYPE=flat
WEDDING_GIFT_FEE_VALUE=2000
WEDDING_GIFT_FEE_FLAT_BELOW_AMOUNT=100000
WEDDING_GIFT_FEE_FLAT_VALUE=2000
WEDDING_GIFT_FEE_PERCENT_VALUE=2
WEDDING_GIFT_MINIMUM_AMOUNT=10000
WEDDING_GIFT_PAYOUT_MINIMUM_AMOUNT=50000
```

`WEDDING_GIFT_FEE_*` adalah konfigurasi sistem. Payload mobile tidak dapat mengganti fee tersebut. Default fee otomatis: gift di bawah Rp100.000 dikenakan Rp2.000, sedangkan gift Rp100.000 ke atas dikenakan 2%. Contoh gift Rp150.000 menghasilkan biaya layanan Rp3.000, QRIS dibuat dengan `gross_amount` Rp153.000, dan pasangan tetap tercatat menerima Rp150.000.

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
    "service_fee": 2000,
    "total_amount": 102000,
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

### Catatan Play Store

Aplikasi mobile tidak berisi tombol pembayaran Wedding Gift atau checkout QRIS; ia hanya mengatur dan memonitor gift. Pembayaran tamu dilakukan di web publik dan tidak membuka fitur digital. Namun, karena model ini mengenakan fee aplikasi, evaluasi kebijakan Play Store kembali sebelum rilis produksi; pembelian template premium atau penghapusan watermark di dalam aplikasi tetap harus memakai Google Play Billing.

## Klaim Dan Pencairan Gift

MVP memakai pencairan manual admin. Dana QRIS diterima merchant aplikasi melalui Midtrans; pasangan tidak otomatis menerima transfer saat tamu membayar.

Alur pasangan:

1. Buka `Dashboard Gift` pada mobile setelah ada transaksi berstatus `paid`.
2. Pilih **Kelola Rekening** dan simpan bank, nomor rekening, serta nama pemilik.
3. Pilih **Ajukan Pencairan**. Minimum default adalah `Rp50.000`.
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

## Mobile Expo

Lihat panduan mobile pada [`mobile/README.md`](mobile/README.md). Mobile menggunakan Expo SDK 55 dan mengakses backend melalui nilai `EXPO_PUBLIC_API_URL`.
