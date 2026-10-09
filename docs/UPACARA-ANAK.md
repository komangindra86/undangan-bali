# Undangan Upacara Bayi dan Anak

Jenis undangan untuk upacara Manusa Yadnya bagi bayi atau anak. Semuanya
memakai satu konfigurasi, satu form, dan satu kerangka halaman, sehingga
menambah upacara baru cukup dengan entri konfigurasi dan template.

| Jenis (`invitation_type`) | Nama tampil | Jenis acara | Template |
| --- | --- | --- | --- |
| `pitung_dina` | Abulan Pitung Dina (42 Hari) | `Abulan Pitung Dina` | Rare Kencana, Sekar Jepun, Langit Kumara |

## Data

- Ayah memakai kolom `groom_*`, ibu memakai `bride_*`. Nama lengkap dan
  panggilan keduanya wajib untuk publish.
- Data buah hati seluruhnya opsional: `child_full_name`, `child_nickname`,
  `child_gender` (`putra` / `putri`), `child_order`, `child_birth_date`,
  `child_photo`. Migration: `2026_10_09_000000_add_child_ceremony_invitations`.
- Draft mobile mengirim `groom_data`, `bride_data`, dan `child_data`.
  Jenis lain mengabaikan `child_*`.

Judul undangan (`display_name`) menyebut orang tua, ayah lebih dulu:
`Putra Wira & Ayu`, `Putri Wira & Ayu`, atau `Buah Hati Wira & Ayu` bila
putra/putri tidak dipilih. Slug: `abulan-pitung-dina-putri-wira-ayu`.

## Halaman

`resources/views/invitations/templates/upacara-anak/page.blade.php` dengan
`public/css/upacara-anak.css`. Isi: Om Swastyastu, nama upacara dan
keterangannya, kata pembuka, buah hati (foto, nama, putra/putri, anak ke-,
tanggal lahir bila diisi) beserta nama ayah dan ibu, waktu dan tempat, peta,
galeri, Tanda Kasih, dan penutup Om Shanti, Shanti, Shanti Om.

## Feed dan Gift

Tampil di feed Moment setelah publish seperti pernikahan dan megedong; pemilik
dapat menyembunyikannya. Foto buah hati ikut tampil di feed, dan layar
konfirmasi memberi tahu hal ini sebelum publish. Gift berlabel "Tanda Kasih".

## Menambah Upacara Baru

Backend:

1. `Invitation::TYPES`, `Invitation::EVENT_TYPES`, dan
   `Invitation::CHILD_CEREMONIES` (`title`, `note`).
2. Template pada `InvitationTemplateSeeder` dengan `invitation_type` baru.
   Tema baru menambah berkas `upacara-anak/<slug>.blade.php` dan aturan
   `.anak--<tema>` di CSS.

Mobile, semuanya di `mobile/src/constants/invitation.js`:

1. `INVITATION_TYPES` dan entri `TYPE_INFO` dengan `childCeremony: true`.
2. Warna kartu template pada `TEMPLATE_CARD_COLORS`.
3. Label pada `mobile/src/utils/templateCatalog.js`.

## Verifikasi

```sh
php artisan test --compact --filter=ChildCeremonyInvitationTest
cd mobile
node tests/child-ceremony-flow.test.cjs
```
