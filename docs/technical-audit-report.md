# Technical Audit Report (Phase 1 — Discovery & Codebase Audit)
## Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web

- **Auditor Role:** Senior Software Architect, Senior Full-Stack Engineer, Senior Backend Engineer, Senior Frontend Engineer, Database Engineer, Application Security Engineer, QA Engineer.
- **Audit Date:** 13 November 2026
- **Context:** Capstone Project Sistem Informasi — Telkom University
- **Target Application:** Uyuy Jastip Pre-Order Fragrance Web System
- **Execution Mode:** STRICT READ-ONLY DISCOVERY

---

## 1. Executive Summary

Audit teknis mendalam dan skeptis telah dilakukan terhadap seluruh komponen codebase **Uyuy Jastip**. Sistem ini merupakan aplikasi pengelolaan jasa titip parfum berbasis pre-order (PO) multi-peran (Customer, Admin, Owner) yang dirancang untuk mendukung siklus pemesanan berbatas waktu, pembayaran transfer BCA Merchant manual, permohonan pembatalan, manajemen katalog produk, serta rekapitulasi bisnis Owner.

### Ringkasan Status Kesiapan Sistem

| Evaluasi | Nilai Kesiapan | Status | Ringkasan Penilaian |
|---|:---:|:---:|---|
| **Kesiapan Sidang Capstone / Demo Prototipe** | **94 / 100** | **SIAP DEMO** | Seluruh 18 kebutuhan fungsional inti berjalan utuh. Alur bisnis Customer, Admin, dan Owner telah terintegrasi dengan validasi ketat, proteksi IDOR, audit trail, serta desain antarmuka Editorial Stone v2 yang proporsional dan responsif. |
| **Kesiapan Rilis Produksi Publik (Commercial Ready)** | **82 / 100** | **SIAP DENGAN CATATAN DEPLOYMENT** | Kode aplikasi sudah sangat solid dan mengimplementasikan best practices keamanan. Kesiapan rilis publik hanya memerlukan langkah administratif deployment: penyetelan kredensial SMTP email produksi, konfigurasi Windows Task Scheduler untuk scheduler cron, backup restore drill, serta self-hosting pustaka CSS Bootstrap CDN. |

### Matriks Temuan Audit Berdasarkan Severity

| Severity Level | Jumlah Temuan | Status Temuan | Keterangan |
|---|:---:|:---:|---|
| 🔴 **CRITICAL** | **0** | **TERKENDALI** | Tidak ditemukan celah keamanan kritis terbuka (seperti remote code execution, SQL injection terbuka, atau hardcoded plain-text credential yang bocor). Seluruh isu kritis arsitektur awal telah termitigasi dengan baik. |
| 🟠 **HIGH** | **2** | **MEMERLUKAN DEPLOYMENT SETUP** | 1) Ketergantungan Task Scheduler OS untuk otomatisasi pembatalan order kedaluwarsa & backup harian.<br>2) Ketergantungan mailer `log` untuk link reset password yang memerlukan konfigurasi SMTP nyata saat rilis. |
| 🟡 **MEDIUM** | **4** | **PERBAIKAN MINOR & REFACTOR** | 1) Ketergantungan CDN eksternal jsDelivr untuk Bootstrap 5.3.<br>2) Rekonsiliasi berkas bukti transfer lama (legacy data) yang menggunakan URL eksternal.<br>3) Pengujian otomatis konkuren database masih berbasis SQLite in-memory, belum terhubung ke instance MySQL nyata di CI pipeline.<br>4) Query agregasi rekapitulasi Owner berada langsung di controller dan dapat diekstrak ke service layer. |
| 🟢 **LOW / INFO** | **3** | **OPTIMASI** | 1) Input ongkir pengiriman masih manual tanpa integrasi API logistik pihak ketiga.<br>2) Session driver menggunakan database MySQL alih-alih Redis.<br>3) Script pengujian lama Python (`run_system_test.py`, `test_business_flows.py`) tidak lagi digunakan dan sebaiknya dihapus. |

---

## 2. Project Architecture Overview

Aplikasi dibangun menggunakan pola arsitektur **Clean MVC + Domain Actions** di atas framework Laravel 12:

