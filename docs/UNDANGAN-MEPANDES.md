# Undangan Mepandes (Potong Gigi)

Jenis undangan `mepandes` untuk upacara Manusa Yadnya potong gigi. Berbeda dari
upacara bayi dan anak, mepandes diadakan untuk satu orang atau beberapa orang
sekaligus, dari satu keluarga maupun beberapa keluarga.

| Hal | Nilai |
| --- | --- |
| Nama tampil | Mepandes (Potong Gigi) |
| Jenis acara | `Mepandes` |
| Template | Bale Gading, Sekar Emas, Wastra Songket |
| Feed Moment | Tampil setelah publish; pemilik dapat menyembunyikannya |
| Gift | Tanda Kasih |

## Data

Migration `2026_10_13_000000_add_mepandes_invitations` menambah dua kolom:

- `family_mode`: `single` (satu keluarga) atau `multi` (beberapa keluarga).
- `participants`: daftar JSON yang mepandes, paling banyak
  `Invitation::MAX_PARTICIPANTS` (10). Tiap peserta berisi `full_name`,
  `nickname`, `gender` (`putra` / `putri`, opsional), `age` (opsional),
  `photo` (opsional), serta `father_name` dan `mother_name` untuk beberapa
  keluarga.

| | Satu keluarga | Beberapa keluarga |
| --- | --- | --- |
| Orang tua | Sekali, di `groom_*` (ayah) dan `bride_*` (ibu) | Per peserta: `father_name`, `mother_name` |
| Pengundang | Nama panggilan ayah dan ibu | `host_name`, mis. "Keluarga Besar I Made Wira" |
| Wajib untuk publish | Nama ayah dan ibu; nama lengkap dan panggilan tiap peserta | `host_name`; nama lengkap, panggilan, ayah, dan ibu tiap peserta |

Judul undangan memakai nama panggilan peserta: `Kirana`, `Kirana & Bagus`,
`Kirana, Bagus & Dewi`, lalu `Kirana, Bagus & 3 lainnya`. Di bawahnya tertulis
`Putra-putri dari Wira & Ayu` (atau `Putra`, `Putri`, `Ananda`) untuk satu
keluarga, dan nama pengundang untuk beberapa keluarga. Slug:
`mepandes-kirana-bagus-dewi`.

## Foto Peserta

Aplikasi mengirim data peserta di `mepandes_data[participants][i][...]`. Foto
hanya dikirim bila ada yang berubah, ditandai `participant_photos_changed=1`:

- foto baru diunggah sebagai `participant_photos[i]`;
- foto yang sudah ada dipertahankan dengan menyebut jalurnya di
  `participants[i][photo_path]` (hanya jalur milik undangan itu yang diterima);
- foto yang tidak lagi dipakai dihapus dari penyimpanan.

Tanpa tanda itu, setiap peserta mempertahankan foto pada urutan yang sama.
Aplikasi menghitung penambahan atau penghapusan peserta sebagai perubahan
media, sehingga urutannya tidak bergeser. Foto peserta ikut terhapus saat
undangan dihapus atau saat media undangan arsip dibersihkan.

## Halaman

Ketiga desain memakai kerangka yang sama dengan upacara anak
(`upacara-anak/frame.blade.php`, lihat [`UPACARA-ANAK.md`](UPACARA-ANAK.md)),
dengan isi di `upacara-anak/designs/` dan gaya di `public/css/upacara-bali.css`.
Daftar peserta digambar oleh `parts/participants.blade.php`; peserta tanpa foto
ditampilkan dengan huruf awal namanya.

| Desain | Tata letak | Ornamen |
| --- | --- | --- |
| Bale Gading | Simetris, gading dan emas; peserta dalam kartu berfoto bundar | Bale tempat upacara: atap alang-alang, ider-ider prada, langse, bantal |
| Sekar Emas | Sampanye dan perunggu; peserta dalam kartu lengkung dua kolom | Mahkota bunga emas payas agung dengan bunga jepun |
| Wastra Songket | Rata kiri, ungu dan emas; peserta dalam baris berfoto persegi | Kain songket di atas dan bawah halaman |

Simbol upacara (`Invitation::MEPANDES`): bunga berkelopak enam dan kalimat
"Upacara potong gigi sebagai tanda beranjak dewasa, lambang mengendalikan enam
musuh dalam diri (sad ripu)."

Foto peserta pertama yang berfoto menjadi sampul pembuka; bila tidak ada,
dipakai foto galeri pertama, lalu ilustrasi desain.

## Aplikasi

- `mobile/src/screens/MepandesFormScreen.js`: pilihan satu atau beberapa
  keluarga, lalu daftar peserta yang bisa ditambah dan dihapus. Foto tiap
  peserta opsional.
- `mobile/src/constants/invitation.js`: entri `mepandes` pada `TYPE_INFO`,
  `participantNamesFor`, dan `MAX_PARTICIPANTS`.
- Draft menyimpan `mepandes_data` (`family_mode`, `host_name`, `participants`).

Mepandes tidak memicu pengingat upacara, karena waktunya tidak ditentukan oleh
umur.

## Verifikasi

```sh
php artisan test --compact --filter=MepandesInvitationTest
cd mobile
node tests/mepandes-flow.test.cjs
```
