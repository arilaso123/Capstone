# CETAK BIRU PENYEMPURNAAN FRONTEND (IMPROVEMENT BLUEPRINT)
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Fase:** Phase 2 Deliverable 7 (Dokumen Induk Persiapan Phase 3)  
**Tanggal Penyusunan:** 13 November 2026  
**Arsitek Solusi:** Lead UI/UX Designer, Senior Frontend Engineer, Software Architect  
**Klasifikasi:** Cetak Biru Spesifikasi Teknis Implementasi Frontend  

---

## 1. TUJUAN & BATASAN IMPLEMENTASI (IMPLEMENTATION BOUNDARIES)

Dokumen ini merupakan **panduan kerja teknis presisi** yang akan dieksekusi pada Phase 3. Seluruh rekomendasi dirancang untuk meningkatkan kualitas visual dan efisiensi pengguna tanpa merusak logika backend yang telah teruji:

1. **Templating Murni Laravel Blade:** Tidak menggunakan SPA framework (React, Vue, atau Next.js). Seluruh tampilan tetap dirender di sisi server menggunakan Blade.
2. **Preservasi Rute & Kontrak HTTP:** Seluruh 53 rute web, nama rute (`route('...')`), URI, dan HTTP method (GET, POST, PUT, DELETE) dipertahankan 100%.
3. **Integritas Formulir & CSRF:** Seluruh formulir mempertahankan token `@csrf`, input tersembunyi `@method('PUT')`, dan atribut nama input (`name="..."`) yang dipersyaratkan oleh FormRequest controller.
4. **Keamanan & Otorisasi Mutlak:** Middleware `CheckRole` dan kebijakan otorisasi `OrderPolicy` tetap menjadi pagar pembatas hak akses.
5. **Preservasi Atribut JS Kritis:** Atribut `data-prevent-double-submit="true"` dan kelas styling validasi tetap dipertahankan.

---

## 2. CETAK BIRU DETAIL PER HALAMAN (PAGE-BY-PAGE BLUEPRINT)

---

### AREA 1: PUBLIC STOREFRONT

#### 1.1. Halaman Beranda (`/`) — [resources/views/home.blade.php](file:///e:/Codex/resources/views/home.blade.php)
- **Keputusan:** **KEEP (DIPERTAHANKAN PENUH)**
- **Rencana Penyempurnaan:** Tambahkan padding horizontal adaptif pada seksi hero di layar lebar `>= 1600px` (`padding-inline: clamp(2rem, 4vw, 5rem);`) untuk menjaga keseimbangan komposisi.
- **Komponen Terdampak:** `resources/views/home.blade.php`, `resources/css/uyuy-theme.css`.
- **Prioritas:** **P3 (Low)**
- **Hasil yang Diharapkan:** Tampilan hero beranda tetap proporsional dan elegan pada monitor ultra-wide 1920px.

#### 1.2. Halaman Katalog Produk (`/katalog`) — [resources/views/catalog/index.blade.php](file:///e:/Codex/resources/views/catalog/index.blade.php)
- **Keputusan:** **POLISH (PENYEMPURNAAN USABILITY)**
- **Rencana Penyempurnaan:**
  1. Tambahkan teks penghitung hasil pencarian di bawah filter aroma: `Menampilkan {{ $products->total() }} produk`.
  2. Perbaiki blok *empty state* ketika filter tidak menemukan hasil: ganti div alert standar dengan komponen mineral card yang ramah dilengkapi tombol "Reset Filter & Tampilkan Semua Produk".
- **Komponen Terdampak:** `resources/views/catalog/index.blade.php`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Pengunjung yang melakukan pencarian spesifik tidak mengalami kebuntuan navigasi.

#### 1.3. Halaman Detail Produk (`/produk/{id}`) — [resources/views/product/show.blade.php](file:///e:/Codex/resources/views/product/show.blade.php)
- **Keputusan:** **POLISH (PENYEMPURNAAN INFORMASI PO)**
- **Rencana Penyempurnaan:** Saat periode PO tidak aktif (`$activePo === null`), tombol submit bertuliskan "PO Ditutup (Disabled)" dan di bawahnya ditambahkan teks informasi: *"Pemesanan akan dibuka kembali pada periode berikutnya. Ikuti jadwal PO di media sosial kami."*
- **Komponen Terdampak:** `resources/views/product/show.blade.php`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Calon pembeli tidak bingung mengapa tombol keranjang tidak dapat diklik.

#### 1.4. Halaman Informasi Pre-Order (`/informasi-po`) — [resources/views/po-info/index.blade.php](file:///e:/Codex/resources/views/po-info/index.blade.php)
- **Keputusan:** **POLISH (ACCORDION FAQ MOBILE)**
- **Rencana Penyempurnaan:** Ubah daftar tanya-jawab statis di bagian bawah halaman menjadi komponen Accordion khas Editorial Stone bergaris tipis untuk menghemat ruang vertikal di layar smartphone.
- **Komponen Terdampak:** `resources/views/po-info/index.blade.php`.
- **Prioritas:** **P3 (Low)**
- **Hasil yang Diharapkan:** Halaman panduan pre-order tampil lebih ringkas dan mudah dipindai di ponsel.