```
[ HTTP Request ] 
       │
       ▼
[ SecurityHeaders Middleware ] (CSP, nosniff, frame-ancestors, HSTS)
       │
       ▼
[ Routing & RateLimiter ] (routes/web.php with throttle:5,1)
       │
       ▼
[ CheckRole Middleware ] (Role segregation: customer, admin, owner)
       │
       ▼
[ FormRequest Validation ] (CheckoutRequest, RegisterRequest, etc.)
       │
       ▼
[ Controllers ] (Thin Controllers: Customer, Admin, Owner)
       │
       ├─────────────────────────────────┐
       ▼                                 ▼
[ Domain Actions ]             [ Authorization Policy ]
(CreateOrderAction,            (OrderPolicy: view, interact)
 VerifyPaymentAction,                    │
 DecideCancellationAction)                ▼
       │                       [ Private File Stream ]
       ▼                       (Sandboxed CSP, no-store)
[ Database Transactions ]
(DB::transaction with row-level locks)
       │
       ▼
[ Eloquent Models & AuditLog ]
       │
       ▼
[ Blade View Engine & Vite Assets ] (Editorial Stone v2)
```

1. **Separation of Concerns:** Modul dipisahkan secara tegas berdasarkan namespace peran (`App\Http\Controllers\Customer`, `Admin`, `Owner`).
2. **Domain Action Layer (`app/Actions`):** Logika transaksi yang rumit (pembuatan pesanan, verifikasi pembayaran, persetujuan pembatalan & refund) diisolasi ke dalam Action class mandiri dengan transaksi atomik `DB::transaction(...)` dan row-locking `lockForUpdate()` / `sharedLock()`.
3. **Data Protection Layer (`app/Policies`):** Menggunakan `OrderPolicy` terpusat untuk memvalidasi kepemilikan data sebelum mengizinkan aksi lihat, unduh bukti transfer, atau ajukan pembatalan, sehingga mencegah Insecure Direct Object References (IDOR).
4. **Audit Trail Engine (`App\Models\AuditLog`):** Mencatat setiap mutasi status, verifikasi pembayaran, approval refund, dan pembuatan produk beserta status sebelum (*before_state*) dan sesudah (*after_state*).

---

## 3. Technology Stack Inventory

- **Runtime Environment:** PHP 8.2.12 CLI (ZTS x64) pada OS Windows.
- **Backend Framework:** Laravel Framework versi `12.71.0` (kompatibel penuh dengan PHP 8.2+).
- **Database Engine:** MySQL / MariaDB (Driver `mysql` di `config/database.php`), SQLite 3 in-memory untuk pengujian lokal terisolasi (`phpunit.xml`).
- **Asset Pipeline:** Vite `v6.4.5` + `@vitejs/plugin-laravel` `v1.2.0`, mengompilasi `resources/css/app.css` dan `resources/js/app.js` ke `public/build/`.
- **CSS Architecture:** Bootstrap 5.3.3 + Custom Design System **Editorial Stone v2** (`resources/css/uyuy-theme.css`), menghasilkan bundel CSS teroptimasi sebesar `116.99 kB` (gzip: `16.69 kB`).
- **JavaScript Engine:** Native ES2022+ Vanilla JS terpusat di `resources/js/app.js` (`64.83 kB`), menghilangkan kebutuhan framework frontend berat dan mematuhi kebijakan CSP tanpa skrip inline.
- **Testing Engine:** PHPUnit `11.5.57` dengan konfigurasi database `:memory:`.

---

## 4. Frontend Audit

### A. Inventarisasi Halaman & Komponen
Codebase memuat **51 berkas template Blade**:
- **3 Layout Utama:** `layouts/app.blade.php` (Storefront/Customer), `layouts/admin.blade.php` (Admin Workspace), `layouts/owner.blade.php` (Owner Workspace).
- **13 Komponen UI Bersama:** `navbar`, `footer`, `sidebar-admin`, `sidebar-owner`, `customer-portal-nav`, `product-card`, `status-badge`, `order-timeline`, `flash-messages`, `demo-badge`, `button`, `card`, `input`.
- **4 Halaman Publik:** Beranda (`/`), Katalog Produk (`/katalog`), Detail Produk (`/produk/{id}`), Informasi PO (`/informasi-po`).
- **4 Halaman Autentikasi:** Login (`/login`), Registrasi (`/register`), Lupa Password (`/forgot-password`), Reset Password (`/reset-password/{token}`).
- **6 Halaman Portal Customer:** Dashboard, Keranjang, Daftar Pesanan, Detail Pesanan, Unggah Pembayaran, Pengajuan Pembatalan.
- **7 Halaman Workspace Admin:** Dashboard Operasional, Antrean Pesanan, Detail Pesanan, Antrean Pembayaran, Verifikasi Pembayaran, Antrean Pembatalan, Tinjau Pembatalan.
- **9 Halaman Workspace Owner:** Dashboard Eksekutif, Manajemen Periode PO (Daftar, Tambah, Edit), Master Produk (Daftar, Tambah, Edit), Rekapitulasi PO, Direktori Pelanggan.
- **5 Halaman Error Sistem:** Shell mandiri `errors/layout.blade.php` bersama template `403`, `404`, `419`, dan `500`.

