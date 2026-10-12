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

Isi tiap undangan: Om Swastyastu, nama upacara dan keterangannya, kata
pembuka, simbol dan arti upacara, buah hati (foto, nama, putra/putri, anak ke-,
tanggal lahir bila diisi) beserta nama ayah dan ibu, waktu dan tempat, peta,
galeri, Tanda Kasih, dan penutup Om Shanti, Shanti, Shanti Om.

`upacara-anak/frame.blade.php` menyiapkan data, sampul pembuka, dan skrip, lalu
memuat isi desain dari `upacara-anak/designs/<slug>.blade.php`. Gaya ada di
`public/css/upacara-bali.css` (`.ub--<slug>`). Tiap desain menyusun bagiannya
sendiri, jadi desain baru berarti berkas isi baru, bukan sekadar warna baru.
Potongan yang sama di banyak desain ada di `upacara-anak/parts/` (judul, data
buah hati, rincian acara, galeri, penutup).

## Desain

| Desain | Tata letak | Ornamen |
| --- | --- | --- |
| Rare Kencana | Simetris, krem dan emas; kartu bertepi ider-ider | Dua lamak janur menjuntai, bunga jepun, patra |
| Sekar Jepun | Serba bundar, putih dan kuning; foto di tengah karangan | Karangan bunga dan daun jepun |
| Langit Kumara | Rata kiri, langit biru lembut; kotak waktu dua kolom | Layangan bebean dengan ekornya, awan |
| Nyambutin Kencana | Simetris, terakota hangat; waktu dalam tiga lajur | Gebogan, umbul-umbul, tembok bata merah |
| Tunjung Petak | Tenang dan lapang, putih-hijau; bagian naik seperti riak | Kolam tunjung putih |
| Surya Alit | Judul rata kiri, jingga pagi; tanggal dalam lingkaran matahari | Matahari terbit di samping Gunung Agung, meru, pohon kelapa, sawah |
| Oton Kencana | Simetris, cahaya pagi di atas gading; tanggal berdiri di antara candi bentar | Candi bentar batu paras, tedung, banten, bunga jepun, bayangan daun, ider-ider, kain prada, patra |
| Sekar Cempaka | Asimetris rata kiri, merah muda lembut; foto dan tanggal berbentuk lengkung | Penjor dengan sampian dan sanggah, canang sari, bunga jepun |
| Purnama Alit | Langit senja di atas halaman terang; garis waktu; galeri geser | Roda pawukon, purnama, awan Kamasan, kain poleng |

Semuanya bernuansa lembut: latar terang, ornamen berisi gradasi dan bayangan
halus alih-alih garis tegas, kartu putih berbayang.

Ornamen digambar sebagai SVG di `upacara-anak/ornaments/`. Gradasi, filter
bayangan, serta kelopak jepun dan tunjung yang dipakai bersama ada di
`upacara-anak/parts/defs.blade.php`; ornamen merujuknya lewat atribut
(`fill="url(#ub-stone)"`), bukan lewat CSS, supaya berlaku di semua browser.
Benang tridatu dan kain poleng dibuat dengan CSS (`.ub-tridatu`, `.ub-poleng`).

## Simbol Upacara

Desain dipakai bersama oleh beberapa upacara, jadi ciri upacara datang dari
konfigurasi, bukan dari desain. `CHILD_CEREMONIES[...]['meaning']` berisi satu
kalimat tentang upacaranya dan `['emblem']` menunjuk gambar
`ornaments/emblem-<nama>.blade.php`; keduanya ditampilkan oleh
`parts/meaning.blade.php` di setiap desain.

| Upacara | Simbol | Kalimat |
| --- | --- | --- |
| Abulan pitung dina | `tirta`: sangku berisi tirta dan bunga jepun | Penyucian ibu dan buah hati pada umur 42 hari |
| Tigang sasih | `tapak`: dua tapak kaki mungil | Umur tiga bulan Bali (105 hari), pertama kali menapak Ibu Pertiwi |
| Otonan | `tridatu`: benang merah, putih, hitam | Hari kelahiran menurut pawukon, setiap 210 hari |

Purnama Alit, yang khusus otonan, juga menulis `30 wuku × 7 hari = 210 hari`.

## Sampul Pembuka

Sampul memakai foto buah hati (atau foto galeri pertama) bila ada. Bila tidak,
`frame.blade.php` merender `upacara-anak/covers/<slug>.blade.php`, yaitu
ilustrasi dari ornamen yang sama dengan halamannya, dan menyisipkannya sebagai
data URI. Warna teks dan lapisan untuk kedua keadaan ada di daftar pada awal
`frame.blade.php`.

## Feed dan Gift

Tampil di feed Moment setelah publish seperti pernikahan dan megedong; pemilik
dapat menyembunyikannya. Foto buah hati ikut tampil di feed, dan layar
konfirmasi memberi tahu hal ini sebelum publish. Gift berlabel "Tanda Kasih".

## Pengingat Upacara Berikutnya

Pemilik undangan upacara anak diingatkan saat upacara berikutnya mendekat,
lewat notifikasi di tab Notifikasi dan push (`ceremony_reminder`):

| Undangan yang pernah dipublish | Diingatkan untuk |
| --- | --- |
| Abulan pitung dina | Tigang sasih, lalu otonan pertama |
| Tigang sasih | Otonan pertama |
| Otonan | Otonan berikutnya, 210 hari setelah tanggal acaranya |

Tanggal upacara dihitung dari tanggal lahir ditambah `age_days` upacara
tujuan (105 dan 210 hari). Bila tanggal lahir kosong, tanggal lahir
diperkirakan dari tanggal acara undangan itu sendiri.

`php artisan invitations:send-ceremony-reminders` berjalan setiap hari pukul
09.00 WITA (`routes/console.php`); `--dry-run` hanya menghitung. Aturannya ada
di `App\Services\CeremonyReminderService`:

- Dua pengingat per upacara: 14 hari dan 7 hari sebelumnya, masing-masing
  sekali. Yang dipakai adalah rentang (14 sampai 8 hari, lalu 7 sampai 1
  hari), sehingga hari yang terlewat atau undangan yang baru dipublish tetap
  mendapat pengingatnya.
- Tidak dikirim bila pemilik sudah punya undangan upacara itu: bertanggal
  dekat hari upacaranya, atau draft belum bertanggal yang dibuat sesudahnya.
- Dua undangan untuk anak yang sama (42 hari dan tigang sasih) hanya
  menghasilkan satu rangkaian pengingat otonan.
- Undangan yang sudah diarsipkan tetap dihitung; draft, undangan contoh, dan
  undangan tanpa pemilik tidak.

Di aplikasi, mengetuk pengingat membuka layar pilihan jenis undangan.

## Menambah Upacara Baru

Backend:

1. `Invitation::TYPES`, `Invitation::EVENT_TYPES`, dan
   `Invitation::CHILD_CEREMONIES` (`title`, `note`, `child_named`,
   `age_days`, `numbered`, `meaning`, `emblem`).
2. Template pada `InvitationTemplateSeeder` dengan `invitation_type` baru.
3. Simbolnya: `meaning` dan `emblem` pada konfigurasi, serta gambar
   `ornaments/emblem-<nama>.blade.php`.

Desain baru: berkas `upacara-anak/<slug>.blade.php` (satu baris yang memuat
`frame`), isi di `designs/`, sampul di `covers/`, aturan `.ub--<slug>` di CSS,
dan warna sampul pada daftar di `frame.blade.php`.

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
