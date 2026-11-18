# REGISTER ISU & TEMUAN AUDIT FRONTEND (ISSUE REGISTER)
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Fase:** Phase 2 Deliverable 10  
**Tanggal Pemutakhiran:** 13 November 2026  
**Auditor:** Quality Assurance Lead, Lead UI/UX Designer, Senior Full-Stack Engineer  
**Klasifikasi:** Daftar Masalah Terkonsolidasi (Single Source of Truth)  

---

## 1. PENDAHULUAN & TAKSONOMI TINGKAT KEPARAHAN

Register ini mengonsolidasikan seluruh temuan cacat fungsional, kelemahan visual, titik gesekan alur (*friction points*), kendala responsivitas, dan isu aksesibilitas yang teridentifikasi selama audit Phase 2.

Taksonomi:
- **Critical (P0):** Kerusakan fungsional yang menggagalkan proses bisnis utama atau memicu HTTP status 500.
- **High (P1):** Hambatan alur pengguna atau dependensi konfigurasi operasional server.
- **Medium (P2):** Gangguan kenyamanan penggunaan, inkonsistensi komponen, atau ketergantungan aset luar.
- **Low (P3):** Peningkatan mikro-interaksi, peringatan linter, atau penyempurnaan estetika sekunder.

---

## 2. TABEL REGISTER ISU TERKONSOLIDASI

