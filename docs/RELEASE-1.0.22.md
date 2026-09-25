# Rilis 1.0.22 (24)

Tanggal persiapan: 25 September 2026.

## Backend

- Commit `d2e7f84` sudah di-deploy ke production pada 25 September 2026.
- Migration `2026_09_25_000000_create_content_reports_and_user_blocks` hanya menambah tabel `content_reports` dan `user_blocks`.
- Backup sebelum deploy: `/var/backups/undangan-bali/release-20260925T090457Z/` (database, storage + `.env`, commit sebelumnya, jumlah data).
- Jumlah user, undangan, gift, pencairan, komentar, dan reaksi sama sebelum dan sesudah deploy.
- API tetap kompatibel dengan aplikasi 1.0.21: field baru hanya ditambahkan, tidak ada yang dihapus.

## Perubahan Untuk Pengguna

- Undangan yang sudah selesai (diarsipkan 30 hari setelah acara) tampil sebagai "Selesai" dan tetap membuka Gift & Pencairan.
- Like/Love menandai reaksi sendiri; ketuk lagi untuk membatalkan.
- Komentar lama dapat dimuat dengan "Lihat komentar sebelumnya".
- Penulis komentar dan pemilik undangan dapat menghapus komentar.
- Laporkan Moment atau komentar, blokir pengguna, dan kelola blokir di Profil > Pengguna Diblokir.
- Undangan Saya memuat semua undangan, bukan hanya 15 terbaru.

Teks "Apa yang baru" untuk Play Console:

```text
- Laporkan Moment atau komentar yang tidak pantas, dan blokir pengguna
- Hapus komentar di Moment Anda
- Tanda Like/Love milik Anda, ketuk lagi untuk membatalkan
- Lihat semua komentar lama
- Gift & pencairan tetap bisa dibuka setelah acara selesai
```

## Moderasi

Admin meninjau laporan di `https://undangan.balisantih.com/admin/reports`. Jumlah laporan terbuka tampil di menu admin. Tindakan: sembunyikan komentar, sembunyikan Moment dari feed, atau abaikan. Laporan terbuka lain untuk konten yang sama ikut ditutup.

Belum ada notifikasi email ke admin saat laporan masuk; periksa menu Laporan secara berkala.

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.22`; version code: `24`.
- Build lokal `:app:bundleRelease` dengan upload key `credentials/android/undangan-bali-upload-keystore.jks`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.22-v24.aab`.
- Ukuran: 55.266.755 byte.
- SHA-256: `26150FB5832288F65C29DE640D385984BC3BD573D65F9EA8D6A3F3C7B938E1C6`.
- Sertifikat upload sama dengan 1.0.17 (SHA-256 `33:C8:4A:68:…:DB:3F:3C`); `jarsigner -verify` lulus.
- Bundle JS berisi layar baru (Pengguna Diblokir, Laporkan Moment, kartu undangan Selesai) dan endpoint production.
- Endpoint release: `https://undangan.balisantih.com/api`.
- Belum diunggah ke Play Console dan belum dicoba di perangkat fisik.

## Keamanan Data Play Console

Laporan menyimpan alasan dan catatan opsional dari pelapor; blokir menyimpan pasangan akun. Keduanya termasuk kategori "konten pengguna" dan "aktivitas/interaksi" yang sudah dideklarasikan. Periksa kembali formulir Keamanan Data sebelum mengirim untuk ditinjau.

## Verifikasi

- Laravel: 101 tes, 1124 assertion lulus. Pint lulus.
- Mobile: 31 tes lulus; `expo export` Android dan web berhasil.
