# Rilis 1.0.27 (29)

Tanggal persiapan: 16 Oktober 2026. Menggantikan 1.0.26 (28) yang ada di production.

## Backend

Tidak ada perubahan backend dalam rilis ini. Yang sudah live sebelumnya (commit `03cd454`, deploy 16 Oktober 2026):

- Tiga template pernikahan gratis baru: Jepun Kirana, Carik Asri, dan Ukir Kencana. Katalog pernikahan di aplikasi berisi 8 template gratis.
- Bagian "Kisah kami" hanya ada di template premium; template gratis tidak menampilkannya.
- Template premium (Wiwaha Agung, Taman Tirta, Lamak Prada) hanya dipakai lewat menu Pesanan Custom di admin dan tidak muncul di aplikasi.

## Perubahan Untuk Pengguna Sejak 1.0.26

- Deskripsi singkat untuk tiga template pernikahan gratis yang baru di layar pilih template. Templatenya sendiri sudah tampil sejak deploy backend.

Teks "Apa yang baru" untuk Play Console:

```text
- Tiga desain undangan pernikahan gratis baru: Jepun Kirana, Carik Asri, dan Ukir Kencana
- Desain bernuansa Bali dengan animasi yang lebih hidup
```

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.27`; version code: `29`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.27-v29.aab`.
- Ukuran: 55.282.692 byte.
- SHA-256: `8ED46025481C440DB87D34D47C9E1C1A7554F36EE116A1701667B54C74D1F829`.
- Sertifikat upload sama dengan rilis sebelumnya (`33:C8:4A:68...`); `jarsigner -verify` lulus.
- 16 Oktober 2026: diunggah ke jalur **internal testing** lewat `eas submit --profile internal` (status selesai). Production tetap `28 (1.0.26)` sampai pemilik meminta promosi.

## Verifikasi

- Laravel: 162 tes lulus. Pint lulus.
- Mobile: kedelapan berkas tes lulus.
- Bundle berisi versi 1.0.27, alamat API production, dan teks deskripsi ketiga template baru.

Belum diuji: tampilan layar pilih template di ponsel sungguhan.
