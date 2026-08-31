# Undangan Ulang Tahun (MVP)

## Alur

Tombol Buat membuka pilihan Pernikahan atau Ulang Tahun. Pilihan ini tidak
memerlukan login. Alur pernikahan tetap tersedia seperti sebelumnya.

Wizard ulang tahun: template, data yang berulang tahun, acara, lokasi,
galeri, musik, Kado Digital opsional, lalu konfirmasi data. Login baru diminta
saat publish. Draft lokal dapat dilanjutkan dari tab Undangan atau pilihan
jenis undangan. Memulai draft baru meminta konfirmasi jika ada draft lokal.

Nama lengkap dan panggilan wajib; panggilan maksimal 18 karakter. Usia 1-150
tahun bersifat opsional dan bukan tanggal lahir. Nama pengundang, judul acara,
dress code, foto, galeri, peta, musik, dan kado juga opsional. Tanggal acara
tidak boleh sebelum hari ini. Foto memakai kompresi yang sudah digunakan app.

## Template

- Ceria Confetti: warna cerah, kartu foto miring, tipografi tebal.
- Ruang Putih: minimalis, tipografi editorial, layout dua kolom di desktop.
- Bali Pradnyan: hijau tua, bingkai dan ornamen emas.

Preview menggunakan ilustrasi, bukan foto orang lain. Undangan asli memakai
foto pemilik; tanpa foto utama akan memakai ilustrasi. Galeri contoh tidak
ditambahkan ke undangan asli. Cover harus dibuka sebelum isi dapat diakses,
dan klik tersebut memulai musik jika dipilih. Parameter `?to=Nama%20Tamu`
tetap didukung.

## Privasi dan Pembayaran

- Ulang tahun tidak otomatis masuk feed. Backend mengabaikan upaya mengaktifkan
  feed lewat payload draft. Endpoint visibilitas memerlukan persetujuan eksplisit
  `privacy_acknowledged: true` dan mencatat `feed_consent_at`.
- Jika pemilik memilih berbagi, foto, nama panggilan dan konten Moment menjadi
  publik. Form persetujuan mengingatkan izin orang tua/wali untuk foto anak.
- Feed tidak menyertakan usia, nama lengkap, pengundang, alamat, tanggal acara,
  atau koordinat. Teks/foto yang sengaja diunggah tetap bisa memuat informasi
  pribadi; aplikasi tidak otomatis menyensor isi foto atau caption.
- Tersembunyi dari feed bukan autentikasi link. Siapa pun yang memperoleh link
  undangan dapat membukanya. Jangan anggap link sebagai undangan berpassword.
- Kado Digital memakai tabel, API, provider, biaya, webhook, dashboard dan
  pencairan gift yang sudah ada. Nama tabel/endpoint tidak diganti agar klien
  lama tetap kompatibel. Tidak ada pembayaran dalam aplikasi atau fitur digital
  yang dibuka oleh pembayaran kado. Penerima/pengelola akun untuk undangan anak
  sebaiknya orang tua/wali; aplikasi tidak membuat akun anak secara otomatis.
- Retensi foto ulang tahun mengikuti jadwal yang sudah ada. Aset template dan
  preview tidak ikut dihapus oleh pembersihan media undangan.

## Aktivasi Backend

Backup database dan storage sebelum deploy. Jalankan dari direktori Laravel
menggunakan PHP 8.2, deploy backend sebelum mendistribusikan aplikasi baru:

```sh
git pull origin main
php artisan migrate --force
php artisan db:seed --class=InvitationTemplateSeeder --force
php artisan optimize:clear
php artisan queue:restart
```

Jangan menjalankan `migrate:fresh` atau seeder seluruh database di production.
Seeder template dapat dijalankan ulang dan tidak mengganti user/password.
Migration memberi nilai `wedding` pada data lama. Jangan rollback migration
ini setelah undangan ulang tahun digunakan karena kolom datanya akan terhapus.

`GET /api/templates` tanpa parameter tetap hanya berisi template pernikahan
untuk aplikasi lama. Katalog baru menggunakan
`GET /api/templates?invitation_type=birthday`.

Draft baru menyertakan `invitation_type: birthday`, `selected_template`,
`birthday_data` (celebrant_full_name, celebrant_nickname, celebrant_age,
host_name), dan `event_data.event_type: Ulang Tahun`. Foto `celebrant_photo`
dikirim multipart; update tidak boleh mengubah jenis undangan yang sudah ada.

## Verifikasi dan Distribusi

```sh
php artisan test --compact
cd mobile
node tests/birthday-flow.test.cjs
npx expo-doctor
npx expo export --platform android --platform web --output-dir dist
```

Perubahan screen mobile memerlukan AAB baru. Export Metro di atas hanya
memeriksa bundel JavaScript, bukan menghasilkan AAB bertanda tangan.
Versi/versionCode belum dinaikkan pada perubahan ini. Naikkan saat proses
release, uji melalui Internal Testing, lalu tentukan rollout terpisah.

Sebelum rilis, uji pada perangkat Android: foto dari galeri, musik custom,
kalender/jam, login Google, resume setelah app ditutup, publish, dan share WA.
Tes provider menggunakan respons palsu; pembayaran/pencairan uang nyata tidak
dijalankan otomatis. Tinjau juga Privacy Policy dan Data Safety sebelum rollout
untuk memastikan deskripsi data foto/usia opsional sesuai fitur yang dirilis.
