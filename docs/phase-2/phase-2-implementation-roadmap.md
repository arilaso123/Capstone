# PETA JALAN IMPLEMENTASI PENYEMPURNAAN (IMPLEMENTATION ROADMAP)
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Fase:** Phase 2 Deliverable 9  
**Tanggal Penyusunan:** 13 November 2026  
**Penyusun:** Senior Full-Stack Engineer, Lead Frontend Engineer, QA Lead  
**Klasifikasi:** Urutan Kerja Teknis & Manajemen Ketergantungan (Work Breakdown Structure)  

---

## 1. PENDAHULUAN & STRATEGI EKSEKUSI

Peta jalan implementasi ini membagi pekerjaan perbaikan dan penyempurnaan antarmuka ke dalam **9 tahapan terstruktur (Milestone)**. Pendekatan bertahap (*incremental staged rollout*) diterapkan untuk memastikan bahwa perbaikan bug kritis diselesaikan terlebih dahulu, diikuti oleh penguatan fondasi sistem desain, lalu penyempurnaan per peran pengguna, dan diakhiri dengan verifikasi regresi penuh.

```
+---------------------------------------------------------------------------------------------------+
|                                 9 TAHAPAN IMPLEMENTASI PHASE 3 & 4                                |
+---------------------------------------------------------------------------------------------------+
|  M-01: Critical Bugfix (Resolusi ParseError Blade 500 pada Edit Produk Owner) [P0]                |
|  M-02: Offline Independence (Bundling Bootstrap 5.3 & Icons via Vite - Zero CDN) [P2]             |
|  M-03: Shared Design Foundation & Standardized Form Tokens [P2]                                   |
|  M-04: Public Storefront Usability Polish (Katalog empty state, counter, FAQ accordion) [P2/P3]   |
|  M-05: Authentication Refinement (Password visibility toggle) [P2]                                |
|  M-06: Customer Portal Usability Polish (Mobile tab swipe, clipboard feedback, help WA) [P2]     |
|  M-07: Admin Workspace Efficiency Polish (Lightbox image zoom, dropdown kurir ekspedisi) [P2/P3] |
|  M-08: Owner Workspace Polish (Pencarian pelanggan, catatan transparansi modal) [P2/P3]           |
|  M-09: Final Regression Testing, Responsive Validation & Production Verification [P0 Verification] |
+---------------------------------------------------------------------------------------------------+
```

---

## 2. DETAIL TAHAPAN KERJA & DEPENDENSI

---

