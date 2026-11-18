# Comprehensive Risk Assessment
## Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web

- **Version:** 1.0 (Phase 1 Technical Audit)
- **Status:** APPROVED FOR DISCOVERY
- **Target Project:** Capstone Project Sistem Informasi — Telkom University
- **Date:** 13 November 2026

---

## 1. Risk Evaluation Methodology

Penilaian risiko dilakukan menggunakan matriks standar **Likelihood (Kemungkinan Terjadi)** vs **Impact (Tingkat Keparahan Dampak)**:

| Likelihood / Impact | Negligible (1) | Minor (2) | Moderate (3) | Major (4) | Catastrophic (5) |
|---|:---:|:---:|:---:|:---:|:---:|
| **Almost Certain (5)** | Low | Medium | High | Critical | Critical |
| **Likely (4)** | Low | Medium | High | High | Critical |
| **Possible (3)** | Low | Low | Medium | High | High |
| **Unlikely (2)** | Low | Low | Low | Medium | High |
| **Rare (1)** | Low | Low | Low | Low | Medium |

---

## 2. Risk Matrix & Detailed Analysis

### A. Security & Access Control Risks

| Risk ID | Risk Name & Description | Likelihood | Impact | Severity Score | Current Mitigation in Codebase | Recommended Contingency Action |
|---|---|:---:|:---:|:---:|---|---|
| **SEC-01** | **Insecure Direct Object Reference (IDOR) pada Pesanan & Bukti Pembayaran**<br>Pelanggan jahat memodifikasi ID URL untuk mengintip pesanan atau berkas transfer pelanggan lain. | Rare (1) | Major (4) | **LOW (Mitigated)** | `OrderPolicy` memvalidasi kepemilikan data (`order->customer_id === user->customer->id`). `PaymentProofController` dan `RefundProofController` memeriksa policy sebelum menyajikan berkas privat. | Pertahankan policy enforcement di seluruh endpoint baru. Lakukan regression test IDOR secara berkala. |
| **SEC-02** | **Broken Access Control Lintas Role**<br>Pengguna dengan role Customer mencoba mengakses URL Admin/Owner atau sebaliknya. | Unlikely (2) | Major (4) | **MEDIUM** | Middleware `CheckRole` diterapkan pada seluruh route group (`role:customer`, `role:admin,owner`, `role:owner`). Unauthorized request langsung menghasilkan HTTP 403 Forbidden. | Pertahankan isolasi rute. Uji setiap route baru dengan skenario unauthorized matrix test. |
| **SEC-03** | **Kebocoran Bukti Transfer ke Ruang Publik**<br>Foto struk transfer BCA atau bukti refund dapat diakses bebas tanpa login melalui tautan web langsung. | Rare (1) | Major (4) | **LOW (Mitigated)** | Disimpan pada disk `local` (`storage/app/private/`) dengan flag `'serve' => false`. Berkas hanya disajikan melalui controller stream berotentikasi dengan header `Cache-Control: private, no-store` dan CSP `sandbox`. | Jalankan berkala `php artisan payments:secure-proofs` untuk memastikan tidak ada berkas tersesat di `storage/app/public/`. |
| **SEC-04** | **Serangan Brute Force pada Endpoint Autentikasi**<br>Penyerang membombardir form login atau registrasi untuk menebak kata sandi pengguna atau memenuhi database. | Unlikely (2) | Moderate (3) | **LOW (Mitigated)** | Route `/login` dan `/register` dilindungi middleware throttle bawaan Laravel (`throttle:5,1`) serta penguncian IP & email via `RateLimiter` di controller. | Pasang proteksi Cloudflare Turnstile / reCAPTCHA jika sistem dibuka untuk publik secara luas. |
| **SEC-05** | **Sensitive Information Exposure via Debug Mode**<br>`APP_DEBUG=true` aktif di server produksi, mengekspos environment variables, query database, dan kredensial saat terjadi error. | Possible (3) | Major (4) | **HIGH (Environment Risk)** | Halaman error khusus Editorial Stone (`resources/views/errors/`) telah dibuat mandiri tanpa kueri database. | **Wajib:** Pastikan file `.env` di server produksi memiliki `APP_DEBUG=false` dan `APP_ENV=production`. |

---

### B. Financial & Business Logic Risks