---

### AREA 2: AUTHENTICATION

#### 2.1. Halaman Login & Register — `resources/views/auth/login.blade.php` & `register.blade.php`
- **Keputusan:** **POLISH (PASSWORD TOGGLE CONTROL)**
- **Rencana Penyempurnaan:** Tambahkan tombol ikon mata kecil di sisi kanan field input password untuk melihat/menyembunyikan teks kata sandi via Vanilla JS.
- **Komponen Terdampak:** `resources/views/auth/login.blade.php`, `register.blade.php`, `resources/js/app.js`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Menekan risiko salah ketik kata sandi saat pengguna mendaftar melalui smartphone.

---

### AREA 3: CUSTOMER PORTAL

#### 3.1. Dashboard Pelanggan (`/pelanggan/dashboard`) — [resources/views/customer/dashboard.blade.php](file:///e:/Codex/resources/views/customer/dashboard.blade.php)
- **Keputusan:** **KEEP (DIPERTAHANKAN PENUH)**
- **Rencana Penyempurnaan:** Pastikan bilah tab portal di ponsel (`.customer-portal-nav`) memiliki CSS `overflow-x: auto; white-space: nowrap;` agar dapat di-swipe mulus tanpa pembungkusan vertikal ganda.
- **Komponen Terdampak:** `resources/views/layouts/customer.blade.php`, `resources/css/uyuy-theme.css`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Tab portal pelanggan di layar smartphone tampil rapi dan nyaman dinavigasi.

#### 3.2. Keranjang Belanja (`/pelanggan/keranjang`) — [resources/views/customer/cart/index.blade.php](file:///e:/Codex/resources/views/customer/cart/index.blade.php)
- **Keputusan:** **POLISH (INTERAKSI KUANTITAS KERANJANG)**
- **Rencana Penyempurnaan:** Berikan feedback visual aktif (berubah warna amber) pada tombol simpan kuantitas ketika angka di dalam input jumlah diubah oleh pelanggan.
- **Komponen Terdampak:** `resources/views/customer/cart/index.blade.php`, `resources/js/app.js`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Pelanggan menyadari bahwa perubahan kuantitas perlu disimpan sebelum melanjutkan ke checkout.

#### 3.3. Halaman Unggah Bukti Transfer (`/pelanggan/pesanan/{id}/pembayaran`) — [resources/views/customer/orders/payment.blade.php](file:///e:/Codex/resources/views/customer/orders/payment.blade.php)
- **Keputusan:** **POLISH (CLIPBOARD FEEDBACK & BANTUAN WA)**
- **Rencana Penyempurnaan:**
  1. Ubah teks tombol "Salin Nomor Rekening" menjadi "Tersalin!" selama 2 detik setelah diklik.
  2. Tambahkan tombol tautan cepat *"Bantuan WhatsApp Admin"* jika pelanggan mengalami kendala transfer sebelum batas waktu berakhir.
- **Komponen Terdampak:** `resources/views/customer/orders/payment.blade.php`, `resources/js/app.js`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Pelanggan mendapatkan konfirmasi jelas saat menyalin rekening bank dan memiliki saluran bantuan langsung.

---

### AREA 4: ADMIN OPERATIONAL WORKSPACE

#### 4.1. Dashboard Operasional Admin (`/admin/dashboard`) — [resources/views/admin/dashboard.blade.php](file:///e:/Codex/resources/views/admin/dashboard.blade.php)
- **Keputusan:** **POLISH (SIDEBAR VERTICAL SPACE REFINEMENT)**
- **Rencana Penyempurnaan:** Ringkas kotak teks catatan batasan wewenang di sidebar agar tombol "Keluar" selalu terlihat di atas lipatan layar laptop resolusi 1366×768 tanpa perlu scroll vertikal pada sidebar.
- **Komponen Terdampak:** `resources/views/layouts/admin.blade.php`, `resources/css/uyuy-theme.css`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Ergonomi kerja staf operasional di laptop meningkat signifikan.

#### 4.2. Halaman Verifikasi Pembayaran (`/admin/pembayaran/{id}/verifikasi`) — [resources/views/admin/payments/verify.blade.php](file:///e:/Codex/resources/views/admin/payments/verify.blade.php)
- **Keputusan:** **POLISH (IN-APP IMAGE LIGHTBOX PREVIEW)**
- **Rencana Penyempurnaan:** Sediakan modal pop-up pratinjau gambar bukti transfer in-page saat thumbnail bukti bayar diklik, sehingga staf tidak perlu membuka tab browser baru.
- **Komponen Terdampak:** `resources/views/admin/payments/verify.blade.php`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Mempercepat waktu pemeriksaan bukti transfer oleh staf admin.

