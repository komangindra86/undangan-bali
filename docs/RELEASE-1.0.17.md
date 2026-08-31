# Rilis 1.0.17 (19)

Tanggal persiapan: 31 Agustus 2026.

## Backend

- Perubahan aplikasi sampai commit `9e8369f` sudah di-deploy ke production.
- Migration ulang tahun dan InvitationTemplateSeeder sudah dijalankan. Tidak menjalankan ulang seeder akun admin.
- Backup database, storage publik, dan konfigurasi dibuat sebelum migration dan diperiksa integritasnya di VPS.
- Jumlah akun, undangan lama, gift, dan fee tidak berubah selama deploy.
- Katalog menyediakan tiga template ulang tahun dan mempertahankan lima template pernikahan untuk klien lama.
- Ketiga preview ulang tahun, halaman utama, login admin, kebijakan privasi, API musik/feed, dan sampel undangan lama mengembalikan HTTP 200.

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.17`; version code: `19`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.17-v19.aab`.
- Ukuran: 55.252.257 byte.
- SHA-256: `88BC8D201933CD453385DE0E02EB95BFE5BC95A50F1F030AF75D72B37C3BAAAF`.
- Build lokal memakai upload key yang cocok dengan sertifikat upload Play Console. Jangan memakai kredensial signing EAS lama tanpa memeriksa kecocokan sertifikat.
- Target SDK 36, minimum SDK 24. Endpoint release: `https://undangan.balisantih.com/api`.
- Build 18 digantikan sebelum dikirim untuk review, untuk menambahkan menu kebijakan privasi dan permintaan penghapusan akun pada Profil.

## Verifikasi

- Laravel: 70 tes, 630 assertion lulus.
- Mobile: 12 tes alur ulang tahun lulus.
- Expo Doctor: 20 pemeriksaan lulus sebelum perubahan tautan Profil.
- Gradle `:app:bundleRelease`: berhasil; tanda tangan AAB terverifikasi.
- Peringatan mapping deobfuscation bukan penghalang upload; R8 tidak diaktifkan pada rilis ini.

## Keamanan Data Dan Penanganan Permintaan

Deklarasi Play Console sebelumnya menyatakan tidak mengumpulkan data. Draf koreksi kini mencakup nama, email, ID akun, alamat acara, nomor telepon permintaan, usia opsional, rekening pencairan, data gift, foto, musik, aktivitas/interaksi, konten pengguna, dan ID perangkat. Nama, foto, dan konten yang masuk feed juga diungkapkan sebagai dibagikan. Tidak ada deklarasi penggunaan data untuk iklan.

ID perangkat tidak dinyatakan sepenuhnya opsional karena Firebase Cloud Messaging dapat melakukan registrasi SDK otomatis, meskipun izin menampilkan notifikasi tetap opsional.

Halaman permintaan: `https://undangan.balisantih.com/privacy-policy#penghapusan-akun`.

Jalur permintaan penghapusan menggunakan email dukungan yang sudah tercantum pada kebijakan privasi. Ini bukan implementasi penghapusan otomatis. Pengelola perlu mengonfirmasi bahwa `admin.balisantih@gmail.com` dipantau, batas penyelesaian permintaan, dan periode retensi tambahan untuk cadangan/catatan transaksi sebelum deklarasi final dikirim ke Google. Jangan mencantumkan tenggat atau periode hukum yang belum ditetapkan.

Permintaan harus diverifikasi kepemilikannya sebelum menghapus apa pun. Penghapusan akun harus mencakup data terkait dan media, bukan sekadar menonaktifkan login. Catatan transaksi atau pencairan yang masih diperlukan perlu ditangani secara terpisah; jangan menjalankan cascade delete terhadap data keuangan tanpa pemeriksaan.

Status persiapan ini tidak berarti rilis sudah disetujui atau tersedia di Play Store. Periksa Ringkasan publikasi sebelum mengirim perubahan untuk ditinjau.
