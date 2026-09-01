# Katalog musik Pixabay

## Perilaku aplikasi

- Langkah musik menampilkan dua kategori: Pernikahan dan Ulang Tahun. Kategori awal mengikuti jenis undangan.
- Tidak ada kolom pencarian. Enam kartu ditampilkan lebih dahulu, kemudian pengguna dapat menampilkan enam berikutnya.
- API hanya mengirim metadata. Preview 30 detik baru dimuat saat pengguna menekan Putar.
- File musik tidak dimasukkan ke AAB dan tidak disalin ke setiap undangan.
- Undangan publik memakai lagu penuh dari backend dengan `preload="none"`, sehingga browser tidak mengunduh audio sebelum undangan dibuka.
- Musik katalog lama dinonaktifkan dari pemilih, tetapi baris dan filenya tidak dihapus agar undangan live lama tetap dapat diputar.
- Upload musik sendiri tetap tersedia. Pengguna wajib mengonfirmasi bahwa ia memiliki hak atau izin sebelum melanjutkan.

## Sumber dan lisensi

Katalog berisi 20 file yang diunduh manual dari Pixabay dan dicatat di
`resources/music/pixabay-catalog.json`. Setiap entri menyimpan ID aset, nama file sumber, pencipta,
kategori, durasi, serta halaman pencarian sumber. Penggunaannya mengacu pada
[Pixabay Content License](https://pixabay.com/service/license-summary/).

Kredit sukarela berupa judul, pencipta, dan Content ID ditampilkan pada pemilih musik dan undangan publik.
Audio tidak boleh dijual atau dibagikan sebagai berkas mandiri. Simpan file unduhan asli dan, jika tersedia,
sertifikat unduhan dari akun Pixabay sebagai arsip internal. Lisensi Pixabay tidak menghapus kewajiban untuk
memeriksa hak pihak ketiga atau klaim Content ID pada setiap aset.

Folder lokal `musik pixbay/` berisi master 256 kbps dan sengaja diabaikan Git. Backend memakai:

- `storage/app/public/musics/pixabay/{asset_id}.mp3`: lagu penuh 96 kbps.
- `storage/app/public/musics/pixabay/previews/{asset_id}.mp3`: preview 30 detik, 80 kbps, fade-out.
- `storage/app/private/music-licenses/pixabay/{asset_id}/evidence.json`: hash audio, identitas sumber, dan rekaman peninjauan.

Hasil kompresi berjumlah 40 file sekitar 34 MiB, turun lebih dari 50% dari master, dan seluruhnya berada di backend.

## Deploy backend

```bash
php8.2 artisan migrate --force
php8.2 artisan db:seed --class='Database\Seeders\PixabayMusicSeeder' --force
php8.2 artisan storage:link --force
php8.2 artisan optimize:clear
php8.2 artisan optimize
```

Seeder memeriksa keberadaan serta ukuran audio, menulis bukti hash privat, dan mempertahankan lagu yang
dinonaktifkan admin. Kegagalan satu file membuat deploy gagal agar katalog tidak aktif setengah jadi.

## API

```http
GET /api/musics
Accept: application/json
```

Respons tetap publik dan hanya berisi lagu aktif yang memiliki metadata lisensi, kredit, serta preview.
`license_evidence_path`, `audio_sha256`, dan `preview_sha256` tidak pernah dikirim ke mobile.

## Validasi

```bash
php8.2 artisan test
php8.2 vendor/bin/pint --test
cd mobile
npm test --if-present
node tests/birthday-flow.test.cjs
node tests/music-catalog.test.cjs
npx expo-doctor
npx expo export --platform android --output-dir dist-music-pixabay-check
```

Selain tes otomatis, uji pada perangkat: kategori awal, putar/jeda preview, pindah kategori ketika audio aktif,
background aplikasi, jaringan putus, pulihkan draft, konfirmasi upload pribadi, publish, dan pemutaran musik pada
setiap template undangan.