### MILESTONE 1: CRITICAL BUGFIX (RESOLUSI PARSEERROR BLADE P0)
- **Tujuan:** Mengembalikan fungsi pengubahan produk parfum pada Owner Workspace yang saat ini crash dengan status HTTP 500.
- **Tindakan Teknis:**
  - Buka berkas [resources/views/owner/products/edit.blade.php](file:///e:/Codex/resources/views/owner/products/edit.blade.php).
  - Hapus sintaksis inline Blade `@if(...) ... @endif` di dalam atribut tag HTML input varian harga dan modal (baris 68 s/d 72).
  - Ubah menjadi ternary Blade bersih: `aria-describedby="{{ $errors->has('variants.'.$size.'.price') ? 'price-'.$size.'-error' : '' }}"`.
  - Pastikan blok foto produk `@if($product->image_path)` terkompilasi sempurna dengan penutup `<?php endif; ?>`.
- **File Terdampak:** `resources/views/owner/products/edit.blade.php`.
- **Estimasi Waktu:** 30 Menit.
- **Ketergantungan:** Tidak ada (Harus dieksekusi pertama kali).
- **Kriteria Lolos:** Rute `/owner/produk/1/edit` merespons HTTP 200 dan form dapat disubmit.

---

### MILESTONE 2: OFFLINE INDEPENDENCE & BUNDLING VITE (P2)
- **Tujuan:** Menghilangkan ketergantungan CDN eksternal `cdn.jsdelivr.net` agar aplikasi dapat dijalankan secara mandiri di intranet tanpa koneksi internet luar.
- **Tindakan Teknis:**
  - Instal dependensi lokal: `pnpm add bootstrap bootstrap-icons` (dieksekusi di luar sandbox).
  - Impor stylesheet dan font icons ke dalam `resources/css/app.css` dan skrip ke `resources/js/app.js`.
  - Jalankan `npm run build` untuk menghasilkan bundle terkompilasi di `public/build/`.
  - Hapus pemanggilan tag `<link href="https://cdn.jsdelivr.net/..." rel="stylesheet">` dari [resources/views/layouts/app.blade.php](file:///e:/Codex/resources/views/layouts/app.blade.php).
  - Perketat CSP header pada [app/Http/Middleware/SecurityHeaders.php](file:///e:/Codex/app/Http/Middleware/SecurityHeaders.php) dengan menghapus domain jsdelivr.
- **File Terdampak:** `package.json`, `resources/css/app.css`, `resources/js/app.js`, `resources/views/layouts/*.blade.php`, `SecurityHeaders.php`.
- **Estimasi Waktu:** 2 Jam.
- **Ketergantungan:** Node.js / Vite build environment.

---

### MILESTONE 3: SHARED DESIGN FOUNDATION & FORM TOKENS (P2)
- **Tujuan:** Menyeragamkan padding formulir, transisi fokus, dan membersihkan peringatan linter editor.
- **Tindakan Teknis:**
  - Buka [resources/css/uyuy-theme.css](file:///e:/Codex/resources/css/uyuy-theme.css).
  - Tambahkan properti standard `line-clamp: 2;` berdampingan dengan `-webkit-line-clamp: 2;` pada baris 1784 untuk menyelesaikan peringatan linter editor.
  - Standardisasi padding form control `padding: 10px 14px;` dan border focus ring 2px amber.
- **File Terdampak:** `resources/css/uyuy-theme.css`.
- **Estimasi Waktu:** 1 Jam.
- **Ketergantungan:** Milestone 2 selesai.

---

### MILESTONE 4: PUBLIC STOREFRONT POLISHING (P2 / P3)
- **Tujuan:** Meningkatkan kenyamanan pengunjung baru saat menjelajahi katalog dan membaca panduan PO.
- **Tindakan Teknis:**
  - `catalog/index.blade.php`: Tambahkan teks counter hasil filter dan perbaiki tampilan empty state dengan tombol reset filter.
  - `product/show.blade.php`: Tambahkan catatan jadwal PO berikutnya di bawah tombol submit disabled saat status periode PO ditutup.
  - `po-info/index.blade.php`: Poles daftar FAQ menjadi accordion minimalis bergaris tipis.
- **File Terdampak:** `resources/views/catalog/index.blade.php`, `resources/views/product/show.blade.php`, `resources/views/po-info/index.blade.php`.
- **Estimasi Waktu:** 2 Jam.
- **Ketergantungan:** Milestone 3 selesai.

---

### MILESTONE 5: AUTHENTICATION ENHANCEMENTS (P2)
- **Tujuan:** Mempermudah pengisian kata sandi pada formulir login dan registrasi.
- **Tindakan Teknis:**
  - Tambahkan tombol ikon mata (*password toggle*) di dalam container input password.
  - Tambahkan event listener ringan pada `resources/js/app.js` untuk mengganti atribut tipe input antara `password` dan `text`.
- **File Terdampak:** `resources/views/auth/login.blade.php`, `register.blade.php`, `resources/js/app.js`.
- **Estimasi Waktu:** 1 Jam.
- **Ketergantungan:** Milestone 3 selesai.

---

### MILESTONE 6: CUSTOMER PORTAL REFINEMENT (P2)
- **Tujuan:** Meningkatkan pengalaman berbelanja dan transparansi pembayaran pelanggan.
- **Tindakan Teknis:**
  - `layouts/customer.blade.php`: Terapkan aturan CSS swipe horizontal pada tab portal pelanggan untuk kenyamanan layar smartphone.
  - `customer/cart/index.blade.php`: Beri indikator visual aktif pada tombol simpan kuantitas saat angka diubah.
  - `customer/orders/payment.blade.php`: Tambahkan toast feedback visual "Tersalin!" saat mengklik tombol salin rekening BCA.
  - `customer/orders/payment.blade.php`: Tambahkan tombol pintasan kontak WhatsApp Admin untuk pesanan yang membutuhkan klarifikasi pembayaran.
- **File Terdampak:** `resources/views/layouts/customer.blade.php`, `resources/views/customer/cart/index.blade.php`, `resources/views/customer/orders/payment.blade.php`, `resources/js/app.js`.
- **Estimasi Waktu:** 2.5 Jam.
- **Ketergantungan:** Milestone 3 selesai.

---

### MILESTONE 7: ADMIN WORKSPACE EFFICIENCY POLISH (P2 / P3)
- **Tujuan:** Memaksimalkan kecepatan kerja staf operasional saat memproses antrean verifikasi dan pengiriman.
- **Tindakan Teknis:**
  - `layouts/admin.blade.php`: Ringkas kotak catatan wewenang di sidebar agar tombol Keluar terlihat di layar laptop 1366×768 tanpa scroll.
  - `admin/payments/verify.blade.php`: Implementasikan modal pop-up lightbox zoom bukti bayar in-page.
  - `admin/orders/show.blade.php`: Tambahkan dropdown pilihan kurir pengiriman populer (JNE, J&T, SiCepat, Paxel, GoSend).
- **File Terdampak:** `resources/views/layouts/admin.blade.php`, `resources/views/admin/payments/verify.blade.php`, `resources/views/admin/orders/show.blade.php`.
- **Estimasi Waktu:** 2.5 Jam.
- **Ketergantungan:** Milestone 3 selesai.

---

### MILESTONE 8: OWNER WORKSPACE POLISH (P2 / P3)
- **Tujuan:** Meningkatkan visibilitas data keuangan dan kemudahan pencarian pelanggan bagi pemilik usaha.
- **Tindakan Teknis:**
  - `owner/recap/index.blade.php`: Tambahkan teks keterangan transparan di bawah kartu modal belanja.
  - `owner/customers/index.blade.php`: Tambahkan input field pencarian cepat nama atau nomor telepon pelanggan.
- **File Terdampak:** `resources/views/owner/recap/index.blade.php`, `resources/views/owner/customers/index.blade.php`.
- **Estimasi Waktu:** 1.5 Jam.
- **Ketergantungan:** Milestone 1 & 3 selesai.

---

### MILESTONE 9: FINAL REGRESSION TESTING & PRODUCTION READINESS VERIFICATION
- **Tujuan:** Memastikan seluruh kriteria penerimaan (Acceptance Criteria) terpenuhi dan tidak ada regresi fungsional.
- **Tindakan Teknis:**
  - Jalankan build Vite produksi: `npm run build`.
  - Jalankan suite pengujian otomatis lengkap: `php artisan test` (Target: 31 passed, 256 assertions).
  - Jalankan pengujian browser menyeluruh pada 6 viewport (360px s/d 1920px).
  - Pastikan 0 console error dan nol overflow horizontal.
- **Estimasi Waktu:** 1.5 Jam.
- **Ketergantungan:** Milestone 1 s/d 8 selesai.

---

## 3. ESTIMASI TOTAL & ALOKASI WAKTU

| Kelompok Kerja | Cakupan Milestone | Total Estimasi Jam |
| :--- | :--- | :---: |
| **Bugfix Kritis & Infrastruktur** | Milestone 1 s/d 3 | **3.5 Jam** |
| **Penyempurnaan Halaman Pengguna**| Milestone 4 s/d 8 | **9.5 Jam** |
| **Verifikasi Regresi & Rilis** | Milestone 9 | **1.5 Jam** |
| **TOTAL WAKTU IMPLEMENTASI** | **9 Milestone** | **14.5 Jam Kerja** |

---

## 4. KESIMPULAN PETA JALAN

Peta jalan ini dirancang secara realistis dan berisiko rendah (*low-risk, high-impact*). Tidak ada perombakan basis data atau perombakan rute yang berpotensi memutus integrasi. Setelah persetujuan diberikan oleh pengguna, implementasi Phase 3 dapat langsung dimulai dari **Milestone 1**.
