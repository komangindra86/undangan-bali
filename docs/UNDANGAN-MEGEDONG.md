# Undangan Megedong-gedongan

Jenis undangan ketiga setelah pernikahan dan ulang tahun, untuk upacara
Manusa Yadnya saat kandungan (garbha wedana).

## Alur

Tombol Buat menampilkan Pernikahan, Ulang Tahun, dan Megedong-gedongan. Wizard:
template, data calon orang tua, acara, lokasi, galeri, musik, Tanda Kasih
(opsional), lalu konfirmasi. Login baru diminta saat publish.

Wajib: nama lengkap dan panggilan calon ibu serta calon ayah. Opsional: foto
keduanya, usia kandungan (teks bebas, contoh `7 bulan`), dan anak ke- (contoh
`Anak pertama`). Jenis acara selalu `Megedong-gedongan`. Kata pembuka sudah
diisi contoh dan dapat diedit.

## Data

Tidak ada tabel baru. Calon ibu memakai kolom `bride_*`, calon ayah memakai
kolom `groom_*`. Migration `2026_10_08_000000_add_megedong_invitations`
menambah `pregnancy_age` dan `child_order` pada `invitations`.

`invitation_type` bernilai `megedong`. Draft mobile mengirim `bride_data`,
`groom_data`, dan `megedong_data` (`pregnancy_age`, `child_order`). Jenis
undangan tidak dapat diubah setelah draft dibuat.

Nama tampilan mendahulukan ibu: `Ayu & Wira`. Slug:
`megedong-gedongan-<panggilan ibu>-<panggilan ayah>`.

## Template

- Garbha Kencana: klasik Bali gelap dengan bingkai emas.
- Padma Sari: pastel hangat bernuansa bunga padma.
- Tirta Hening: minimalis hijau sage.

Ketiganya memakai `resources/views/invitations/templates/megedong/page.blade.php`
dan `public/css/megedong-invitation.css`. Tanpa foto, halaman memakai ilustrasi
padma dan inisial nama. Isi halaman: Om Swastyastu, kata pembuka, calon ibu dan
ayah, usia kandungan, waktu dan tempat, peta, galeri, Tanda Kasih, dan penutup
Om Shanti, Shanti, Shanti Om.

## Feed dan Gift

- Tampil di feed Moment setelah publish seperti undangan pernikahan; pemilik
  dapat menyembunyikannya dari tab Undangan. Layar konfirmasi memberi tahu hal
  ini sebelum publish. Feed tidak memuat jadwal, alamat, atau usia kandungan.
- Gift memakai tabel, provider, dan pencairan yang sama dengan label
  "Tanda Kasih".

## Kompatibilitas

`GET /api/templates` tanpa parameter tetap hanya berisi template pernikahan.
Katalog baru: `GET /api/templates?invitation_type=megedong`. Aplikasi sebelum
1.0.23 tidak menampilkan pilihan ini dan tidak terpengaruh.

## Deploy

Backend lebih dulu, lalu aplikasi:

```sh
git pull origin main
php artisan migrate --force
php artisan db:seed --class=InvitationTemplateSeeder --force
```

`scripts/deploy-vps.sh` sudah menjalankan ketiganya.

## Verifikasi

```sh
php artisan test --compact --filter=MegedongInvitationTest
cd mobile
node tests/megedong-flow.test.cjs
```
