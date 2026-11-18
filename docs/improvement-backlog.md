# BACKLOG PERBAIKAN & PENYEMPURNAAN SISTEM (IMPROVEMENT BACKLOG)
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Tanggal Penyusunan:** 13 November 2026  
**Penyusun:** Senior Software Architect, QA Lead, Senior Full-Stack Engineer  
**Klasifikasi Dokumen:** Rencana Kerja Teknis Terprioritas (Phase 1.5 Deliverable 5)

---

## 1. STRUKTUR & METODOLOGI PRIORITAS

Backlog perbaikan ini disusun berdasarkan hasil audit komprehensif pada Phase 1 dan rekonsiliasi Phase 1.5. Seluruh item dikelompokkan secara ketat menggunakan taksonomi prioritas rekayasa perangkat lunak:

- **P0 — Critical (Kritis):** Cacat keamanan fatal, kerusakan integritas data transaksi, kegagalan fungsi inti, atau celah bypass otorisasi. Harus diselesaikan sebelum sistem dapat digunakan dalam bentuk apa pun.
- **P1 — High (Tinggi / Kesiapan Operasional):** Ketergantungan konfigurasi lingkungan produksi dan scheduler sistem yang wajib diaktifkan saat deployment agar alur bisnis otomatis berjalan di server live.
- **P2 — Medium (Menengah / Ketahanan & Validasi Empiris):** Peningkatan kemandirian infrastruktur (offline intranet), sanitasi data historis, dan pengujian beban empiris.
- **P3 — Low (Rendah / Optimasi Jangka Panjang):** Pembersihan kode sumber lawas (*legacy cleanup*), refactoring modularitas, integrasi API pihak ketiga di masa mendatang, dan penyesuaian peringatan linter.

---

## 2. REKAPITULASI MATRIKS BACKLOG

| ID | Kategori | Item Masalah | Prioritas | Estimasi | Status |
| :---: | :---: | :--- | :---: | :---: | :---: |
| - | **P0** | *Tidak ada temuan cacat kritis (Zero P0 Vulnerabilities)* | **P0** | 0 jam | **SELESAI (CLEAN)** |
| **B-01** | Operasional | Konfigurasi SMTP Server Riil pada Environment Produksi | **P1** | 1 jam | Menunggu Deployment |
| **B-02** | Operasional | Pendaftaran Task Scheduler untuk `schedule:run` Otomatis | **P1** | 1 jam | Menunggu Server Host |
| **B-03** | Infrastruktur | Self-host Bootstrap 5.3 & Icons ke dalam Vite Bundle | **P2** | 3 jam | Terencana |
| **B-04** | Data Quality | Skrip Rekonsiliasi Bukti Pembayaran Seeder Legacy | **P2** | 2 jam | Terencana |
| **B-05** | QA / Perf | Eksekusi Pengujian Beban Konkuren Empiris (k6 / JMeter) | **P2** | 6 jam | Terencana di Staging |
| **B-06** | Arsitektur | Refactoring Kueri Agregasi Rekapitulasi ke Service/Action | **P3** | 4 jam | Terencana |
| **B-07** | Ekstensi | Integrasi API Ekspedisi Otomatis (RajaOngkir) | **P3** | 8 jam | Roadmap Masa Depan |
| **B-08** | Kebersihan | Pengarsipan Skrip Pengujian Lawas Berbasis Python | **P3** | 30 menit | Terencana |
| **B-09** | Frontend | Resolusi Peringatan Linter CSS `line-clamp` Standard | **P3** | 15 menit | Terencana |

---

## 3. RINCIAN ITEM BACKLOG

---

### ITEM P0 — CRITICAL (KRITIS)
> **STATUS:** **TIDAK ADA ITEM P0.**  
> **Justifikasi Auditor:**  
> Seluruh 22 Kebutuhan Fungsional (FR-01 s/d FR-22) telah terbukti terimplementasi. Seluruh 31 pengujian otomatis PHPUnit (256 asersi) berhasil lulus 100%. Transaksi dilindungi mekanisme ACID dan penguncian pesimis (`lockForUpdate`, `sharedLock`), data pelanggan diisolasi mutlak oleh `OrderPolicy`, berkas transaksi diamankan di storage privat dengan sandboxed CSP, dan sistem kebal terhadap manipulasi harga sisi klien.

