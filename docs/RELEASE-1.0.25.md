# Rilis 1.0.25 (27)

Tanggal persiapan: 12 Oktober 2026. Menggantikan 1.0.24 (26) yang ada di production.

## Backend

- Commit `9e533c5` di-deploy ke production pada 12 Oktober 2026.
- Migration `2026_10_12_000000_add_ceremony_number_to_invitations` menambah kolom `ceremony_number` pada `invitations`.
- InvitationTemplateSeeder menambah enam template tigang sasih dan sembilan template otonan; jumlah template 14 menjadi 29.
- Perintah terjadwal baru `invitations:send-ceremony-reminders`, setiap hari pukul 09.00 WITA.
- Backup sebelum deploy: `/var/backups/undangan-bali/release-20261012T043425Z/`.
- Jumlah user, undangan, gift, dan komentar sama sebelum dan sesudah deploy.
- Saat deploy belum ada undangan abulan pitung dina yang tayang, jadi perombakan desain tidak mengubah undangan siapa pun.

## Perubahan Untuk Pengguna Sejak 1.0.24

- Undangan upacara Tigang Sasih (3 Bulanan) dengan enam template; nama buah hati menjadi judul undangan.
- Undangan Otonan dengan sembilan template, untuk otonan pertama maupun berikutnya, dengan isian opsional "Otonan ke-".
- Kesembilan desain upacara anak dirombak: tiap desain punya tata letak dan ornamen Bali sendiri, serta simbol dan arti upacaranya. Lihat [`UPACARA-ANAK.md`](UPACARA-ANAK.md).
- Pengingat upacara berikutnya (14 dan 7 hari sebelumnya) untuk pemilik undangan 42 hari, tigang sasih, dan otonan.

Teks "Apa yang baru" untuk Play Console:

```text
- Baru: undangan upacara Tigang Sasih (3 bulanan)
- Baru: undangan Otonan
- Desain baru bernuansa Bali untuk undangan upacara anak
- Pengingat saat upacara berikutnya sudah dekat
```

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.25`; version code: `27`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.25-v27.aab`.
- Ukuran: 55.276.236 byte.
- SHA-256: `30E5D66199CA474CA13D2F5544E9B4601C70A4EF2974E470D105B259E884A68F`.
- Sertifikat upload sama dengan rilis sebelumnya; `jarsigner -verify` lulus.
- 12 Oktober 2026: diunggah ke jalur **internal testing** lewat `eas submit --profile internal` (status COMPLETED). Production tetap `26 (1.0.24)` sampai pemilik meminta promosi.

## Verifikasi

- Laravel: 135 tes, 2056 assertion lulus. Pint lulus.
- Mobile: 51 tes lulus; `expo export` Android berhasil.
- Sembilan desain diaudit di browser pada lebar 320 sampai 1366 px dengan data lengkap, tanpa nama anak, dan data minimal: tidak ada teks terpotong, gulir menyamping, atau gambar rusak.
- Alur otonan dijalankan di aplikasi web terhadap backend lokal sampai layar konfirmasi.
- Production setelah deploy: API template kelima jenis, lima belas preview template, CSS baru, dan undangan lama mengembalikan HTTP 200; penjadwal memuat perintah pengingat; tidak ada error baru di log.

Belum diuji: publish dari aplikasi di perangkat nyata, pengiriman push pengingat ke ponsel, dan tampilan di ponsel sungguhan. Simbol serta kalimat arti upacara masih menunggu pemeriksaan adat oleh pemilik.
