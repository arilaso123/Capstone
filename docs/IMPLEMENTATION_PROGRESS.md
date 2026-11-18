# Status Implementasi
## Sistem Informasi Pengelolaan Pre-Order Parfum — Uyuy Jastip

Pembaruan: **10 November 2026**. Target database: MySQL/MariaDB. Status ini merangkum kode saat ini, migrasi lokal yang telah diterapkan, dan pemeriksaan yang benar-benar dijalankan.

## Ringkasan Eksekutif

**Kesiapan sidang/demo: 85/100. Kesiapan produksi: 65/100 (belum layak rilis publik).** Penilaian adalah estimasi kelengkapan dan bukti verifikasi, bukan sertifikasi keamanan. Fitur inti telah dilengkapi dengan harga varian eksplisit, batas tanggal PO WIB, checkout transaksional/idempotent, tenggat pembayaran, refund manual, resi/ongkir, password reset, pencatatan audit, serta backup MySQL terjadwal.

| Area | Status | Bukti/keterbatasan |
|---|---:|---|
| Spesifikasi dan keputusan | 95% | Keputusan tanggal PO, refund manual, tenggat default 24 jam, dan retensi bukti dicatat. Owner masih perlu mengesahkan nominal/tenggat operasional dan prosedur arsip. |
| Frontend dan integrasi view | 88% | Bootstrap menjadi framework tunggal, tema dibangun sebagai aset lokal. UAT browser/responsif belum dilakukan. |
| Database | 96% | Semua migrasi berjalan di MySQL XAMPP lokal setelah backup. Staging dan restore drill belum dilakukan. Satu order lama tidak memiliki status history; kronologi historis tidak ditebak. |
| Backend dan keamanan | 91% | RBAC/Policy, validasi, transaksi, harga eksplisit, refund, expiry, upload privat, dan audit untuk mutasi penting tersedia. Uji konkurensi MySQL belum dilakukan. |
| Pengujian | 64% | 16 Feature Tests / 96 assertion lulus di SQLite in-memory. Ini belum menggantikan uji integrasi MySQL, browser, staging, maupun penilaian keamanan eksternal. |

## Status Alur Bisnis

| Alur | Kondisi aktual | Status |
|---|---|---|
| Katalog → keranjang → checkout | Varian/harga dibaca dari database; order hanya dibuat jika PO berstatus `Dibuka` dan tanggal WIB inklusif berada di dalam rentang; token UUID idempotent dan seluruh transaksi dibuat atomik. |
| Pembayaran | Nominal diverifikasi terhadap tagihan; file baru disimpan privat; unggahan dan hasil verifikasi per attempt tersimpan; unggahan baru setelah tenggat ditolak. |
| Expiry pembayaran | Default 24 jam dari pembuatan order, dapat diatur via `PAYMENT_DEADLINE_HOURS`; command terjadwal membatalkan order lewat tenggat kecuali bukti masih menunggu verifikasi atau cancellation masih pending. |
| Pembatalan dan refund | Order belum dibayar mengikuti alur approval. Order berbayar hanya dapat disetujui setelah Admin mencatat refund penuh, referensi unik, bukti privat, dan waktu refund. |
| Pemenuhan | Admin mengisi kurir dan nomor resi sebelum menandai `Barang Dikirim`; ongkir dicatat terpisah dan belum ditambahkan ke tagihan pembayaran. |
| Rekap Owner | Pendapatan dan modal pengadaan dihitung melalui agregasi database untuk order dengan payment valid; modal lama yang tidak tercatat tetap NULL. |
| Backup | Perintah backup terkompresi dan scheduler harian pukul 02.00 WIB tersedia. Scheduler Windows harus diaktifkan; backup terenkripsi di luar host dan latihan pemulihan belum dilakukan. |

## Matriks Keterlacakan FR