---

### ITEM P1 — HIGH (KESIAPAN OPERASIONAL DEPLOYMENT)

#### B-01: Konfigurasi SMTP Server Riil pada Environment Produksi
- **Masalah:** Konfigurasi driver pengiriman surat saat ini menggunakan driver log (`MAIL_MAILER=log`).
- **Bukti:** Berkas `config/mail.php` dan temuan audit [technical-audit-report.md](file:///e:/Codex/docs/technical-audit-report.md) (Finding F-02).
- **Dampak:** Notifikasi email reset password pelanggan ([PasswordResetLinkController.php](file:///e:/Codex/app/Http/Controllers/Auth/PasswordResetLinkController.php)) hanya tertulis pada file log lokal `storage/logs/laravel.log` dan tidak terkirim secara fisik ke inbox email pengguna di lingkungan live.
- **Prioritas:** **P1 (High)**
- **Solusi yang Direkomendasikan:** Pada berkas `.env` server produksi, isi kredensial SMTP yang valid (seperti Brevo, Mailgun, Google Workspace SMTP, atau Amazon SES):
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=smtp-relay.brevo.com
  MAIL_PORT=587
  MAIL_USERNAME=user@uyuyjastip.com
  MAIL_PASSWORD=secret_smtp_key
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS="no-reply@uyuyjastip.com"
  MAIL_FROM_NAME="Uyuy Jastip"
  ```
- **Risiko Apabila Tidak Diperbaiki:** Pelanggan yang lupa kata sandi tidak dapat memulihkan akunnya secara mandiri.
- **Ketergantungan:** Kredensial akun SMTP penyedia pihak ketiga.

---

#### B-02: Pendaftaran Task Scheduler untuk `schedule:run` Otomatis
- **Masalah:** Perintah otomatis pembatalan pesanan yang melewati batas pembayaran 24 jam (`orders:expire-unpaid`) dan pencadangan database harian (`database:backup`) belum berjalan otomatis jika tidak ada daemon scheduler di level OS.
- **Bukti:** Berkas `routes/console.php:12-16` dan temuan audit [technical-audit-report.md](file:///e:/Codex/docs/technical-audit-report.md) (Finding F-01).
- **Dampak:** Pesanan kedaluwarsa yang tidak dibayar akan tetap berstatus `Menunggu Pembayaran` dan tidak dibatalkan otomatis oleh sistem kecuali admin/operator menjalankan perintah artisan secara manual.
- **Prioritas:** **P1 (High)**
- **Solusi yang Direkomendasikan:** Daftarkan perintah Laravel Scheduler pada sistem operasi server target:
  - **Pada Windows Server (Task Scheduler):**
    Buat Scheduled Task yang berjalan setiap 1 menit:
    ```cmd
    schtasks /create /sc minute /mo 1 /tn "UyuyJastipScheduler" /tr "C:\php\php.exe E:\Codex\artisan schedule:run"
    ```
  - **Pada Linux Server (Cron):**
    Tambahkan baris berikut pada crontab:
    ```cron
    * * * * * cd /var/www/uyuy-jastip && php artisan schedule:run >> /dev/null 2>&1
    ```
- **Risiko Apabila Tidak Diperbaiki:** Rekapitulasi pesanan terganggu oleh pesanan hangus yang tidak pernah diselesaikan dan backup database otomatis harian tidak tereksekusi.
- **Ketergantungan:** Hak akses Administrator/Root pada mesin server hosting.

---

### ITEM P2 — MEDIUM (KETAHANAN & VALIDASI EMPIRIS)

#### B-03: Self-host Bootstrap 5.3 & Icons ke dalam Vite Bundle
- **Masalah:** Halaman aplikasi masih mengimpor CSS framework Bootstrap 5.3 dan Bootstrap Icons melalui CDN pihak ketiga (`cdn.jsdelivr.net`).
- **Bukti:** [resources/views/layouts/app.blade.php](file:///e:/Codex/resources/views/layouts/app.blade.php) dan middleware [SecurityHeaders.php:25-27](file:///e:/Codex/app/Http/Middleware/SecurityHeaders.php#L25-L27).
- **Dampak:** Membutuhkan akses internet publik saat browser merender aplikasi. Jika CDN mengalami gangguan atau server dijalankan pada lingkungan intranet terisolasi, styling tata letak dasar berisiko mengalami malfungsi.
- **Prioritas:** **P2 (Medium)**
- **Solusi yang Direkomendasikan:**
  1. Pasang paket melalui package manager:
     ```bash
     pnpm add bootstrap bootstrap-icons
     ```
  2. Impor langsung ke dalam `resources/css/app.css` dan `resources/js/app.js`.
  3. Kompilasi menggunakan Vite (`npm run build`).
  4. Hapus ketergantungan CDN dari template Blade dan perketat CSP pada `SecurityHeaders.php` dengan menghapus `https://cdn.jsdelivr.net`.
- **Risiko Apabila Tidak Diperbaiki:** Ketergantungan jaringan eksternal pihak ketiga saat memuat halaman web.
- **Ketergantungan:** Node.js / Vite build environment.

---

#### B-04: Skrip Rekonsiliasi Bukti Pembayaran Seeder Legacy
- **Masalah:** Beberapa entri pesanan dummy pada seed awal database masih menggunakan URL gambar Unsplash eksternal (`https://images.unsplash.com/...`), bukan berkas riil di storage lokal privat.
- **Bukti:** [PaymentProofController.php:33](file:///e:/Codex/app/Http/Controllers/PaymentProofController.php#L33) secara sengaja memblokir URL HTTP (`abort_if(preg_match('/^https?:\/\//i', $proofPath) === 1, 404);`).
- **Dampak:** Bukti pembayaran pada data dummy pesanan seeder lama menghasilkan halaman 404 saat diklik oleh admin/owner (meskipun pada transaksi baru pelanggan berfungsi normal).
- **Prioritas:** **P2 (Medium)**
- **Solusi yang Direkomendasikan:** Buat skrip migrasi data satu kali (*one-off artisan command*) `payments:reconcile-legacy-proofs` yang menggantikan URL eksternal dummy lama dengan contoh berkas gambar dummy lokal di `storage/app/private/payments/`.
- **Risiko Apabila Tidak Diperbaiki:** Mengurangi estetika data demo saat presentasi atau audit visual terhadap data transaksi lawas.
- **Ketergantungan:** Tidak ada.

---

#### B-05: Eksekusi Pengujian Beban Konkuren Empiris (k6 / JMeter)
- **Masalah:** Kebutuhan non-fungsional NFR-07 (mendukung 200 pengguna aktif dengan waktu respons < 2 detik) secara arsitektur telah dioptimalkan (caching periode PO, indeks gabungan basis data, locking non-blocking, ULID), namun belum memiliki bukti angka hasil pengujian beban nyata (*empirical benchmark evidence*).
- **Bukti:** [docs/requirements-traceability-matrix.md](file:///e:/Codex/docs/requirements-traceability-matrix.md) (NFR-07 berstatus `NOT VERIFIED`).
- **Dampak:** Kapasitas puncak server riil (CPU/RAM/MySQL max connections) belum terukur secara kuantitatif.
- **Prioritas:** **P2 (Medium)**
- **Solusi yang Direkomendasikan:** Susun skrip uji beban menggunakan k6 yang mensimulasikan:
  - 200 Virtual Users (VU) menjelajahi katalog publik.
  - 50 VU melakukan checkout secara bersamaan.
  - Catat P95 dan P99 response time serta error rate pada server staging dengan database MySQL riil.
- **Risiko Apabila Tidak Diperbaiki:** Kurangnya data kuantitatif untuk dokumen bab pengujian skripsi/capstone.
- **Ketergantungan:** Server staging dengan database MySQL aktif dan tool pengujian beban (k6).

---

### ITEM P3 — LOW (OPTIMASI & PEMELIHARAAN JANGKA PANJANG)

#### B-06: Refactoring Kueri Agregasi Rekapitulasi ke Service / Action Class
- **Masalah:** Controller [Owner/RecapController.php](file:///e:/Codex/app/Http/Controllers/Owner/RecapController.php) memuat kueri agregasi SQL yang cukup panjang langsung di dalam method controller.
- **Bukti:** Pengecekan kode controller Owner.
- **Dampak:** Pelanggaran prinsip *Single Responsibility* (*Fat Controller*), kueri tidak dapat digunakan ulang secara mudah di luar controller (misalnya untuk ekspor PDF/Excel di masa depan).
- **Prioritas:** **P3 (Low)**
- **Solusi yang Direkomendasikan:** Ekstrak logika agregasi data keuangan dan perhitungan margin keuntungan ke dalam kelas service khusus `App\Services\RecapCalculationService` atau `App\Actions\GenerateFinancialRecapAction`.
- **Risiko Apabila Tidak Diperbaiki:** Sedikit menurunkan maintainability kode saat ada kebutuhan penambahan laporan baru.
- **Ketergantungan:** Tidak ada.

---

#### B-07: Integrasi API Ekspedisi Otomatis (RajaOngkir / Kurir API)
- **Masalah:** Saat ini input tarif pengiriman (`shipping_cost`), nama kurir (`courier`), dan nomor resi (`tracking_number`) dilakukan secara manual oleh staf Admin Operasional saat pesanan masuk status `Barang Dikirim`.
- **Bukti:** [Admin/OrderController.php:60-70](file:///e:/Codex/app/Http/Controllers/Admin/OrderController.php#L60-L70).
- **Dampak:** Proses operasional membutuhkan input manual staf.
- **Prioritas:** **P3 (Low)**
- **Solusi yang Direkomendasikan:** Di masa depan, hubungkan form checkout dan admin dengan API layanan logistik (seperti RajaOngkir / Biteship) untuk perhitungan tarif otomatis berdasarkan asal dan tujuan pengiriman.
- **Risiko Apabila Tidak Diperbaiki:** Tidak ada; mekanisme manual saat ini sudah sesuai dengan rancangan proposal awal Capstone.
- **Ketergantungan:** Langganan API pihak ketiga (RajaOngkir).

---

#### B-08: Pengarsipan Skrip Pengujian Lawas Berbasis Python
- **Masalah:** Direktori root masih menyimpan skrip pengujian lawas berbasis Python (`run_system_test.py` dan `test_business_flows.py`) yang tidak lagi sinkron dengan suite pengujian resmi PHPUnit dan sempat menimbulkan perbedaan angka skenario pengujian (52 skenario Python vs 31 pengujian PHPUnit).
- **Bukti:** Berkas root `run_system_test.py` dan `test_business_flows.py`.
- **Dampak:** Berpotensi membingungkan pengembang baru atau auditor eksternal mengenai runner pengujian resmi proyek.
- **Prioritas:** **P3 (Low)**
- **Solusi yang Direkomendasikan:** Pindahkan kedua skrip tersebut ke folder `archive/legacy-tests/` atau hapus seluruhnya dari repositori aktif.
- **Risiko Apabila Tidak Diperbaiki:** Redundansi berkas non-kritis di repositori.
- **Ketergantungan:** Tidak ada.

---

#### B-09: Resolusi Peringatan Linter CSS `line-clamp` Standard
- **Masalah:** Linter editor menampilkan peringatan vendor prefix untuk `line-clamp` pada stylesheet tema.
- **Bukti:** [resources/css/uyuy-theme.css:1784](file:///e:/Codex/resources/css/uyuy-theme.css#L1784).
- **Dampak:** Timbulnya 1 warning minor pada tab *Problems* editor kode (tanpa dampak fungsional di browser modern).
- **Prioritas:** **P3 (Low)**
- **Solusi yang Direkomendasikan:** Tambahkan properti standard `line-clamp: 2;` berdampingan dengan `-webkit-line-clamp: 2;`.
- **Risiko Apabila Tidak Diperbaiki:** Tidak ada risiko fungsional.
- **Ketergantungan:** Tidak ada.

---

## 4. KESIMPULAN & TAHAPAN SELANJUTNYA

Dengan nol temuan berstatus P0, sistem **Uyuy Jastip** berada dalam kondisi teknis yang sangat sehat (*Production-Grade*). 

Tahapan perbaikan yang disarankan untuk dieksekusi pertama kali adalah pemenuhan kebutuhan **P1 (B-01 & B-02)** saat deployment server, disusul oleh implementasi **P2 (B-03)** untuk kemandirian penuh terhadap aset CDN. Proyek siap melangkah ke tahap implementasi perbaikan terencana dengan persetujuan pengguna.