#### 4.3. Halaman Detail Pesanan Admin (`/admin/pesanan/{id}`) — [resources/views/admin/orders/show.blade.php](file:///e:/Codex/resources/views/admin/orders/show.blade.php)
- **Keputusan:** **POLISH (DROPDOWN KURIR EKSPEDISI STANDAR)**
- **Rencana Penyempurnaan:** Tambahkan dropdown pilihan cepat kurir populer (JNE, J&T Express, SiCepat, Paxel, GoSend, GrabExpress, Lainnya) pada form input pengiriman pesanan.
- **Komponen Terdampak:** `resources/views/admin/orders/show.blade.php`.
- **Prioritas:** **P3 (Low)**
- **Hasil yang Diharapkan:** Mengurangi kesalahan ketik nama kurir pada data resi pengiriman.

---

### AREA 5: OWNER EXECUTIVE WORKSPACE

#### 5.1. Dashboard Eksekutif Owner (`/owner/dashboard`) — [resources/views/owner/dashboard.blade.php](file:///e:/Codex/resources/views/owner/dashboard.blade.php)
- **Keputusan:** **KEEP (DIPERTAHANKAN PENUH)**
- **Rencana Penyempurnaan:** Tidak ada perubahan struktural; kartu KPI dan kontrol PO aktif telah berfungsi optimal.
- **Prioritas:** **P3 (Low)**

#### 5.2. Halaman Ubah Produk Parfum (`/owner/produk/{id}/edit`) — [resources/views/owner/products/edit.blade.php](file:///e:/Codex/resources/views/owner/products/edit.blade.php)
- **Keputusan:** **REFACTOR (P0 CRITICAL BUG FIX)**
- **Masalah:** Halaman saat ini mengalami crash total **HTTP 500 ParseError (unexpected token "endif")**.
- **Rencana Penyempurnaan Teknis:**
  1. Hapus seluruh inline `@if(...) ... @endif` yang disisipkan di dalam atribut tag HTML pada baris 68 s/d 72.
  2. Ganti dengan operator ternary Blade bersih:
     ```blade
     aria-describedby="{{ $errors->has('variants.'.$size.'.price') ? 'price-'.$size.'-error' : '' }}"
     ```
  3. Pastikan blok `@if($product->image_path)` memiliki pasangan `@endif` yang terkompilasi secara valid.
- **Komponen Terdampak:** `resources/views/owner/products/edit.blade.php`.
- **Prioritas:** **P0 (Critical)**
- **Hasil yang Diharapkan:** Halaman edit produk terbuka dengan status HTTP 200, form terisi lengkap data produk, dan Owner dapat memperbarui harga varian, modal, dan foto parfum tanpa error.

#### 5.3. Halaman Rekapitulasi PO (`/owner/rekapitulasi`) — [resources/views/owner/recap/index.blade.php](file:///e:/Codex/resources/views/owner/recap/index.blade.php)
- **Keputusan:** **POLISH (TRANSPARANSI SUMBER MODAL)**
- **Rencana Penyempurnaan:** Tambahkan catatan informatif pada kartu modal tercatat: *"Data modal dihitung dari produk yang telah dilengkapi harga modalnya pada menu Master Produk."*
- **Komponen Terdampak:** `resources/views/owner/recap/index.blade.php`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Owner memahami secara transparan perhitungan laba kotor dan sumber modal belanja.

#### 5.4. Halaman Direktori Pelanggan (`/owner/pelanggan`) — [resources/views/owner/customers/index.blade.php](file:///e:/Codex/resources/views/owner/customers/index.blade.php)
- **Keputusan:** **POLISH (QUICK SEARCH FILTER)**
- **Rencana Penyempurnaan:** Tambahkan input pencarian nama/telepon pelanggan di bilah atas tabel pelanggan.
- **Komponen Terdampak:** `resources/views/owner/customers/index.blade.php`.
- **Prioritas:** **P2 (Medium)**
- **Hasil yang Diharapkan:** Owner dapat mencari data riwayat pelanggan tertentu dengan cepat.

---

## 3. RINGKASAN STRATEGIS JADWAL IMPLEMENTASI PHASE 3

```
+---------------------------------------------------------------------------------------------------------+
|                                    URUTAN IMPLEMENTASI PHASE 3                                          |
+---------------------------------------------------------------------------------------------------------+
| 1. SPRINT 1 (P0 Fixes)   : Perbaikan ParseError Blade pada /owner/produk/{id}/edit                      |
| 2. SPRINT 2 (Infrastruktur): Bundling Bootstrap 5.3 & Icons via Vite (Self-host CDN)                    |
| 3. SPRINT 3 (Admin Polish): Lightbox modal bukti transfer & dropdown kurir                             |
| 4. SPRINT 4 (Customer Pol): Password toggle, tab scroll mobile, feedback salin rekening                 |
| 5. SPRINT 5 (Owner Polish): Pencarian pelanggan, transparansi modal rekapitulasi                        |
+---------------------------------------------------------------------------------------------------------+
```

---

## 4. KESIMPULAN CETAK BIRU

Cetak biru ini membuktikan bahwa arsitektur antarmuka Uyuy Jastip hanya membutuhkan **1 perbaikan bug kritis (P0)** dan serangkaian **polishing terukur pada 12 halaman**. Tidak ada pembongkaran arsitektur atau perubahan alur bisnis yang dapat membahayakan stabilitas sistem backend. Cetak biru ini siap dieksekusi secara terstruktur pada Phase 3.