### B. Evaluasi Responsivitas & Tipografi
- **Skala Desain Editorial Stone v2:** Menggunakan hierarki tipografi proporsional mulai dari `--text-micro` (`12px` tracked uppercase) hingga Hero `--text-heading` (`44px–58px`), dengan angka monospaced `tabular-nums` untuk nominal rupiah dan metrik.
- **Breakpoint Responsif:** Telah dioptimasi untuk target viewport **`1440px`**, **`1366px`**, **`768px`**, dan **`390px`** (+ guardrail `< 380px`).
- **Integritas Tata Letak:**
  - Sidebar Admin dan Owner (`256px`) menggunakan `position: sticky; top: 68px; height: calc(100vh - 68px);` sehingga tidak lagi tertutup atau terpotong di bawah topbar saat halaman digulir.
  - Tabel operasional desktop memakai `width: 100%; min-width: 0;` dan elipsis nomor pesanan (`max-width: 195px` dengan tooltip `title`), mencegah luapan horizontal pada layar laptop `1366×768`.
  - Kolom rupiah menerapkan `white-space: nowrap !important` sehingga simbol `Rp` tidak pernah terpisah dari angka nominalnya.

### C. State Handling & Validasi Klien
- **Form States:** Seluruh form dilindungi pencegah *double-submission* otomatis pada `app.js` (`data-ui-submitting="true"`, `aria-busy="true"`, `.ui-submit-is-loading`). Tombol submit pulih otomatis saat navigasi *back-forward cache* (`pageshow`).
- **Feedback State:** Tersedia penanganan lengkap untuk loading, validation error (`is-invalid` terhubung dengan `aria-describedby`), flash messages (`flash-messages.blade.php`), serta empty states informatif di setiap tabel/kartu.

---

## 5. Backend Audit

### A. Routing & Middleware
- 53 rute web terdaftar secara eksplisit di `routes/web.php` dengan penamaan rute standar Laravel.
- Middleware `SecurityHeaders` menyuntikkan header keamanan HTTP (`nosniff`, `DENY`, `strict-origin`, serta CSP ketat).
- Middleware `CheckRole` membatasi hak akses berdasarkan role pengguna dengan abort HTTP 403 Forbidden.
- Rate limiting aktif di rute publik kritis: `/login` (5 attempt/menit), `/register` (5 attempt/menit), `/forgot-password` (3 attempt/menit), dan `/reset-password` (5 attempt/menit).

### B. Controller & Form Requests
- Logika validasi HTTP telah dipisahkan menggunakan class `FormRequest` tersendiri (`RegisterRequest`, `CheckoutRequest`, `VerifyPaymentRequest`, `DecideCancellationRequest`), menjaga controller tetap ringkas (*skinny controllers*).
- Normalisasi nomor telepon terpusat pada `IndonesianPhone::normalize()`, secara otomatis mengubah format `+62...` atau `62...` menjadi `08...`.

### C. Transaksi Database & Concurrency Handling
- Seluruh aksi mutasi data kritis dijalankan di dalam `DB::transaction(...)`.
- `CreateOrderAction` menggunakan `sharedLock()` pada pengecekan periode PO yang sedang dibuka. Ini memungkinkan transaksi pemesanan konkuren berjalan secara bersamaan tanpa saling mengunci antrean, sekaligus memblokir aksi penutupan PO oleh Owner sampai transaksi selesai dicatat.
- Penomoran pesanan menggunakan format `UJ-PO{poId}-{YYYYMMDD}-{ULID}` yang menghilangkan ketergantungan pada query counter beruntun dan mencegah *lock contention* pada tabel pesanan.
- `VerifyPaymentAction` dan `DecideCancellationAction` menerapkan row-level lock `lockForUpdate()` pada entitas order, payment, dan cancellation request untuk mencegah *race condition* saat verifikasi atau approval simultan.

---

## 6. Database Audit

### A. Migrasi & Skema Relasional
Database terdiri dari 16 file migrasi yang mendefinisikan tabel inti:
1. `users`: Kredensial dan role pengguna.
2. `customers`: Profil pelanggan (`user_id` unik, relasi 1:1).
3. `po_periods`: Periode pre-order dengan rentang tanggal dan status.
4. `products`: Master produk parfum.
5. `product_variants`: Master varian ukuran (35, 60, 100 ml), harga jual, dan harga modal (`cost_price`).
6. `orders`: Pesanan transaksi dengan foreign key terindeks ke pelanggan dan periode PO.
7. `order_details`: Rincian item pesanan dengan snapshot harga jual dan harga modal.
8. `payments`: Pembayaran pesanan (`order_id` unik, relasi 1:1).
9. `payment_attempts`: Riwayat unggahan bukti pembayaran ulang jika terjadi penolakan.
10. `order_status_histories`: Riwayat perubahan tahapan status pesanan.
11. `cancellation_requests`: Pengajuan pembatalan dengan data audit refund.
12. `audit_logs`: Ledger jejak audit perubahan sistem.

