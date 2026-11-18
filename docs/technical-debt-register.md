# Technical Debt Register
## Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web

- **Version:** 1.0 (Phase 1 Technical Audit)
- **Status:** ACTIVE
- **Target Project:** Capstone Project Sistem Informasi — Telkom University
- **Date:** 13 November 2026

---

## Technical Debt Ledger

| Debt ID | Module / Component | Technical Debt Description | Root Cause | Impact | Effort Estimate | Priority | Resolution Strategy |
|---|---|---|---|---|:---:|:---:|---|
| **TD-01** | `Infrastructure / Frontend` | Ketergantungan CDN Eksternal untuk Bootstrap 5.3 & Icons | Penggunaan link jsDelivr CDN pada layout `app`, `admin`, dan `owner` | Risiko kegagalan render antarmuka jika server/klien berada di jaringan tertutup (intranet) atau CDN mengalami throttling/pemblokiran. | 1 Hari | **MEDIUM** | Unduh aset Bootstrap CSS/JS dan Bootstrap Icons ke dalam direktori `resources/`, lalu bundel secara lokal melalui pipeline Vite (`npm run build`). |
| **TD-02** | `DevOps / Scheduler` | Ketergantungan Eksekusi Scheduler pada Host OS (Windows Task Scheduler) | Laravel scheduler `schedule:run` memerlukan pemicu periodik per menit dari OS | Jika Task Scheduler Windows tidak dikonfigurasi saat deployment, pembersihan pesanan kedaluwarsa (`orders:cancel-expired`) dan backup harian (`database:backup`) tidak akan berjalan otomatis. | 0.5 Hari | **HIGH** | Dokumentasikan dan pasang Scheduled Task di server produksi Windows (`schtasks /create /tn "UyuyScheduler" /tr "php e:\Codex\artisan schedule:run" /sc minute /mo 1`). |
| **TD-03** | `Data Integrity / Storage` | Rekam Jejak Bukti Transfer Legacy Berformat URL Eksternal | Penggunaan URL dummy Unsplash pada data awal pengembangan/seeding | Rute privat file (`PaymentProofController`) menolak akses URL eksternal (HTTP 404). Admin/Pelanggan tidak dapat membuka berkas bukti lama yang berstatus legacy eksternal. | 1 Hari | **MEDIUM** | Jalankan `php artisan payments:secure-proofs` dan lakukan rekonsiliasi data legacy: unggah ulang file bukti lokal atau arsipkan record pembayaran lama. |
| **TD-04** | `Data Integrity / Audit` | Ketiadaan Status History pada 1 Record Pesanan Awal | Migrasi awal tidak menerapkan trigger atau listener otomatis untuk status history pada order dummy pertama | Satu pesanan historis di database tidak memiliki jejak kronologi status pada timeline pelanggan. | 0.5 Hari | **LOW** | Lakukan verifikasi data sebelum cut-off produksi; jangan membuat data sintetis sembarangan. Tandai record sebagai legacy data atau reset sebelum fase live. |
| **TD-05** | `Backend / Query Layer` | Agregasi SQL Rekapitulasi Berada Langsung di Controller | Ketiadaan Service/Repository Layer khusus untuk analitik bisnis Owner | `Owner\RecapController` memuat query SQL yang cukup panjang. Meskipun performa tinggi dan aman dari SQL Injection (parameter binding), keterbacaan kode berkurang. | 1-2 Hari | **LOW** | Ekstrak logika agregasi rekapitulasi ke dalam class service `App\Services\RecapReportService` atau `App\Actions\GeneratePoRecapAction`. |
| **TD-06** | `Configuration / Mail` | Mail Transport Default Menggunakan Driver `log` | Konfigurasi lokal belum menyertakan kredensial SMTP aktif | Fitur reset password (`/forgot-password`) berhasil membuat token, tetapi tautan reset ditulis ke `storage/logs/laravel.log` bukan terkirim ke inbox email pelanggan. | 0.5 Hari | **HIGH** | Konfigurasikan kredensial SMTP produksi (misal: Mailtrap, SES, Sendgrid, atau SMTP hosting) di `.env` produksi saat fase deployment. |
| **TD-07** | `Logistics / Operations` | Penentuan Ongkir dan Resi Bersifat Manual | Ketiadaan integrasi API ekspedisi pihak ketiga (RajaOngkir / Biteship) | Admin harus memeriksa biaya pengiriman secara manual di luar sistem dan menginput nomor resi/kurir saat mengubah status ke `Barang Dikirim`. | 2-3 Hari | **MEDIUM** | Pertahankan input manual untuk MVP Capstone, lalu jadwalkan integrasi API RajaOngkir untuk fase rilis komersial lanjutan. |
| **TD-08** | `Performance / Session` | Session dan Cache Menggunakan Driver Database | Standar default Laravel 12 tanpa Redis server lokal | Pada volume transaksi yang sangat tinggi, tabel `sessions` dan `cache` dapat menambah beban I/O pada MySQL. | 1 Hari | **LOW** | Cukup memadai untuk skala saat ini; pertimbangkan migrasi ke Redis jika konkurensi aktif melampaui 500 request/detik. |
| **TD-09** | `Testing / CI Pipeline` | Automated Test Suite Hanya Berjalan di SQLite In-Memory | Ketiadaan pipeline CI/CD yang terhubung ke instance MySQL nyata | Perilaku locking transaksional spesifik MySQL (seperti deadlock handling dan lock waiting timeout pada konkurensi paralel ekstrim) belum teruji secara otomatis di CI. | 2 Hari | **MEDIUM** | Tambahkan job GitHub Actions / GitLab CI yang menjalankan MySQL container service untuk menguji transaksi konkurensi tinggi. |

