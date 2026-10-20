# Rilis 1.0.29 (31)

Tanggal persiapan: 20 Oktober 2026. Menggantikan 1.0.28 (30) yang ada di production.

## Backend

- Commit `51f2f49` di-deploy ke production pada 20 Oktober 2026.
- Migration `2026_10_20_000000_add_account_and_invitation_deletion` menambah `users.account_deleted_at`, `invitations.owner_deleted_at`, dan tabel `account_deletion_requests`.
- Backup sebelum deploy: `/var/backups/undangan-bali/release-20261020T051454Z/`.
- `POST /api/register` dan `POST /api/login` menjawab 410 dengan pesan untuk memakai tombol Masuk dengan Google. Login admin web tidak berubah.
- Endpoint baru: `GET /api/account/deletion` (ringkasan) dan `DELETE /api/account` (wajib `confirm: HAPUS`; `forfeit_balance` wajib bila masih ada saldo).
- `DELETE /api/invitations/{id}` kini berlaku untuk undangan live: tanpa transaksi tanda kasih dihapus total; dengan transaksi disembunyikan (media, link, dan aktivitas sosial dihapus, catatan tanda kasih dan pencairan disimpan); ditolak bila masih ada saldo atau pencairan yang belum selesai.
- Halaman web `/hapus-akun` dan menu admin **Pengguna** (cari akun, ganti email, hapus akun, permintaan hapus akun). Kebijakan Privasi diperbarui.
- Akun yang dihapus dianonimkan, bukan dihapus barisnya, supaya catatan pencairan tetap utuh.

## Perubahan Untuk Pengguna Sejak 1.0.28

- Masuk dan daftar hanya dengan akun Google; formulir email dan password serta layar Daftar dihapus.
- Profil → Hapus Akun, dengan ringkasan data yang dihapus, peringatan saldo yang hangus, dan konfirmasi ketik HAPUS.
- Tombol Hapus Undangan di setiap kartu Undangan Saya, termasuk undangan yang sudah live.

Teks "Apa yang baru" untuk Play Console:

```text
- Masuk dan daftar kini cukup dengan akun Google
- Hapus akun langsung dari menu Profil
- Undangan, termasuk yang sudah live, kini bisa dihapus dari Undangan Saya
```

## Isian Play Console Yang Perlu Diubah Pemilik

- Keamanan data → penghapusan data: `https://undangan.balisantih.com/hapus-akun`.
- Akses aplikasi: login hanya dengan Google; hapus keterangan akun uji email dan password bila masih ada.

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.29`; version code: `31`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.29-v31.aab`.
- Ukuran: 55.284.315 byte.
- SHA-256: `5C78E7EBC0AA6B7AB69E3D2733319140B132BAEBD38B325E6C9E6C0666C7BF5B`.
- Sertifikat upload sama dengan rilis sebelumnya (`33:C8:4A:68...`); `jarsigner -verify` lulus.
- 20 Oktober 2026: diunggah ke jalur **internal testing** lewat `eas submit --profile internal` (status selesai). Production tetap `30 (1.0.28)` sampai pemilik meminta promosi.
- 20 Oktober 2026: atas permintaan pemilik, kode 31 dipromosikan ke **production** (rollout penuh) lewat Google Play Developer API dengan teks "Apa yang baru" di atas, bahasa `id`; teksnya diperiksa sebelum dan sesudah promosi. Jalur production kini berisi `31 (1.0.29)` menggantikan `30 (1.0.28)`. Status peninjauan Google hanya terlihat di Play Console.

## Verifikasi

- Laravel: 172 tes lulus. Pint lulus.
- Mobile: semua berkas tes lulus, termasuk `tests/account.test.cjs`; `expo export` Android berhasil.
- Bundle berisi versi 1.0.29, alamat API production, layar Hapus Akun dan tombol Hapus Undangan; teks formulir email dan "Belum punya akun? Daftar" sudah tidak ada.
- Production setelah deploy: `/api/register` 410, `/api/login` menjawab pesan Google, `DELETE /api/account` tanpa login 401, `/hapus-akun` 200, halaman undangan dan gift normal, tidak ada error baru di log.
- Halaman `/hapus-akun` diperiksa di browser lokal pada lebar 375 px.

Belum diuji: layar Masuk, Hapus Akun, dan Hapus Undangan di ponsel sungguhan; halaman admin Pengguna lewat login admin sungguhan.