### B. Foreign Keys & Integritas Relasi
- Foreign key didefinisikan dengan constraint yang aman:
  - `orders.customer_id` dan `orders.po_period_id` menggunakan `onDelete('restrict')` untuk mencegah penghapusan data master pelanggan atau periode PO yang masih memiliki transaksi aktif.
  - `order_details.order_id` dan `payments.order_id` menggunakan `onDelete('cascade')`.
  - Kolom pencatat pengguna seperti `verified_by`, `reviewed_by`, dan `changed_by` menggunakan `onDelete('set null')` sehingga jejak historis tetap tersimpan meskipun akun staf dinonaktifkan.
- Seluruh kolom query pencarian dan penyaringan telah dilengkapi index gabungan (`orders_customer_created_at_index`, `orders_po_status_index`, `orders_status_created_at_index`, `payments_status_created_at_index`).

---

## 7. Security Audit

Berdasarkan tinjauan kerangka kerja OWASP Top 10:

1. **Broken Access Control (A01:2021):** **TERLINDUNGI.** Rute diproteksi middleware `CheckRole`. Akses terhadap detail pesanan, pengunggahan pembayaran, dan pembatalan diverifikasi melalui `OrderPolicy` (`authorize('view', $order)` dan `authorize('interact', $order)`). Penggantian ID pada URL oleh pengguna lain menghasilkan HTTP 403 Forbidden.
2. **Cryptographic Failures (A02:2021):** **TERLINDUNGI.** Kata sandi di-hash menggunakan algoritma Bcrypt dengan 12 putaran (`BCRYPT_ROUNDS=12`). Sesi menggunakan cookie berflag `HttpOnly`, `SameSite=lax`, dan otomatis mengaktifkan flag `Secure` saat berjalan di HTTPS produksi.
3. **Injection (A03:2021):** **TERLINDUNGI.** Seluruh query database menggunakan Eloquent ORM dan PDO Prepared Statements dengan parameter binding (`?`). Tidak ada konkatenasi string SQL mentah dari input pengguna.
4. **Insecure Design (A04:2021):** **TERLINDUNGI.** Kalkulasi harga dijalankan 100% di server dari master data `product_variants`. Token idempotency `checkout_token` mencegah pesanan ganda. Persetujuan pembatalan pesanan yang telah dibayar mewajibkan rekonsiliasi transfer refund dan bukti transfer nyata.
5. **Security Misconfiguration (A05:2021):** **TERMITIGASI DI KODE, WAJIB DICEK SAAT DEPLOY.** Middleware `SecurityHeaders` menyuntikkan Content-Security-Policy yang ketat tanpa `'unsafe-inline'` pada `script-src`. Storage disk `local` disetel `'serve' => false` sehingga berkas privat tidak dapat diakses langsung via URL browser.
6. **Vulnerable and Outdated Components (A06:2021):** **TERPANTAU.** Menggunakan Laravel 12.71 dan PHP 8.2 modern. Pustaka eksternal minimal.
7. **Identification and Authentication Failures (A07:2021):** **TERLINDUNGI.** Dilengkapi rate limiting pada login, regenerasi session ID saat login (`session()->regenerate()`), dan invalidasi sesi saat logout (`session()->invalidate()`).
8. **Software and Data Integrity Failures (A08:2021):** **TERLINDUNGI.** Tautan CDN Bootstrap pada layout Blade menggunakan atribut *Subresource Integrity* (SRI hash).
9. **Security Logging and Monitoring Failures (A09:2021):** **TERLINDUNGI.** Dilengkapi tabel `audit_logs` dan `order_status_histories` yang mencatat pelaku, waktu, IP address, user agent, dan status perubahan.
10. **Server-Side Request Forgery (A10:2021):** **TIDAK ADA BIDANG SERANG.** Aplikasi tidak melakukan pemanggilan HTTP outbound ke URL luar berdasarkan input pengguna.

---

## 8. Business Logic Compliance (Pencocokan 18 Kebutuhan Fungsional)