---

## Analisis Komponen Teknis: Keep, Refactor, Rebuild, Remove

| Komponen / Modul | Keputusan Arsitektur | Justifikasi Teknis | Tindakan yang Direkomendasikan |
|---|:---:|---|---|
| **Domain Actions** (`CreateOrderAction`, `VerifyPaymentAction`, `DecideCancellationAction`) | **KEEP** | Menerapkan Single Responsibility Principle, transaksi atomik, row-level locking, dan jejak audit trail yang solid. | Pertahankan. Logika sudah bersih, teruji, dan modular. |
| **Eloquent Models & Migrations** (`12 Models`, `16 Migrations`) | **KEEP** | Skema relasional sudah ternormalisasi, foreign keys lengkap dengan constraint aman (`restrict`/`cascade`), index query lengkap, dan relasi Eloquent terdefinisi jelas. | Pertahankan. Tidak memerlukan perombakan skema. |
| **Form Request Classes** (`CheckoutRequest`, `RegisterRequest`, dll.) | **KEEP** | Validasi input ketat, normalisasi nomor telepon Indonesia terpusat, regex kode pos 5 digit, dan pesan error ramah pengguna. | Pertahankan. |
| **Role-Based Access Control** (`CheckRole.php` & Route Groups) | **KEEP** | Middleware RBAC bekerja konsisten pada 53 rute; isolasi antara Customer, Admin, dan Owner terjaga ketat. | Pertahankan. |
| **Order Policy & Secure File Access** (`OrderPolicy`, `PaymentProofController`, `RefundProofController`) | **KEEP** | Mencegah IDOR secara menyeluruh. Bukti transfer dan refund dilindungi authorization policy dan disajikan via response stream privat dengan header sandboxed CSP. | Pertahankan. |
| **Design System Editorial Stone v2** (`uyuy-theme.css`, Vite assets) | **KEEP** | Hierarki visual multi-surface hangat, tipografi multi-weight proporsional, responsive di 4 breakpoint target (`1440px`, `1366px`, `768px`, `390px`), dan bebas sub-13px override. | Pertahankan. |
| **Scheduler & Console Commands** (`BackupDatabase`, `CancelExpiredOrders`) | **KEEP** | Fitur operasional penting untuk otomatisasi pembatalan order kedaluwarsa dan backup database terkompresi. | Aktifkan di Task Scheduler server produksi. |
| **Recap Controller** (`Owner\RecapController`) | **REFACTOR** | Query agregasi SQL langsung di controller berfungsi dengan baik dan efisien, namun akan lebih rapi jika dipisah ke Service/Action class. | Pindahkan ke `GeneratePoRecapAction` saat fase maintenance pasca-audit. |
| **External CDN Links** (Bootstrap CDN di `layouts/*.blade.php`) | **REFACTOR** | Masih mengandalkan CDN jsDelivr eksternal meskipun sudah diamankan SRI. | Self-host ke dalam bundle npm/Vite untuk deployment offline/intranet. |
| **Legacy Python Test Scripts** (`run_system_test.py`, `test_business_flows.py`) | **REMOVE** | Script lama yang membingungkan karena seluruh testing sudah dialihkan ke PHPUnit suite resmi. | Hapus file script Python tersebut agar tidak menimbulkan kerancuan bagi penguji. |

