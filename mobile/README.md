# Undangan Pernikahan Bali - Mobile

Aplikasi mobile MVP berbasis React Native Expo SDK 55. User dapat menyusun draft hingga halaman konfirmasi data tanpa login. Login atau register baru dibutuhkan ketika menekan **Publish & Lihat Hasil**.

Pada beranda, user baru tetap diarahkan untuk mulai tanpa login. User yang pernah memiliki akun mendapatkan akses **Masuk untuk Lihat Undangan Saya**, dan sesi yang kedaluwarsa akan meminta login ulang tanpa membuang draft lokal.

## Screen

```text
SplashScreen
LandingScreen
TemplateScreen
TemplatePreviewScreen
GroomBrideFormScreen
EventFormScreen
LocationScreen
GalleryScreen
MusicScreen
GiftSetupScreen
PreviewScreen
AuthGateScreen
LoginScreen
RegisterScreen
ShareScreen
MyInvitationsScreen
WeddingGiftSettingScreen
WeddingGiftDashboardScreen
PayoutAccountScreen
RequestPayoutScreen
PayoutHistoryScreen
```

## Alur Draft

Setiap langkah wizard menyimpan data ke AsyncStorage:

```text
selected_template
groom_data
bride_data
event_data
location_data
gallery_data
music_data
gift_data
```

Jika user belum login, data hanya berada di perangkat sampai user menekan publish. Setelah login/register, draft dikirim ke `POST /api/invitations/sync-local-draft`, lalu dipublish melalui `POST /api/invitations/{id}/publish`.

Jika token user sudah tersimpan sejak awal, setiap tombol **Lanjut** juga menyinkronkan draft ke backend secara otomatis.

Pada langkah template tersedia tiga pilihan bernuansa Bali. Tombol **Lihat Preview** membuka contoh data dummy lebih dahulu; dari sana user dapat melihat undangan demo lengkap berfoto, bergaleri, dan beranimasi sebelum memilih template.

Foto mempelai dapat diunggah pada langkah data mempelai. Galeri menerima maksimal 6 foto pada langkah tersendiri setelah lokasi. Gambar diperkecil dan dikompresi sebelum diunggah agar draft dan halaman publik tetap ringan; foto contoh template tidak dipakai pada undangan yang dipublish.

Pada langkah acara, user memilih tanggal melalui kalender dan jam melalui pemilih waktu, sehingga tidak perlu mengetik format tanggal/jam secara manual.

Langkah musik menyediakan tombol **Play/Pause** untuk mendengarkan cuplikan instrumental bawaan maupun file milik user sebelum memilih lagu. Upload musik menerima MP3, WAV, atau M4A maksimal 10 MB agar halaman undangan tetap ringan.

Sesudah memilih musik, langkah **Wedding Gift** bersifat opsional dan tetap dapat diisi sebelum login. Pilihan tersebut disimpan pada draft lokal dan ikut dikirim saat user publish. Halaman **Undangan Saya** tetap menyediakan pengaturan lanjutan dan dashboard transaksi setelah undangan dipublish. Mobile hanya mengaktifkan fitur, menentukan nama penerima/minimum gift, dan melihat transaksi yang telah dikonfirmasi backend. Tidak ada tombol pembayaran atau QRIS di mobile; tamu membayar melalui halaman web undangan.

Dashboard Gift juga menampilkan saldo tersedia, saldo sedang diproses, dan saldo yang sudah dicairkan. Pasangan dapat menyimpan rekening serta mengirim permintaan pencairan; proses transfer dilakukan admin secara manual, bukan melalui tombol pembayaran di mobile.

## Struktur

```text
src/
  components/       # Tombol, field input, layout wizard/progress
  context/          # AuthProvider dan DraftProvider
  navigation/       # Native stack navigation
  screens/          # Seluruh screen utama
  services/api.js   # Semua panggilan REST API
  services/draftStorage.js
  theme.js
```

## Menjalankan

Backend perlu berjalan dan dapat dijangkau perangkat mobile. Buat environment:

Untuk mencoba paling mudah melalui browser laptop, cukup klik dua kali file `JALANKAN-DI-LAPTOP.bat` yang ada di folder utama project.

Untuk menjalankan secara manual atau memakai HP, lanjutkan panduan berikut.

```powershell
cd C:\laragon\www\undangan-bali\mobile
Copy-Item .env.example .env
```

Gunakan URL sesuai target:

```text
Production/Play Store: EXPO_PUBLIC_API_URL=https://undangan.balisantih.com/api
Android emulator: EXPO_PUBLIC_API_URL=http://10.0.2.2:8000/api
iOS simulator:     EXPO_PUBLIC_API_URL=http://127.0.0.1:8000/api
Perangkat fisik:   EXPO_PUBLIC_API_URL=http://ALAMAT-IP-LAN-PC:8000/api
```

Untuk perangkat fisik, atur juga `APP_URL` backend ke alamat LAN yang sama agar link public hasil publish dapat dibuka dari ponsel.
Jalankan backend agar dapat dijangkau perangkat pada jaringan yang sama:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Jalankan:

```powershell
npm install
npx expo start
```

## Build Android Play Store

Backend production sudah diarahkan ke:

```text
https://undangan.balisantih.com/api
```

Build AAB untuk Play Store:

```powershell
cd C:\laragon\www\undangan-bali\mobile
npm install
npx eas login
npx eas build --platform android --profile production
```

File `.aab` dari EAS bisa diunggah ke Google Play Console. Wedding Gift tetap aman untuk Play Store karena pembayaran QRIS dilakukan oleh tamu di halaman web undangan, bukan di dalam aplikasi mobile.

## Batas MVP

- Upload foto mempelai, galeri, dan musik sendiri sudah tersedia dengan batas ukuran untuk menjaga undangan tetap ringan.
- Link Google Maps dan koordinat pin disimpan; pemilih peta visual belum termasuk MVP ini.