| FR | Status | Catatan |
|---|---|---|
| FR-01 Registrasi customer | Selesai | Form Request, normalisasi/validasi nomor Indonesia, kode pos, transaksi pembuatan akun/profil. |
| FR-02 Login/logout | Selesai | Rate limit, regenerasi sesi, logout membersihkan sesi. |
| FR-03 Akses role | Selesai | Middleware role dan `OrderPolicy`; jalur utama order/bukti mengecek otorisasi. |
| FR-04 Kelola PO | Selesai | Buat, ubah nama/catatan/tanggal sebelum ada order, buka/tutup; checkout menegakkan tanggal WIB. |
| FR-05 Kelola produk dan varian | Selesai | Owner mengatur harga/modal tiap ukuran yang tersedia; ukuran tanpa harga tidak dijual. |
| FR-06 Katalog | Selesai | Filter ukuran memakai relasi varian, harga awal dan ukuran berasal dari data. |
| FR-07 Checkout dalam periode | Selesai | Dicek kembali di transaksi dengan shared lock yang kompatibel untuk checkout paralel. |
| FR-08 Order terhubung customer/PO | Selesai | Detail, payment, serta status awal dibuat atomik. |
| FR-09 Multi-item/idempotency | Selesai | Harga server-side, token checkout unik, harga/modal tersnapshot. Uji request paralel masih terbuka. |
| FR-10 Info bayar/unggah bukti | Selesai | Konfigurasi BCA, validasi nominal/tipe/ukuran, disk privat. |
| FR-11 Review bukti | Selesai | Hanya role/pemilik berwenang; legacy URL eksternal harus ditinjau/reupload manual. |
| FR-12 Keputusan pembayaran | Selesai | Update payment, attempt, order, history, dan audit dalam transaksi. |
| FR-13 Timeline order | Selesai | Status transisi baru dicatat dan ditampilkan; 1 order legacy tanpa history perlu direkonsiliasi sumber aslinya. |
| FR-14 Pengajuan pembatalan | Selesai | Ownership/status dicek dan pengajuan ulang setelah ditolak ditangani. |
| FR-15 Keputusan/refund | Selesai | Refund manual penuh dan bukti wajib dicatat sebelum pembatalan order berbayar disetujui. |
| FR-16 Status order customer | Selesai | Query dan akses detail dibatasi Policy kepemilikan. |
| FR-17 Rekap PO | Selesai | Hanya pembayaran valid yang masuk pendapatan/pengadaan; perlu dicocokkan Owner dengan pembukuan. |
| FR-18 Filter order | Selesai | Filter status/pencarian/order PO tersedia; beban skala produksi belum diukur. |
| FR-19 Riwayat bukti/status | Selesai | `payment_attempts`, histories, dan audit log mempertahankan jejak penting. |
| FR-20 Isolasi customer | Selesai | IDOR order diuji pada jalur detail; semua route lintas role tetap perlu regression test lebih luas. |
| FR-21 Batas kewenangan Admin | Selesai | Role groups di route; audit test komprehensif antar-role belum lengkap. |
| FR-22 Retensi bukti | Sesuai keputusan Owner | Bukti disimpan privat sampai Owner menentukan arsip/purge. Tidak ada purge otomatis. |

## Perbaikan yang Diterapkan

- Migrasi indeks, idempotency/ledger, varian produk/tenggat/pengiriman, data refund, dan audit log diterapkan ke database lokal.
- Form Request dan `CreateOrderAction` memisahkan validasi registrasi/checkout serta transaksi pembuatan order.
- Harga tidak lagi ditebak dengan formula ukuran; setiap ukuran/harga/modal merupakan data Owner dan snapshot modal order.
- Refund pembatalan berbayar mensyaratkan transfer penuh dan bukti privat; referensi refund unik.
- Password reset memakai Laravel broker; rate limit diterapkan pada register/login/reset.
- File bukti lokal tidak dapat dilayani sebagai URL publik; header keamanan dipasang pada grup web; cookie secure aktif secara default di produksi.
- Katalog menggunakan eager loaded variants; rekap belanja dihitung agregat SQL; periode PO di-cache singkat dan cache dibersihkan pada perubahan.
- Tailwind serta plugin build-nya dihapus; Bootstrap menjadi framework tunggal, tema lokal dikompilasi Vite.
- Ditambahkan backup database terkompresi privat dan runbook pemulihan.

