# Undangan Upacara Bayi dan Anak

Jenis undangan untuk upacara Manusa Yadnya bagi bayi atau anak. Semuanya
memakai satu konfigurasi, satu form, dan satu kerangka halaman, sehingga
menambah upacara baru cukup dengan entri konfigurasi dan template.

| Jenis (`invitation_type`) | Nama tampil | Jenis acara | Template |
| --- | --- | --- | --- |
| `pitung_dina` | Abulan Pitung Dina (42 Hari) | `Abulan Pitung Dina` | Rare Kencana, Sekar Jepun, Langit Kumara |
| `tigang_sasih` | Tigang Sasih (3 Bulanan) | `Tigang Sasih` | Nyambutin Kencana, Tunjung Petak, Surya Alit, ditambah tiga desain abulan pitung dina |
| `otonan` | Otonan | `Otonan` | Oton Kencana, Sekar Cempaka, Purnama Alit, ditambah keenam desain di atas |

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

Upacara dengan `child_named` (tigang sasih, otonan) mewajibkan nama lengkap dan
panggilan buah hati untuk publish. Nama panggilan menjadi judul (`Kirana`),
dengan keterangan `Putri dari Wira & Ayu` (`child_relation`) di bawahnya;
bila putra/putri kosong tertulis `Buah hati dari Wira & Ayu`. Slug:
`tigang-sasih-kirana`. Draft yang belum bernama tetap memakai judul orang tua.

Otonan berulang setiap 210 hari, jadi undangannya berlaku untuk otonan ke
berapa pun. Upacara dengan `numbered` punya isian opsional `ceremony_number`
(1-200, dikirim di `child_data`; migration
`2026_10_12_000000_add_ceremony_number_to_invitations`). Bila diisi, keterangan
di bawah nama upacara menjadi `Otonan Pertama` atau `Otonan ke-3`, teks
bagikan menyebut `upacara otonan ke-3 buah hati kami, Kirana`, dan slug menjadi
`otonan-ke-3-kirana`. Bila kosong hanya tertulis `Otonan` (slug
`otonan-kirana`). Jenis lain mengabaikan `ceremony_number`.

## Desain Bersama

Satu baris template hanya untuk satu jenis undangan. Desain yang ditawarkan ke
lebih dari satu upacara mendapat baris sendiri dengan slug berbeda dan
`blade_view` yang sama, misalnya `tigang-sasih-rare-kencana` memakai
`upacara-anak.rare-kencana`. Aplikasi mengenali desain dari bagian akhir
`blade_view` (`templateDesignKey`), bukan dari slug.

## Halaman

Ada dua generasi desain. Tiga desain otonan (Oton Kencana, Sekar Cempaka,
Purnama Alit) punya tata letak sendiri dengan ornamen Bali: lihat
[Desain Berornamen](#desain-berornamen). Enam desain lainnya masih memakai
satu halaman bersama yang hanya berbeda warna:

`resources/views/invitations/templates/upacara-anak/page.blade.php` dengan
`public/css/upacara-anak.css`. Isi: Om Swastyastu, nama upacara dan
keterangannya, kata pembuka, buah hati (foto, nama, putra/putri, anak ke-,
tanggal lahir bila diisi) beserta nama ayah dan ibu, waktu dan tempat, peta,
galeri, Tanda Kasih, dan penutup Om Shanti, Shanti, Shanti Om.

## Desain Berornamen

`upacara-anak/frame.blade.php` menyiapkan data, sampul pembuka, dan skrip, lalu
memuat isi desain dari `upacara-anak/designs/<slug>.blade.php`. Gaya ada di
`public/css/upacara-bali.css` (`.ub--<slug>`). Tiap desain menyusun bagiannya
sendiri, jadi desain baru berarti berkas isi baru, bukan sekadar warna baru.

| Desain | Tata letak | Ornamen |
| --- | --- | --- |
| Oton Kencana | Simetris; tanggal berdiri di antara candi bentar | Candi bentar, tedung, banten, ider-ider, kain prada, patra |
| Sekar Cempaka | Asimetris rata kiri; foto dan tanggal berbentuk lengkung | Penjor dengan sampian dan sanggah, canang sari |
| Purnama Alit | Garis waktu; galeri geser | Roda pawukon, purnama, awan Kamasan, kain poleng |

Ornamen digambar sebagai SVG di `upacara-anak/ornaments/` dan mengambil warna
dari variabel CSS desainnya (`--orn`, `--orn-cloth`, dan seterusnya). Benang
tridatu dan kain poleng dibuat dengan CSS (`.ub-tridatu`, `.ub-poleng`).

Ciri upacara: `CHILD_CEREMONIES[...]['meaning']` berisi satu kalimat tentang
upacaranya, ditampilkan bersama benang tridatu; Purnama Alit juga menulis
`30 wuku × 7 hari = 210 hari`.

Sampul pembuka memakai foto buah hati bila ada; bila tidak, ilustrasi
`public/images/otonan-*.svg` dengan bayangan yang lebih tipis.

## Feed dan Gift

Tampil di feed Moment setelah publish seperti pernikahan dan megedong; pemilik
dapat menyembunyikannya. Foto buah hati ikut tampil di feed, dan layar
konfirmasi memberi tahu hal ini sebelum publish. Gift berlabel "Tanda Kasih".

## Menambah Upacara Baru

Backend:

1. `Invitation::TYPES`, `Invitation::EVENT_TYPES`, dan
   `Invitation::CHILD_CEREMONIES` (`title`, `note`, `child_named`,
   `age_days`, `numbered`).
2. Template pada `InvitationTemplateSeeder` dengan `invitation_type` baru.
   Tema baru menambah berkas `upacara-anak/<slug>.blade.php` dan aturan
   `.anak--<tema>` di CSS.

Mobile, semuanya di `mobile/src/constants/invitation.js`:

1. `INVITATION_TYPES` dan entri `TYPE_INFO` dengan `childCeremony: true`
   (serta `childNamed: true` bila nama buah hati wajib dan `numbered: true`
   bila upacaranya berulang).
2. Warna kartu template pada `TEMPLATE_CARD_COLORS`.
3. Label pada `mobile/src/utils/templateCatalog.js`.

## Verifikasi

```sh
php artisan test --compact --filter=ChildCeremonyInvitationTest
cd mobile
node tests/child-ceremony-flow.test.cjs
```
