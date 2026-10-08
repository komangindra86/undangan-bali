# Rilis 1.0.23 (25)

Tanggal persiapan: 8 Oktober 2026.

## Backend

- Commit `6cbc6c6` di-deploy ke production pada 8 Oktober 2026.
- Migration `2026_10_08_000000_add_megedong_invitations` menambah kolom `pregnancy_age` dan `child_order` pada `invitations`.
- InvitationTemplateSeeder menambah tiga template megedong-gedongan; jumlah template 8 menjadi 11.
- Backup sebelum deploy: `/var/backups/undangan-bali/release-20261008T070136Z/`.
- Jumlah user, undangan, gift, pencairan, dan komentar sama sebelum dan sesudah deploy.
- API tetap kompatibel dengan aplikasi lama: `GET /api/templates` tanpa parameter tetap hanya template pernikahan.
- `event_date` kini dikirim sebagai `Y-m-d`. Sebelumnya dikirim sebagai waktu UTC sehingga tanggal mundur sehari di aplikasi.

## Perubahan Untuk Pengguna

- Jenis undangan baru: Megedong-gedongan, dengan tiga template (Garbha Kencana, Padma Sari, Tirta Hening). Lihat [`UNDANGAN-MEGEDONG.md`](UNDANGAN-MEGEDONG.md).
- Tanggal acara pada kartu Undangan Saya dan pada draft yang dilanjutkan tidak lagi mundur sehari.

Teks "Apa yang baru" untuk Play Console:

```text
- Baru: undangan upacara Megedong-gedongan dengan 3 pilihan template
- Perbaikan tanggal acara yang tampil mundur sehari
```

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.23`; version code: `25`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.23-v25.aab`.
- Ukuran: 55.270.316 byte.
- SHA-256: `F41FFA0A92576B1F243E7951045080A4DB5C6C6F0EE87D7983A779C761A6F6D2`.
- Sertifikat upload sama dengan rilis sebelumnya; `jarsigner -verify` lulus.
- 8 Oktober 2026: diunggah ke jalur **internal testing** lewat `eas submit --profile internal`. Belum dipromosikan ke production dan belum dicoba di perangkat fisik.

## Verifikasi

- Laravel: 107 tes, 1253 assertion lulus. Pint lulus.
- Mobile: 37 tes lulus; `expo export` Android dan web berhasil.
- Alur megedong-gedongan dijalankan penuh di aplikasi web terhadap backend lokal: pilih jenis, template, form, acara, lokasi, lanjutkan draft setelah muat ulang, publish, halaman publik, dan kartu Undangan Saya.
- Production setelah deploy: halaman utama, API template ketiga jenis, feed, tiga preview megedong, dan undangan lama mengembalikan HTTP 200.

## Catatan Operasional

- Klien SSH Git Bash (OpenSSH 8.8) ditolak server dengan "Connection reset". Gunakan `C:\Windows\System32\OpenSSH\ssh.exe` (9.5).
- 8 Oktober 2026: kunci `XENDIT_SECRET_KEY` production ditolak Xendit (`INVALID_API_KEY`). Satu percobaan gift gagal pukul 14:15 WITA. Pembayaran gift tidak dapat dibuat sampai kunci diganti di `.env` server. Ini tidak terkait deploy 1.0.23.