## Keputusan dan Batas Operasional

- **PO:** checkout hanya dalam tanggal mulai/akhir WIB (inklusif) dan status `Dibuka`; status administrasi tidak otomatis ditutup saat akhir tanggal.
- **Payment deadline:** default implementasi 24 jam, dapat dikonfigurasi. Owner tetap perlu mengesahkan nilai operasional akhir.
- **Refund:** Admin harus mencatat refund manual sebelum approval pembatalan order yang sudah dibayar.
- **Bukti pembayaran:** simpan sampai Owner menetapkan kebijakan arsip/purge; keputusan ini mengesampingkan batas 30 hari di proposal. Retensi backup database (14 hari default) berbeda dari retensi file bukti.
- **Varian legacy:** data lokal berisi 15 produk dan 15 varian yang ter-backfill dari ukuran/harga utama lama. Harga ukuran lain tidak dibuat otomatis; Owner perlu mengisi hanya ukuran yang benar-benar ditawarkan.
- **Ongkir:** dicatat untuk referensi operasional, tidak termasuk nominal order/payment hingga kebijakan bisnis disepakati.
- **Konfigurasi deployment:** set secret `APP_KEY`, `APP_DEBUG=false`, email SMTP, rekening BCA resmi, URL sosial resmi, koneksi database, dan jalankan scheduler.
- **Data legacy:** 7 payment dengan bukti dan 7 attempt terhubung; 0 bukti terbaru tanpa attempt. Periksa URL eksternal dan akses file lama. Satu order tanpa riwayat status tidak diberi history sintetis karena fakta transisinya tidak diketahui.

## Verifikasi Terakhir

- Backup pra-migrasi: `storage/app/private/backups/uyuy-20261110-063239.sql.gz` (7.947 byte, direktori privat); stream gzip berhasil dibaca penuh (40.208 byte SQL, 18 `CREATE TABLE`, marker dump selesai).
- `php artisan migrate:status`: semua migrasi berstatus `Ran`; tiga migrasi 10 November diterapkan setelah backup.
- Pemeriksaan agregat MySQL lokal: 15 produk, 15 varian, 0 produk tanpa varian; 0 order menunggu pembayaran tanpa deadline; 7 bukti/7 payment attempt; 0 bukti terbaru tanpa attempt; 1 order tanpa status history lama.
- PHPUnit SQLite: **16 test, 96 assertion lulus**, termasuk penambahan varian produk ke keranjang.
- PHP lint: **83 berkas PHP lulus**.
- Build Vite berhasil; Blade templates berhasil dicache; **54 route** terdaftar dan tidak ada controller action yatim; `git diff --check` lulus (hanya peringatan normal bahwa `composer.lock` memiliki CRLF dan Git akan menormalisasi ke LF).

## Pekerjaan Sebelum Rilis

1. Isi harga/modal varian berdasarkan daftar resmi Owner dan tinjau bukti legacy/1 order tanpa history.
2. Konfigurasikan SMTP/BCA/media sosial, secret produksi, serta keputusan akhir payment deadline dan ongkir.
3. Aktifkan Scheduler Windows setiap menit, pastikan backup tersalin terenkripsi di luar host.
4. Jalankan restore drill pada database uji, MySQL integration test, uji paralel checkout/verifikasi-vs-cancellation, lalu browser smoke/UAT.
5. Tinjau CSP: kebijakan kini mengizinkan inline script/style karena view lama masih menggunakannya; migrasikan skrip/style ke asset lokal sebelum menegakkan CSP lebih ketat.