| No | Kebutuhan Bisnis Proposal | Status Implementasi | Bukti Kode & Verifikasi |
|---|---|:---:|---|
| **1** | Registrasi, Login, dan Logout | **COMPLIANT** | `AuthController.php`, `RegisterRequest.php`. Rate limiting aktif, hash Bcrypt, pembuatan profil Customer otomatis, dan pembersihan sesi saat logout. |
| **2** | Role Customer, Admin, dan Owner | **COMPLIANT** | `User.php` (`ROLE_CUSTOMER`, `ROLE_ADMIN`, `ROLE_OWNER`), `CheckRole.php`. Isolasi hak akses berjalan ketat pada 53 rute web. |
| **3** | Pengelolaan Periode PO | **COMPLIANT** | `Owner\PoPeriodController.php`. Fitur buat, edit, tutup, dan buka PO. Hanya satu PO yang dapat berstatus administrasi `Dibuka`. Penutupan PO memvalidasi tanggal batas waktu checkout WIB. |
| **4** | Pengelolaan Produk Parfum | **COMPLIANT** | `Owner\ProductController.php`. Pengelolaan master produk, deskripsi, gambar, kategori, aktivasi status, dan varian multi-ukuran (35, 60, 100 ml) beserta harga modal (`cost_price`). |
| **5** | Katalog Produk | **COMPLIANT** | `CatalogController.php`, `ProductDetailController.php`. Menampilkan produk aktif, filter pencarian teks, kategori aroma, filter ukuran varian, dan pagination. |
| **6** | Checkout Hanya Saat PO Dibuka & Tanggal Valid | **COMPLIANT** | `PoPeriod::isOrderableNow()`, `CreateOrderAction.php`. Mengecek status `Dibuka` dan memastikan tanggal transaksi berada dalam rentang `start_date` dan `end_date` (WIB sampai 23.59). Transaksi di luar jendela waktu otomatis ditolak. |
| **7** | Pemesanan Multi-Item | **COMPLIANT** | `CartController.php`, `CreateOrderAction.php`. Mendukung penambahan multi-produk dan multi-varian dalam satu pesanan dengan batasan 1–50 botol per varian. |
| **8** | Perhitungan Harga di Sisi Server | **COMPLIANT** | `CreateOrderAction.php`. Harga satuan diambil langsung dari tabel database `product_variants`. Input harga dari klien diabaikan sepenuhnya. |
| **9** | Informasi Pembayaran BCA Merchant | **COMPLIANT** | `config/jastip.php`, `customer/orders/payment.blade.php`. Nomor rekening dan nama merchant dibaca dari konfigurasi terpusat dan dilengkapi tombol salin nomor rekening. |
| **10** | Unggah Bukti Transfer | **COMPLIANT** | `Customer\OrderController@uploadPayment`. Validasi berkas gambar/PDF maksimal 2 MB, validasi kecocokan nominal transfer dengan total tagihan pesanan, dan penyimpanan pada storage privat. |
| **11** | Verifikasi Pembayaran Manual | **COMPLIANT** | `Admin\PaymentController.php`, `VerifyPaymentAction.php`. Verifikasi dilakukan manual oleh Admin/Owner dengan mencocokkan mutasi rekening bank nyata. Status diverifikasi menjadi `Valid` atau `Ditolak` (wajib catatan alasan). |
| **12** | Riwayat Perubahan Status Pesanan | **COMPLIANT** | `OrderStatusHistory.php`. Mencatat kronologi transisi status pesanan (`Menunggu Pembayaran` → `Pembayaran Diverifikasi` → `Diproses` → `Barang Dikirim` → `Selesai` atau `Dibatalkan`) beserta nama petugas dan catatan. Ditampilkan pada komponen `<x-order-timeline />`. |
| **13** | Pengajuan Pembatalan Pesanan | **COMPLIANT** | `Customer\OrderController@submitCancellation`. Pelanggan dapat mengajukan pembatalan dengan alasan sebelum pesanan diproses lebih lanjut. Mencegah pengajuan aktif ganda. |
| **14** | Persetujuan atau Penolakan Pembatalan | **COMPLIANT** | `Admin\CancellationController.php`, `DecideCancellationAction.php`. Admin/Owner dapat menyetujui atau menolak. Jika pesanan sudah dibayar, approval mewajibkan pencatatan refund lengkap (nominal, nomor referensi, dan bukti transfer refund). |
| **15** | Customer Hanya Mengakses Pesanan Miliknya | **COMPLIANT** | `OrderPolicy.php`, `Customer\OrderController.php`. Seluruh query pesanan pelanggan dibatasi pada `customer->orders()`. Akses via URL ID milik pelanggan lain dicegah dengan respon HTTP 403 Forbidden. |
| **16** | Rekapitulasi Berdasarkan Periode PO | **COMPLIANT** | `Owner\RecapController.php`. Menampilkan agregasi total pesanan, pendapatan valid, jumlah pelanggan unik, dan kebutuhan pengadaan botol per varian produk yang hanya bersumber dari pesanan dengan pembayaran `Valid`. |
| **17** | Dashboard Admin dan Owner | **COMPLIANT** | `Admin\DashboardController.php`, `Owner\DashboardController.php`. Dashboard Admin menampilkan antrean tugas operasional (pembayaran pending, pembatalan pending, pesanan perlu diproses). Dashboard Owner menampilkan metrik eksekutif bisnis kumulatif dan rincian status batch PO aktif. |
| **18** | Retensi Bukti Pembayaran | **COMPLIANT** | `PaymentAttempt.php`, `app/Console/Commands/SecurePaymentProofs.php`. Bukti transfer disimpan privat di `storage/app/private/` dan riwayat unggahan berulang tetap tersimpan utuh di tabel `payment_attempts`. Bukti transfer tidak dihapus otomatis dan dipertahankan untuk audit keuangan. |

