# Rilis 1.0.26 (28)

Tanggal persiapan: 13 Oktober 2026. Menggantikan 1.0.25 (27) yang ada di production.

## Backend

- Commit `6c39f7b` di-deploy ke production pada 13 Oktober 2026.
- Migration `2026_10_13_000000_add_mepandes_invitations` menambah kolom mepandes pada `invitations`.
- Migration `2026_10_13_010000_add_takedown_to_invitations` menambah `taken_down_at`, `takedown_note`, dan `feed_blocked_at` pada `invitations`.
- Migration `2026_10_13_020000_allow_guest_content_reports` membuat `reporter_user_id` boleh kosong dan menambah `reporter_ip` serta `reporter_contact` pada `content_reports`.
- InvitationTemplateSeeder menambah tiga template mepandes; jumlah template 29 menjadi 32.
- Backup sebelum deploy: `/var/backups/undangan-bali/release-20261013T001946Z/`.
- Jumlah user, undangan, gift, pencairan, komentar, dan laporan sama sebelum dan sesudah deploy.

## Perubahan Untuk Pengguna Sejak 1.0.25

- Undangan Mepandes (Potong Gigi) dengan tiga template (Bale Gading, Sekar Emas, Wastra Songket), untuk satu keluarga atau beberapa keluarga, dengan daftar peserta dan foto opsional. Lihat [`UNDANGAN-MEPANDES.md`](UNDANGAN-MEPANDES.md).
- Setiap halaman undangan punya link "laporkan undangan ini" menuju formulir laporan tanpa login (`/u/{slug}/lapor`).
- Alasan laporan baru di aplikasi dan web: "Undangan palsu atau penipuan".

Teks "Apa yang baru" untuk Play Console:

```text
- Baru: undangan Mepandes (potong gigi), untuk satu atau beberapa keluarga
- Tiga desain baru bernuansa Bali untuk Mepandes
- Undangan palsu kini bisa dilaporkan langsung dari halaman undangan
```

## Kontrol Undangan Palsu (Admin)

- Menu **Undangan** (`/admin/invitations`): daftar semua undangan dengan pencarian dan filter Tayang, Dilaporkan, Diturunkan.
- **Turunkan undangan** (alasan wajib), dari menu Undangan atau dari antrian Laporan. Link undangan menampilkan "Undangan tidak tersedia" (HTTP 410), halaman gift mati, dan undangan keluar dari feed. Pemilik tidak dapat mempublikasikan ulang, menampilkan kembali di feed, atau menghapusnya; admin dapat memulihkan.
- "Sembunyikan Moment dari feed" kini tidak dapat dibatalkan pemilik.
- Pencairan gift ditolak selama undangan diturunkan; pengajuan yang sudah menunggu hanya dapat ditolak admin. Halaman pencairan memberi peringatan bila undangan punya laporan yang belum ditinjau.
- Laporan tamu masuk ke antrian Laporan dengan tanda "tamu dari halaman undangan", alamat IP, dan kontak opsional. Batas lima kiriman per sepuluh menit per alamat.
- Gift yang terlanjur dibayar ke undangan palsu tertahan di akun iPaymu bersama; pengembalian ke pemberi dilakukan manual.

Tahap berikutnya yang dicatat, belum dikerjakan: penangguhan akun, verifikasi email saat daftar, batas jumlah undangan per akun, dan paragraf Syarat Layanan tentang undangan palsu serta pengembalian gift.

## Android

- Package: `com.balisantih.undanganbali`.
- Version name: `1.0.26`; version code: `28`.
- Berkas: `mobile/android/app/build/outputs/bundle/release/undangan-bali-santih-1.0.26-v28.aab`.
- Ukuran: 55.282.679 byte.
- SHA-256: `86F73E6A7F7B114C6739D9DCDDC40B78672B6636E7808998333F3BC0DD045EDC`.
- Sertifikat upload sama dengan rilis sebelumnya (`33:C8:4A:68...`); `jarsigner -verify` lulus.
- 13 Oktober 2026: diunggah ke jalur **internal testing** lewat `eas submit --profile internal` (status COMPLETED). Production tetap `27 (1.0.25)` sampai pemilik meminta promosi.
- 13 Oktober 2026: atas permintaan pemilik, kode 28 dipromosikan ke **production** (rollout penuh) lewat Google Play Developer API dengan teks "Apa yang baru" di atas, bahasa `id`. Jalur production kini berisi `28 (1.0.26)` menggantikan `27 (1.0.25)`. Status peninjauan Google hanya terlihat di Play Console.

## Verifikasi

- Laravel: 152 tes, 2365 assertion lulus. Pint lulus.
- Mobile: kedelapan berkas tes lulus.
- Formulir laporan dan link-nya dilihat di browser pada lebar ponsel terhadap backend lokal.
- Production setelah deploy: API template mepandes, tiga preview template mepandes, halaman utama, login admin, dan feed mengembalikan HTTP 200; tiga undangan yang sedang tayang memuat link lapor dan formulirnya terbuka; antrean, php-fpm, dan nginx aktif.

Belum diuji: halaman admin Undangan dan tombol Turunkan di browser (hanya lewat tes otomatis), mengirim laporan dan menurunkan undangan di production, publish mepandes dari aplikasi di perangkat nyata. Simbol serta kalimat arti upacara mepandes masih menunggu pemeriksaan adat oleh pemilik.