| Risk ID | Risk Name & Description | Likelihood | Impact | Severity Score | Current Mitigation in Codebase | Recommended Contingency Action |
|---|---|:---:|:---:|:---:|---|---|
| **FIN-01** | **Manipulasi Harga dari Sisi Klien (Price Tampering)**<br>Pengguna memodifikasi harga satuan atau total belanja pada request HTTP checkout. | Rare (1) | Major (4) | **LOW (Mitigated)** | Request harga dari browser diabaikan. `CreateOrderAction` mengambil harga satuan resmi langsung dari tabel `product_variants` dan menghitung ulang total di server. | Pertahankan kalkulasi server-side harga dan kuantitas. |
| **FIN-02** | **Double-Order / Race Condition Checkout**<br>Pengguna mengklik tombol checkout berulang kali secara cepat, menghasilkan pesanan ganda dan tagihan duplikat. | Rare (1) | Moderate (3) | **LOW (Mitigated)** | Sistem menerapkan UUID `checkout_token` idempotency: request dengan token yang sama mengembalikan pesanan yang sudah dibuat sebelumnya tanpa menduplikasi data. Tombol UI menampilkan loading indicator via `app.js`. | Pertahankan mekanisme idempotency token dan row locking pada pemrosesan pesanan. |
| **FIN-03** | **Persetujuan Pembatalan Tanpa Refund Finansial**<br>Admin menyetujui pembatalan pesanan yang sudah berstatus `Pembayaran Diverifikasi` tanpa mentransfer balik dana pelanggan. | Rare (1) | Major (4) | **LOW (Mitigated)** | `DecideCancellationAction` dan `DecideCancellationRequest` memblokir approval pesanan terbayar jika Admin belum mengisi nominal refund (harus sama persis dengan total bayar), nomor referensi bank, dan bukti transfer refund. | Pertahankan aturan bisnis refund wajib sebelum approval pembatalan pesanan terbayar. |
| **FIN-04** | **Ketidaksesuaian Mutasi Rekening BCA Merchant**<br>Pelanggan mengunggah bukti palsu atau nominal transfer tidak sesuai dengan tagihan pesanan. | Possible (3) | Moderate (3) | **MEDIUM (Operational)** | Verifikasi pembayaran bersifat manual oleh Admin/Owner. Admin wajib membandingkan bukti dengan mutasi rekening bank nyata sebelum mengubah status menjadi `Valid`. Nominal bukti divalidasi presisi cent integer via `Money::matches()`. | Berikan SOP tertulis bagi Admin operasional untuk selalu memeriksa e-banking BCA sebelum verifikasi. |

---

### C. Operational & System Reliability Risks

| Risk ID | Risk Name & Description | Likelihood | Impact | Severity Score | Current Mitigation in Codebase | Recommended Contingency Action |
|---|---|:---:|:---:|:---:|---|---|
| **OPS-01** | **Task Scheduler Tidak Berjalan di Host Server**<br>Cron / Windows Task Scheduler tidak aktif, menyebabkan pesanan tak terbayar tidak pernah batal otomatis dan backup harian terhenti. | Likely (4) | Major (4) | **HIGH (Deployment Risk)** | Perintah konsol `orders:cancel-expired` dan `database:backup` sudah dibuat rapi di `routes/console.php` dan teruji. | Pasang task terjadwal di Windows Task Scheduler saat setup server produksi (`php artisan schedule:run` per menit). |
| **OPS-02** | **Kegagalan Pengiriman Email Reset Password**<br>Pelanggan lupa kata sandi dan tidak dapat memulihkan akun karena `MAIL_MAILER` masih disetel ke `log`. | Likely (4) | Moderate (3) | **HIGH (Configuration Risk)** | Fitur lupa kata sandi (`PasswordResetLinkController`) dan reset (`NewPasswordController`) berfungsi sempurna secara internal. | Konfigurasikan kredensial SMTP aktif (Mailtrap / Sendgrid / SMTP hosting) pada file `.env` produksi. |
| **OPS-03** | **Bencana Kehilangan Data (Disaster Recovery)**<br>Kerusakan storage, korupsi disk, atau ransomware menyebabkan database produksi hilang. | Unlikely (2) | Catastrophic (5) | **HIGH** | Command `database:backup` menghasilkan dump `.sql.gz` terkompresi dengan retensi lokal 14 hari. | Lakukan uji coba pemulihan (restore drill) ke database kosong sebelum go-live, dan salin backup ke storage off-host (S3/Drive terpisah). |
| **OPS-04** | **Ketergantungan Jaringan pada CDN jsDelivr**<br>Bootstrap 5.3 CSS/JS dimuat dari CDN eksternal. Jika jaringan kampus/kantor memblokir CDN atau CDN mengalami gangguan, styling halaman rusak. | Unlikely (2) | Moderate (3) | **MEDIUM** | Menggunakan atribut Subresource Integrity (SRI) untuk menjamin keaslian skrip CDN. | Untuk lingkungan produksi yang sepenuhnya independen, bundel aset Bootstrap ke direktori lokal menggunakan build tool Vite. |