---

## 9. Testing Results

Pemeriksaan pengujian non-destruktif dijalankan pada lingkungan lokal menggunakan test suite resmi Laravel PHPUnit:

- **Perintah:** `php artisan test` (menggunakan database in-memory SQLite `:memory:`).
- **Hasil:** **31 Tests Passed, 256 Assertions Passed (0 Failed, 0 Skipped, 0 Risky)**.
- **Durasi Eksekusi:** 57.52 detik.

### Rincian Test Suite yang Lulus
1. `Tests\Feature\BusinessFlowHardeningTest` (16 tests):
   - Uji checkout idempotency token ganda menghasilkan pesanan tunggal.
   - Uji snapshot harga dan biaya modal dari varian produk aktif.
   - Uji penolakan checkout di luar jendela tanggal PO.
   - Uji pemblokiran persetujuan pembatalan saat bukti transfer masih berstatus pending.
   - Uji preservasi bukti transfer lama saat pelanggan mengunggah bukti perbaikan.
   - Uji pencegahan unggah bukti baru pada pesanan yang sudah kedaluwarsa.
   - Uji agregasi rekapitulasi Owner hanya menghitung pesanan dengan pembayaran valid.
   - Uji pembatalan otomatis pesanan kedaluwarsa tanpa bukti pending.
   - Uji kewajiban pencatatan refund sebelum menyetujui pembatalan pesanan terbayar.
   - Uji alur reset password dan pembaruan hash kata sandi.
   - Uji rate limiting registrasi dan penyuntikan security headers.
   - Uji proteksi IDOR: pelanggan ditolak mengakses pesanan pelanggan lain.
   - Uji kewajiban input data ekspedisi (kurir & nomor resi) saat status diubah ke `Barang Dikirim`.
   - Uji filter katalog varian dan pembaruan jumlah item keranjang.
   - Uji penambahan varian produk ke keranjang.
   - Uji koreksi informasi periode PO oleh Owner.
2. `Tests\Feature\CustomerAndAdminFlowTest` (5 tests):
   - Uji operasi keranjang belanja (tambah, akumulasi, hapus, dan penolakan keranjang kosong/basi).
   - Uji proteksi IDOR pada endpoint bukti pembayaran dan pesanan pelanggan.
   - Uji verifikasi pembayaran Admin (kewajiban catatan saat reject, approval valid, dan pencegahan reverifikasi).
   - Uji pemenuhan status pesanan Admin mengikuti transisi operasional yang sah.
   - Uji penanganan putusan pembatalan pesanan belum dibayar beserta jejak audit.
3. `Tests\Feature\OwnerManagementTest` (3 tests):
   - Uji manajemen periode PO (validasi tanggal, penutupan PO lama, dan toggle status).
   - Uji pembuatan, pengubahan, penonaktifan produk parfum beserta varian dan audit log.
   - Uji rekapitulasi pendapatan Owner dan pencarian direktori pelanggan.
4. `Tests\Feature\RoleAccessAndAuthTest` (5 tests):
   - Uji render halaman storefront publik dan filter katalog.
   - Uji registrasi pelanggan, normalisasi nomor telepon, dan validasi kode pos.
   - Uji pengalihan dashboard berdasarkan role saat login dan invalidasi sesi saat logout.
   - Uji header keamanan CSP menolak skrip inline yang tidak aman.
   - Uji pembatasan akses lintas-role pada seluruh route group.
5. `Tests\Feature\ExampleTest` (1 test): Uji respons status halaman dasar.
6. `Tests\Unit\ExampleTest` (1 test): Uji unit dasar environment.

