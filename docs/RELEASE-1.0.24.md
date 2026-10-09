# Rilis 1.0.24 (26)

Tanggal persiapan: 9 Oktober 2026. Menggantikan 1.0.23 (25) yang hanya sampai internal testing.

## Backend

- Commit `2596a40` di-deploy ke production pada 9 Oktober 2026.
- Migration `2026_10_09_000000_add_child_ceremony_invitations` menambah kolom `child_full_name`, `child_nickname`, `child_gender`, `child_birth_date`, dan `child_photo` pada `invitations`.
- InvitationTemplateSeeder menambah tiga template abulan pitung dina; jumlah template 11 menjadi 14.
- Backup sebelum deploy: `/var/backups/undangan-bali/release-20261009T014352Z/`.
- Jumlah user, undangan, gift, pencairan, dan komentar sama sebelum dan sesudah deploy.
- Sejak 9 Oktober 2026 gift diproses lewat iPaymu QRIS (lihat README, bagian iPaymu QRIS). Xendit tidak dipakai lagi.

## Perubahan Untuk Pengguna Sejak 1.0.22

- Undangan upacara Megedong-gedongan dengan tiga template. Lihat [`UNDANGAN-MEGEDONG.md`](UNDANGAN-MEGEDONG.md).
- Undangan upacara Abulan Pitung Dina (42 Hari) dengan tiga template. Lihat [`UPACARA-ANAK.md`](UPACARA-ANAK.md).
- Tanggal acara pada kartu Undangan Saya dan pada draft yang dilanjutkan tidak lagi mundur sehari.

Teks "Apa yang baru" untuk Play Console:

```text
- Baru: undangan upacara Megedong-gedongan
- Baru: undangan upacara Abulan Pitung Dina (42 hari)
- Perbaikan tanggal acara yang tampil mundur sehari
```

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.24`; version code: `26`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.24-v26.aab`.
- Ukuran: 55.274.330 byte.
- SHA-256: `83567FE2D236F0E4C3178299E495F45AEC37D9702AE999F50AF8FECD8D0E8A00`.
- Sertifikat upload sama dengan rilis sebelumnya; `jarsigner -verify` lulus.
- 9 Oktober 2026: diunggah ke jalur **internal testing** lewat `eas submit --profile internal`; Google Play Developer API mengonfirmasi jalur internal berisi kode 26 dan production tetap `24 (1.0.22)`.
- 9 Oktober 2026: atas permintaan pemilik, kode 26 dipromosikan ke **production** (rollout penuh) lewat Google Play Developer API dengan teks "Apa yang baru" di atas, bahasa `id`. Jalur production kini berisi `26 (1.0.24)` menggantikan `24 (1.0.22)`. Status peninjauan Google hanya terlihat di Play Console.

## Verifikasi

- Laravel: 120 tes, 1463 assertion lulus. Pint lulus.
- Mobile: 44 tes lulus; `expo export` Android dan web berhasil.
- Alur abulan pitung dina dijalankan penuh di aplikasi web terhadap backend lokal dan diperiksa pemilik di laptop.
- Production setelah deploy: halaman utama, API template keempat jenis, feed, enam preview upacara, halaman gift, dan undangan lama mengembalikan HTTP 200; tidak ada error baru di log.