| Issue ID | Page / Area | Category | Description | Severity | Priority | Evidence | Proposed Action | Status |
| :---: | :---| :---: | :---| :---: | :---: | :---| :---| :---: |
| **ISS-01** | `/owner/produk/{id}/edit` | Functional / Blade | Halaman edit produk mengalami crash fatal HTTP 500 karena ParseError syntax error akibat penulisan inline `@if` di atribut tag Blade | **Critical** | **P0** | `storage/logs/laravel.log`: ParseError unexpected "endif" | Refactor atribut error pada `owner/products/edit.blade.php` menggunakan operator ternary Blade bersih | **OPEN** |
| **ISS-02** | `/pelanggan/pesanan/{id}/pembayaran` | Data Quality / Test | Data seeder pesanan memiliki batas pembayaran lampau (`2026-10-30`), memicu pengalihan (*redirect*) ke detail pesanan saat pengujian form bukti transfer | **High** | **P1** | `inspection-results.json` & DB query order 1 | Siapkan skrip seeder aktif atau transaksi uji live dengan deadline masa depan untuk verifikasi form upload | **OPEN** |
| **ISS-03** | Global Layouts (`app.blade.php`) | Infrastructure | Template dasar masih memuat CSS Bootstrap 5.3 & Bootstrap Icons dari CDN eksternal `cdn.jsdelivr.net` | **Medium** | **P2** | `layouts/app.blade.php` tag link CDN | Pasang `bootstrap` dan `bootstrap-icons` via package manager dan bundel mandiri via Vite (Zero CDN) | **OPEN** |
| **ISS-04** | `/admin/pembayaran/{id}/verifikasi` | Usability / Workflow | Pratinjau gambar bukti transfer membuka tab browser baru, memperlambat staf saat memeriksa banyak antrean | **Medium** | **P2** | `evidence/admin/admin-payment-verification-desktop.png` | Tambahkan modal pop-up lightbox zoom in-page pada gambar bukti bayar | **OPEN** |
| **ISS-05** | `/pelanggan/dashboard` | Responsive Mobile | Tab navigasi portal pelanggan di layar smartphone sempit berisiko bertumpuk secara vertikal jika teks panjang | **Medium** | **P2** | `customer/dashboard.blade.php` | Tambahkan styling CSS swipe horizontal (`overflow-x: auto`) pada bilah tab portal mobile | **OPEN** |
| **ISS-06** | `/pelanggan/pesanan/{id}/pembayaran` | Usability / Feedback | Tombol "Salin Nomor Rekening BCA" belum memiliki konfirmasi visual mikro (*toast feedback*) saat diklik | **Medium** | **P2** | `customer/orders/payment.blade.php` | Ubah teks tombol menjadi "Tersalin!" selama 2 detik saat clipboard berhasil disalin | **OPEN** |
| **ISS-07** | `/login` & `/register` | Usability / Auth | Input formulir kata sandi belum memiliki tombol kontrol pengalih tampilan (*password visibility toggle*) | **Medium** | **P2** | `evidence/auth/auth-login-desktop.png` | Tambahkan tombol ikon mata interaktif untuk menampilkan/menyembunyikan teks password | **OPEN** |
| **ISS-08** | `/katalog` | Usability / Catalog | Saat filter aroma tidak menemukan produk, pesan kosong hanya berupa alert Bootstrap standar tanpa tombol reset | **Medium** | **P2** | `catalog/index.blade.php` | Poles tampilan empty state menjadi kartu mineral ramah dilengkapi tombol "Reset Filter" | **OPEN** |
| **ISS-09** | `/owner/rekapitulasi` | Usability / Data | Kartu "Modal tercatat" menampilkan Rp 0 karena varian produk historis belum diinput harga modalnya oleh Owner | **Medium** | **P2** | `evidence/owner/owner-recap-desktop.png` | Tambahkan teks keterangan transparan bahwa modal dihitung dari produk yang memiliki data harga modal | **OPEN** |
| **ISS-010**| `/owner/pelanggan` | Usability / Search | Tabel direktori pelanggan belum memiliki kolom filter pencarian cepat nama atau nomor kontak pelanggan | **Medium** | **P2** | `evidence/owner/owner-customers-desktop.png` | Tambahkan field input pencarian cepat pada bilah atas tabel pelanggan | **OPEN** |
| **ISS-011**| `resources/css/uyuy-theme.css:1784` | Code Quality / Lint | Peringatan linter editor CSS mengenai properti standard vendor-prefix `line-clamp` | **Low** | **P3** | VS Code Problems Tab (Line 1784) | Tambahkan properti standar `line-clamp: 2;` berdampingan dengan `-webkit-line-clamp: 2;` | **OPEN** |
| **ISS-012**| `/admin/pesanan/{id}` | Usability / Form | Input nama kurir pengiriman saat ini berupa teks manual bebas, rawan kesalahan ketik nama ekspedisi | **Low** | **P3** | `evidence/admin/admin-order-detail-desktop.png` | Sediakan dropdown pilihan cepat ekspedisi populer (JNE, J&T, SiCepat, Paxel, GoSend) | **OPEN** |
| **ISS-013**| `/produk/{id}` | Usability / Info | Saat periode PO ditutup, tombol submit bertuliskan "PO Ditutup" tanpa keterangan kapan PO berikutnya dibuka | **Low** | **P3** | `product/show.blade.php` | Tambahkan catatan jadwal pembukaan PO berikutnya di bawah tombol disabled | **OPEN** |
| **ISS-014**| `/informasi-po` | Responsive / Mobile | Daftar pertanyaan FAQ di bagian bawah halaman masih berupa teks panjang statis tanpa accordion | **Low** | **P3** | `po-info/index.blade.php` | Terapkan komponen accordion minimalis khas Editorial Stone untuk segmen FAQ | **OPEN** |
| **ISS-015**| Global Accessibility | Accessibility | Beberapa gambar katalog produk memiliki teks alternatif (`alt`) yang terlalu singkat | **Low** | **P3** | Template Blade katalog | Perkaya teks alt menjadi deskriptif mencakup nama varian dan kategori aroma produk | **OPEN** |

---

## 3. RINGKASAN DISTRIBUSI TEMUAN

```
+-----------------------------------------------------------------------------+
|                        DISTRIBUSI TINGKAT KEPARAHAN                         |
+-----------------------------------------------------------------------------+
|  P0 — Critical (Crash / Broken Functionality) : 1 Temuan (6.7%)             |
|  P1 — High (Operational / Test Readiness)     : 1 Temuan (6.7%)             |
|  P2 — Medium (Usability / Offline Bundling)   : 8 Temuan (53.3%)            |
|  P3 — Low (Polishing / Minor Enhancements)    : 5 Temuan (33.3%)            |
|  TOTAL TEMUAN TERDAFTAR                       : 15 Temuan                   |
+-----------------------------------------------------------------------------+
```

---

## 4. KESIMPULAN REGISTER ISU

Dengan hanya **1 isu kritis P0** (sintaksis Blade pada edit produk) dan **1 isu operasional P1** (tanggal seeder), kondisi kesehatan repositori Uyuy Jastip tergolong sangat prima. Seluruh 15 isu di atas telah memiliki solusi teknis terpetakan dan siap diselesaikan secara bertahap pada Phase 3 sesuai Milestone Roadmap.