---

## 10. Findings Ledger (Tabel Temuan Audit)

| Finding ID | Category | Description | Evidence (File & Lokasi Kode) | Severity | Impact | Recommended Action | Verification Status |
|---|---|---|---|:---:|---|---|:---:|
| **F-01** | `Deployment / Scheduler` | Otomatisasi pembatalan order kedaluwarsa dan backup harian bergantung pada Windows Task Scheduler | `routes/console.php:13-14` | **HIGH** | Jika Task Scheduler tidak dipasang di host server, pesanan kedaluwarsa tidak akan dibatalkan otomatis dan backup tidak berjalan. | Pasang task terjadwal per menit (`php artisan schedule:run`) pada Task Scheduler Windows server. | **VERIFIED** |
| **F-02** | `Configuration / Mail` | Driver email default disetel ke `log`, bukan server SMTP nyata | `config/mail.php` & `.env.example:62` | **HIGH** | Tautan pemulihan kata sandi (`/forgot-password`) hanya tertulis di file log lokal dan tidak terkirim ke email pengguna. | Konfigurasikan kredensial SMTP aktif (Mailtrap / Sendgrid / SMTP hosting) pada `.env` produksi. | **VERIFIED** |
| **F-03** | `Frontend / Infrastructure` | Framework CSS Bootstrap 5.3 dimuat melalui CDN jsDelivr eksternal | `resources/views/layouts/app.blade.php:21`, `admin.blade.php:21`, `owner.blade.php:21` | **MEDIUM** | Jika server atau klien berada di jaringan tertutup tanpa internet (intranet), antarmuka kehilangan styling visual. | Bundel aset Bootstrap CSS/JS ke direktori lokal menggunakan npm/Vite sebelum rilis offline. | **VERIFIED** |
| **F-04** | `Data Integrity / Storage` | Terdapat record bukti transfer legacy yang menggunakan format URL eksternal dummy | `database/seeders/DatabaseSeeder.php` & `app/Console/Commands/SecurePaymentProofs.php:34` | **MEDIUM** | Rute privat berkas menolak akses URL eksternal (HTTP 404). Admin tidak dapat melihat bukti lama tersebut. | Jalankan rekonsiliasi data: unggah ulang file bukti lokal atau arsipkan record pembayaran lama sebelum go-live. | **VERIFIED** |
| **F-05** | `Testing / CI Pipeline` | Automated test suite berjalan pada SQLite in-memory, belum menguji live MySQL parallelism | `phpunit.xml:25-26` | **MEDIUM** | Perilaku deadlock atau lock wait timeout pada konkurensi ekstrim MySQL belum tercakup di automated pipeline. | Siapkan staging environment dengan database MySQL nyata untuk menjalankan uji beban dan uji konkurensi. | **VERIFIED** |
| **F-06** | `Architecture / Refactor` | Query kalkulasi analitik rekapitulasi Owner berada langsung di controller | `app/Http/Controllers/Owner/RecapController.php:50-67` | **MEDIUM** | Controller memuat query SQL yang panjang. Meskipun efisien dan aman dari SQL injection, kode kurang modular. | Ekstrak query agregasi ke dalam service class khusus `App\Services\RecapReportService`. | **VERIFIED** |
| **F-07** | `Logistics / Operations` | Ongkos kirim diinput manual oleh Admin tanpa integrasi API ekspedisi | `app/Http/Controllers/Admin/OrderController.php:62`, `orders.shipping_cost` | **LOW** | Admin harus memeriksa ongkir secara manual di luar aplikasi saat mengirim barang. | Pertahankan input manual untuk fase sidang Capstone; jadwalkan integrasi RajaOngkir untuk tahap komersial. | **VERIFIED** |
| **F-08** | `Infrastructure / Cache` | Session dan Cache menggunakan driver database MySQL | `config/session.php:21`, `config/cache.php` | **LOW** | Pada beban ribuan transaksi bersamaan, tabel `sessions` dan `cache` dapat menambah beban I/O database. | Cukup memadai untuk skala Capstone; migrasikan ke Redis jika sistem berkembang ke skala nasional. | **VERIFIED** |
| **F-09** | `Code Organization` | Script pengujian lama berbasis Python masih ada di root direktori | `run_system_test.py`, `test_business_flows.py` | **INFORMATIONAL** | Berkas Python lama berpotensi membingungkan penguji lain karena suite resmi sudah dipusatkan di PHPUnit. | Hapus file script Python lama tersebut dari repositori. | **VERIFIED** |

---

## 11. Component Decisions (Keep, Refactor, Rebuild, Remove)

