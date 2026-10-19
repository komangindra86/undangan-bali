# Rilis 1.0.28 (30)

Tanggal persiapan: 19 Oktober 2026. Menggantikan 1.0.27 (29) yang ada di production.

## Backend

- Commit `b4a71b7` di-deploy ke production pada 19 Oktober 2026.
- Migration `2026_10_19_000000_record_custom_order_transfers` membuat `payout_account_id` dan `bank_code` boleh kosong dan menambah `proof_path` pada `gift_payout_requests`.
- Backup sebelum deploy: `/var/backups/undangan-bali/release-20261019T021325Z/`.
- Biaya pencairan dihitung per tanda kasih menurut tanggal lunasnya. Tarif baru 10% **belum aktif**: `WEDDING_GIFT_PAYOUT_FEE_NEW_FROM` di `.env` production masih kosong, jadi semua tanda kasih tetap 1%.
- Endpoint baru: `GET /api/gift-fee` (publik) dan `GET /api/invitations/{id}/payout-quote?amount=` (pemilik undangan).
- Landing page, Syarat Layanan, Kebijakan Privasi, dan halaman admin mengambil kalimat biaya dari pengaturan yang sama.
- Pesanan Custom: admin mencatat transfer ke pelanggan beserta foto bukti; pelanggan melihat riwayatnya di halaman pantau.

## Perubahan Untuk Pengguna Sejak 1.0.27

- Teks biaya platform di layar pengaturan tanda kasih, dashboard, dan riwayat pencairan diambil dari server, tidak lagi tertulis "1%" di aplikasi.
- Layar pengajuan pencairan menampilkan rincian biaya yang dihitung server sebelum pengajuan dikirim.

Teks "Apa yang baru" untuk Play Console:

```text
- Rincian biaya pencairan dihitung langsung sebelum Anda mengajukan
- Informasi biaya platform di aplikasi selalu mengikuti ketentuan terbaru
```

## Mengaktifkan Biaya 10%

Hanya setelah 1.0.28 ada di production dan pemilik menentukan tanggalnya:

1. Isi `WEDDING_GIFT_PAYOUT_FEE_NEW_FROM="YYYY-MM-DD HH:MM:SS"` (WITA) di `.env` production.
2. Jalankan `php8.2 artisan config:cache`.
3. Periksa `GET /api/gift-fee` dan halaman Syarat Layanan menyebut 10% beserta tanggalnya.

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.28`; version code: `30`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.28-v30.aab`.
- Ukuran: 55.283.225 byte.
- SHA-256: `02C677011ECE7A08C0DEB8FBAF5099EF393CC0FDA28BCC5E9BB1B09AB8465038`.
- Sertifikat upload sama dengan rilis sebelumnya (`33:C8:4A:68...`); `jarsigner -verify` lulus.
- 19 Oktober 2026: diunggah ke jalur **internal testing** lewat `eas submit --profile internal` (status selesai). Production tetap `29 (1.0.27)` sampai pemilik meminta promosi.

## Verifikasi

- Laravel: 167 tes lulus. Pint lulus.
- Mobile: kedelapan berkas tes lulus, termasuk pemeriksaan bahwa layar biaya tidak lagi menulis persentase tetap.
- Bundle berisi versi 1.0.28, alamat API production, dan pemanggilan `/gift-fee` serta `payout-quote`; teks "fee 1%" sudah tidak ada.
- Production setelah deploy: `/api/gift-fee` menyebut 1% tanpa tanggal tarif baru, Syarat Layanan menyebut 1%, `payout-quote` menolak tanpa login (401), tidak ada error baru di log.
- Halaman pantau dengan riwayat transfer diperiksa di browser lokal pada lebar 375 dan 320 px.

Belum diuji: formulir catat transfer lewat login admin sungguhan, dan layar pengajuan pencairan di ponsel sungguhan.