| Komponen / Modul | Keputusan | Justifikasi Teknis |
|---|:---:|---|
| **Domain Actions** (`CreateOrderAction`, `VerifyPaymentAction`, `DecideCancellationAction`) | **KEEP** | Menerapkan Single Responsibility Principle, transaksi atomik, row-level locking, dan jejak audit trail yang solid. |
| **Eloquent Models & Migrations** (`12 Models`, `16 Migrations`) | **KEEP** | Skema relasional sudah ternormalisasi, foreign keys lengkap dengan constraint aman (`restrict`/`cascade`), index query lengkap, dan relasi Eloquent terdefinisi jelas. |
| **Form Request Classes** (`CheckoutRequest`, `RegisterRequest`, dll.) | **KEEP** | Validasi input ketat, normalisasi nomor telepon Indonesia terpusat, regex kode pos 5 digit, dan pesan error ramah pengguna. |
| **Role-Based Access Control** (`CheckRole.php` & Route Groups) | **KEEP** | Middleware RBAC bekerja konsisten pada 53 rute; isolasi antara Customer, Admin, dan Owner terjaga ketat. |
| **Order Policy & Secure File Access** (`OrderPolicy`, `PaymentProofController`, `RefundProofController`) | **KEEP** | Mencegah IDOR secara menyeluruh. Bukti transfer dan refund dilindungi authorization policy dan disajikan via response stream privat dengan header sandboxed CSP. |
| **Design System Editorial Stone v2** (`uyuy-theme.css`, Vite assets) | **KEEP** | Hierarki visual multi-surface hangat, tipografi multi-weight proporsional, responsive di 4 breakpoint target (`1440px`, `1366px`, `768px`, `390px`), dan bebas sub-13px override. |
| **Scheduler & Console Commands** (`BackupDatabase`, `CancelExpiredOrders`) | **KEEP** | Fitur operasional penting untuk otomatisasi pembatalan order kedaluwarsa dan backup database terkompresi. |
| **Recap Controller** (`Owner\RecapController`) | **REFACTOR** | Query agregasi SQL langsung di controller berfungsi dengan baik dan efisien, namun akan lebih rapi jika dipisah ke Service/Action class. |
| **External CDN Links** (Bootstrap CDN di `layouts/*.blade.php`) | **REFACTOR** | Masih mengandalkan CDN jsDelivr eksternal meskipun sudah diamankan SRI. Sebaiknya di-bundle lokal untuk kemandirian jaringan. |
| **Legacy Python Test Scripts** (`run_system_test.py`, `test_business_flows.py`) | **REMOVE** | Script lama yang tidak lagi dipakai karena testing resmi dipusatkan di PHPUnit. |

---

## 12. Recommendations & Next Steps

Berdasarkan hasil temuan audit teknis, direkomendasikan urutan pengerjaan berikutnya:

### Tahap 1: Persiapan Sidang & Demo Capstone (Segera)
1. **Bersihkan File Yatim:** Hapus script lama `run_system_test.py` dan `test_business_flows.py` agar repositori rapi dan tidak membingungkan dosen penguji.
2. **Pertahankan Kode Utama:** Jangan melakukan perombakan arsitektur besar (*no rebuild needed*). Kode yang ada saat ini sudah sangat matang dan memenuhi 100% dari 18 kebutuhan fungsional proposal.
3. **Dokumentasi & Uji Coba Manual:** Gunakan panduan checklist verifikasi manual pada viewport `1440px`, `1366px`, `768px`, dan `390px` untuk mendemonstrasikan sistem saat presentasi sidang.

### Tahap 2: Persiapan Rilis Produksi (Commercial Launch)
1. **Konfigurasi Environment Server:**
   - Pasang `APP_ENV=production` dan pastikan `APP_DEBUG=false`.
   - Generate `APP_KEY` baru khusus server produksi.
   - Konfigurasikan kredensial SMTP email aktif agar fitur reset password berfungsi mengirim email nyata.
   - Masukkan nomor rekening resmi BCA Merchant dan nama pemilik toko di `BCA_MERCHANT_ACCOUNT` dan `BCA_MERCHANT_NAME`.
2. **Aktivasi Otomasi Server:**
   - Daftarkan scheduled task di Windows Task Scheduler untuk mengeksekusi `php artisan schedule:run` setiap 1 menit.
   - Lakukan uji coba pemulihan data (*restore drill*) dari file backup `database:backup` ke database MySQL pengujian.
3. **Optimasi Kemandirian Aset:**
   - Unduh pustaka Bootstrap 5.3 ke repositori lokal dan bundel via Vite untuk menghilangkan ketergantungan CDN jsDelivr luar.

