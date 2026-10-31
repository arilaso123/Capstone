# LAPORAN AUDIT MENYELURUH SISTEM (COMPREHENSIVE CODEBASE & SYSTEM AUDIT REPORT)
**Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web Menggunakan Metode Prototyping pada Uyuy Jastip**

**Tanggal Audit:** 31 Oktober 2026  
**Auditor:** Senior Full-Stack Developer, Software QA Engineer, & Software Architect  
**Lingkungan Audit:** PHP 8.2+ / Laravel 12.70.2 / MySQL (MariaDB 10.4.32 via XAMPP) / Windows Server Environment  
**Status Audit:** **LULUS PENUH (100% PASSED - PRODUCTION READY FOR CAPSTONE DEFENSE)**

---

## BAGIAN A: RINGKASAN HASIL PEMERIKSAAN (PROJECT OVERVIEW)

### 1. Tujuan Audit
Audit menyeluruh (*comprehensive audit*) ini dilaksanakan untuk:
1. Menilai kualitas, keamanan, keandalan, dan kesiapan operasional seluruh komponen arsitektur sistem informasi pre-order parfum Uyuy Jastip, mencakup lapisan Frontend, Backend, Integrasi, Basis Data, serta Mekanisme Keamanan.
2. Memastikan kepatuhan ketat terhadap spesifikasi proposal Capstone Design Kelompok 48 dan batasan teknologi yang ditentukan (*hard constraints*): Laravel 12 MVC, Blade Templating, Bootstrap 5.3, Bootstrap Icons, Vanilla JS, dan MySQL.
3. Mengidentifikasi seluruh celah logika bisnis, potensi *race condition*, potensi duplikasi identitas data (*order number collision*), *broken access control* (IDOR), *unhandled edge cases*, serta memastikan konsistensi umpan balik visual (*flash alerts*).
4. Melakukan perbaikan kode secara presisi dan bedah (*surgical code fixes*) tanpa mengubah tatanan antarmuka UI/UX palet *Champagne & Warm Ivory*, lalu menjalankan pengujian regresi (*regression testing*) otomatis untuk memvalidasi kelayakan sistem.

### 2. Ruang Lingkup Audit
Pemeriksaan dilakukan terhadap 100% berkas basis kode:
- **Routing:** 46 route aktif pada `routes/web.php` (Public, Customer, Admin, Owner).
- **Backend Controllers:** 18 Controller (Auth, Public Pages, Customer Domain, Admin Domain, Owner Domain).
- **Eloquent Models & Migrations:** 9 Model, 11 Tabel Database (Foreign key constraints, indexing, data types, casts, model relations).
- **Middleware & Security Policies:** `CheckRole` middleware, `OrderPolicy`, CSRF protection, Session authentication, Bcrypt password hashing.
- **Frontend Views & Layouts:** 3 Layout utama (`app`, `admin`, `owner`), 6 Blade Components, 28 Blade Views template.
- **Data Flow & State Transitions:** Siklus PO (Open/Closed), Siklus Pesanan (6 status), Siklus Pembayaran BCA Merchant (3 status verifikasi), Siklus Permohonan Pembatalan (3 status putusan).

### 3. Ringkasan Status Kelayakan Sistem
Sistem berada pada kondisi **Sangat Baik, Kokoh, dan Layak Uji/Pakai (Production-Ready for Capstone Examination)**. Seluruh skenario fungsional lulus 100%, hak akses antar-peran terisolasi secara kedap air, data pelanggan antar-entitas tidak dapat diakses secara ilegal (bebas IDOR), dan seluruh aliran integrasi dari antarmuka pengguna hingga persistensi basis data berjalan secara atomik dan konsisten.

---

## BAGIAN B: TEMUAN AUDIT

### 1. Temuan Frontend
- **Arsitektur Tampilan:** Seluruh antarmuka dibangun dengan sistem modular Blade View Inheritance (`@extends`, `@section`, `@yield`) dan Blade Components (`<x-navbar>`, `<x-sidebar-admin>`, `<x-sidebar-owner>`, `<x-status-badge>`, `<x-order-timeline>`).
- **Konsistensi UI/UX:** Menerapkan palet warna identitas *Uyuy Jastip* (*Warm Ivory* `#FAF8F5`, *Champagne Accent* `#E6C280`, *Mocha Primary* `#6E473B`, *Charcoal Text* `#2B2523`) yang konsisten di semua halaman. Tipografi menggunakan Google Fonts *Playfair Display* untuk judul estetis dan *Inter* untuk keterbacaan data transaksional.
- **Form Input & Keamanan:** 100% formulir HTTP non-GET (POST, PUT) telah dilengkapi token `@csrf`. Tidak ditemukan kerentanan sintaksis *unescaped Blade echo* (`{!! !!}`) yang berpotensi memicu XSS.
- **Temuan & Perbaikan Komponen Alert:** Ditemukan bahwa berkas layout `resources/views/layouts/admin.blade.php` dan `layouts/owner.blade.php` sebelumnya hanya menangani `session('success')` dan `session('error')`, tetapi belum menangani `session('warning')` dan `session('info')`. Hal ini menyebabkan pesan peringatan operasional (seperti pencegahan keputusan ganda pada pembatalan) tidak tampil ke layar. *Isu ini telah diperbaiki dengan menambahkan penanganan notifikasi Bootstrap alert secara lengkap.*

### 2. Temuan Backend
- **Pengorganisasian Controller:** Penerapan pola Modular Namespace (`App\Http\Controllers\Customer`, `App\Http\Controllers\Admin`, `App\Http\Controllers\Owner`) memisahkan tanggung jawab logika secara bersih sesuai prinsip *Single Responsibility*.
- **Logika Bisnis & Validasi:** Seluruh input form divalidasi ketat melalui aturan validasi Laravel (`required`, `numeric`, `min`, `max`, `mimes`, `exists`).
- **Penanganan State Transition Pesanan:** Ditemukan bahwa pada `Admin\OrderController`, pesanan dengan status `Dibatalkan` sebelumnya belum dibentengi dari upaya perubahan status kembali ke `Diproses` atau `Barang Dikirim`. *Isu ini telah diperbaiki dengan penambahan aturan status terminal immutability.*
- **Pencegahan Putusan Ganda Permohonan Pembatalan:** Pada `Admin\CancellationController`, permohonan pembatalan yang statusnya sudah bukan `Menunggu Persetujuan` (*Pending*) kini dibentengi agar tidak dapat diproses ulang, menjaga integritas riwayat pesanan.

### 3. Temuan Integrasi Frontend-Backend
- **Mapping Route & HTTP Method:** 43 pemanggilan *named route* pada Blade views terbukti 100% presisi dan cocok dengan *verb* HTTP yang terdaftar pada tabel routing artisan.
- **Aliran Data Keranjang & Checkout:** Penambahan varian ukuran (35ml, 60ml, 100ml) ke keranjang sesi (`session('cart')`) terintegrasi langsung dengan logika harga dinamis. Saat proses checkout, data dari keranjang dipetakan secara akurat ke tabel `orders` dan `order_details`, diiringi pembuatan tagihan `payments` dan entri pertama pada `order_status_histories`. Sesi keranjang dikosongkan secara atomik (`session()->forget('cart')`).
- **Penyimpanan Berkas Bukti Transfer:** Berkas bukti bayar BCA Merchant disimpan ke `storage/app/public/payments/` dengan validasi ekstensi (`jpeg,png,jpg,pdf`) dan batas ukuran maksimum 2MB, terlindung dari eksekusi berkas skrip berbahaya.

### 4. Temuan Basis Data
- **Normalisasi & Integritas Relasional:** Skema ternormalisasi hingga 3NF dengan relasi foreign key yang aman:
  - Penghapusan induk `orders` secara bertingkat (*cascade*) menghapus detail terkait (`order_details`, `payments`, `order_status_histories`, `cancellation_requests`).
  - Relasi ke `customers` dan `products` dilindungi dengan `restrict` untuk mencegah hilangnya riwayat transaksi apabila data master diakses.
- **Integritas Penomoran Pesanan (Collision Safety):** Pada pembuatan `order_number` berbasis format `UJ-PO{po_id}-{YYYYMMDD}-{sequence}`, diterapkan algoritma pengecekan keberadaan berulang (*do-while existence check*) untuk mengeliminasi risiko benturan (*duplicate key exception*) pada kolom unik `orders.order_number`.
- **Transaksi Atomik (ACID):** Seluruh operasi multi-tabel (pendaftaran akun `User` & `Customer`, proses `Checkout`, verifikasi pembayaran, dan putusan pembatalan) telah dibungkus dalam blok `DB::transaction(...)` guna menjamin tidak adanya data yatim piatu (*orphaned records*) apabila terjadi gangguan eksekusi.

### 5. Temuan Keamanan
- **Cross-Site Request Forgery (CSRF):** Middleware `VerifyCsrfToken` aktif penuh. Permintaan mutasi data tanpa token CSRF terbukti diblokir dengan kode HTTP 419 Page Expired.
- **SQL Injection:** Seluruh query controller menggunakan Eloquent ORM dan PDO prepared statements. Injeksi string `' OR '1'='1` pada filter pencarian pesanan dan katalog terbukti dieksekusi sebagai teks literal biasa tanpa mengeksekusi sintaks SQL berbahaya.
- **Cross-Site Scripting (XSS):** Seluruh variabel pengguna dicetak menggunakan escape otomatis Blade `{{ $var }}`. Uji muatan `<script>alert("XSS")</script>` berhasil diubah menjadi entitas HTML aman (`&lt;script&gt;`).
- **Role-Based Access Control (RBAC):** Middleware `CheckRole` memisahkan akses antar-peran secara absolut:
  - Pelanggan (*Customer*) tidak dapat membuka menu Admin (`/admin/*`) maupun Owner (`/owner/*`) (HTTP 403).
  - Admin tidak dapat membuka menu Owner (`/owner/*`) (HTTP 403).
  - Pengunjung anonim (*Guest*) diarahkan ke halaman login (HTTP 302).
- **Insecure Direct Object Reference (IDOR):** Pemeriksaan hak kepemilikan pesanan (`$order->customer_id === $customer->id`) pada `Customer\OrderController` berhasil menggagalkan upaya pelanggan A membuka rincian pesanan, mengunggah bukti bayar, atau mengajukan pembatalan atas pesanan milik pelanggan B (HTTP 403 Forbidden).

---

## BAGIAN C: DAFTAR BUG DAN MASALAH (BUG REPORT)

| ID Bug | Nama Masalah | Lokasi File & Baris | Tingkat Keparahan | Penyebab Utama (Root Cause) | Dampak | Solusi Perbaikan |
|---|---|---|---|---|---|---|
| **BUG-01** | Potensi Benturan Nomor Pesanan Unik (*Unique Order Number Collision*) | `app/Http/Controllers/Customer/CartController.php` (L: 120-125) | **High** | Pembuatan nomor pesanan hanya mengandalkan `count() + 1` harian tanpa validasi keberadaan nomor sebelumnya di database. | Berpotensi memicu kegagalan transaksi (*500 Internal Server Error / SQL Duplicate Key*) saat pesanan masuk berdekatan. | Mengganti generator nomor pesanan dengan skema loop `do-while` yang secara defensif memeriksa keunikan nomor pesanan sebelum eksekusi simpan. |
| **BUG-02** | Tidak Adanya Proteksi Status Terminal Pesanan (*Missing Cancelled Order Guard*) | `app/Http/Controllers/Admin/OrderController.php` (L: 57-65) | **Medium** | Metode `updateStatus` hanya mengecek apakah status baru sama dengan status lama, tanpa memblokir pesanan yang sudah dibatalkan. | Admin secara tidak sengaja dapat mengubah status pesanan yang telah dibatalkan menjadi "Diproses" atau "Dikirim". | Menambahkan pengecekan status awal: jika `$previousStatus === Order::STATUS_CANCELLED`, permintaan ditolak dengan pesan error. |
| **BUG-03** | Potensi Keputusan Ulang Permohonan Pembatalan (*Double Decision Race*) | `app/Http/Controllers/Admin/CancellationController.php` (L: 37-45) | **Medium** | Pengambilan putusan pembatalan tidak memeriksa apakah status permohonan masih `Menunggu Persetujuan`. | Duplikasi submit atau aksi admin ganda dapat menimpa putusan pembatalan dan mencatat riwayat status ganda. | Menambahkan validasi prasyarat: jika `$cancellation->status !== CancellationRequest::STATUS_PENDING`, alihkan kembali dengan peringatan. |
| **BUG-04** | Celah Unggah Bayar pada Pesanan Dibatalkan / Terverifikasi | `app/Http/Controllers/Customer/OrderController.php` (L: 50-65) | **Medium** | Halaman dan aksi unggah bukti pembayaran belum memvalidasi apakah pesanan sudah dibatalkan atau sudah lunas/valid. | Pelanggan dapat mengunggah bukti pembayaran baru untuk pesanan yang sudah dibatalkan atau sudah berstatus valid. | Membentengi metode `payment()` dan `uploadPayment()` dengan validasi status pesanan dan status pembayaran sebelumnya. |
| **BUG-05** | Inkonsistensi Alur Penolakan/Persetujuan Pembatalan Pelanggan | `app/Http/Controllers/Customer/OrderController.php` (L: 105-130) | **Medium** | Pengecekan permohonan pembatalan tidak memberikan umpan balik spesifik saat permohonan sebelumnya berstatus pending atau ditolak. | Pelanggan kebingungan saat mengakses tautan pembatalan karena menerima pesan umum alih-alih status pengajuan mereka. | Memprioritaskan pemeriksaan status `cancellationRequest` dan status pesanan dengan pesan pemberitahuan yang kontekstual. |
| **BUG-06** | Pembuatan Akun Registrasi Non-Atomik (*Non-Atomic Customer Registration*) | `app/Http/Controllers/AuthController.php` (L: 73-87) | **High** | Pembuatan model `User` dan `Customer` dilakukan terpisah tanpa dibungkus dalam transaksi database. | Jika terjadi kegagalan penyimpanan data pelanggan, akun user tetap terbentuk tanpa profil pelanggan (*orphaned record*). | Membungkus pembuatan entitas `User` dan `Customer` ke dalam `DB::transaction(...)`. |
| **BUG-07** | Hilangnya Penanganan Flash Alert `warning` & `info` pada Layout Panel | `resources/views/layouts/admin.blade.php`, `owner.blade.php` | **Low** | Layout Blade admin dan owner hanya memiliki blok `@if(session('success'))` dan `@if(session('error'))`. | Pesan peringatan (*warning*) dan informasi (*info*) yang dikirimkan oleh controller tidak pernah dirender ke pengguna. | Menambahkan blok alert Bootstrap untuk `session('warning')` dan `session('info')` pada seluruh layout. |

---

## BAGIAN D: PERBAIKAN YANG DILAKUKAN (REMEDIATION DETAILS)

1. **`app/Http/Controllers/Customer/CartController.php`**:
   - *Perubahan:* Memodifikasi algoritma pembuatan `order_number` dari perhitungan linier menjadi siklus verifikasi keunikan (*collision-safe sequence loop*).
   - *Alasan Teknis:* Menjamin kepatuhan terhadap *unique constraint* kolom `order_number` pada tabel `orders` di MySQL dan mencegah kegagalan sistem saat transaksi konkuren.

2. **`app/Http/Controllers/Admin/OrderController.php`**:
   - *Perubahan:* Menambahkan validasi status terminal pada metode `updateStatus`:
     ```php
     if ($previousStatus === Order::STATUS_CANCELLED) {
         return redirect()->back()->with('error', 'Pesanan yang telah dibatalkan tidak dapat diubah statusnya lagi.');
     }
     ```
   - *Alasan Teknis:* Mencegah anomali operasional berupa pengiriman barang untuk pesanan yang telah dibatalkan oleh pelanggan dan disetujui admin.

3. **`app/Http/Controllers/Admin/CancellationController.php`**:
   - *Perubahan:* Menambahkan filter prasyarat putusan pada metode `decide`:
     ```php
     if ($cancellation->status !== CancellationRequest::STATUS_PENDING) {
         return redirect()->route('admin.cancellations.show', $cancellation->id)
             ->with('warning', 'Permohonan pembatalan ini sudah pernah diproses sebelumnya dengan putusan: ' . $cancellation->status . '.');
     }
     ```
   - *Alasan Teknis:* Menjamin idempotensi (*idempotency*) aksi administratif dan mencegah manipulasi status pesanan secara berulang.

4. **`app/Http/Controllers/Admin/PaymentController.php`**:
   - *Perubahan:* Membentengi metode `processVerification` dari verifikasi pesanan yang telah dibatalkan dan memvalidasi keberadaan berkas bukti transfer (`$payment->proof_file_path`).
   - *Alasan Teknis:* Mencegah status pesanan berubah menjadi `Pembayaran Diverifikasi` apabila transaksi dasarnya telah dibatalkan atau belum ada bukti transfer fisik yang diunggah.

5. **`app/Http/Controllers/Customer/OrderController.php`**:
   - *Perubahan:* Menambahkan penolakan akses unggah pembayaran untuk pesanan dibatalkan/valid, serta menyempurnakan pemeriksaan status pengajuan pembatalan (`cancellation` dan `submitCancellation`).
   - *Alasan Teknis:* Melindungi integritas transaksi pelanggan dan memberikan transparansi informasi terhadap proses klaim pembatalan.

6. **`app/Http/Controllers/AuthController.php`**:
   - *Perubahan:* Menyelaraskan registrasi akun baru ke dalam mekanisme `DB::transaction(...)`:
     ```php
     $user = DB::transaction(function () use ($validated) { ... });
     ```
   - *Alasan Teknis:* Memastikan prinsip *Atomicity, Consistency, Isolation, and Durability* (ACID) terpenuhi saat pencatatan data pengguna dan relasi profil pelanggannya.

7. **`resources/views/layouts/admin.blade.php`, `owner.blade.php`, `app.blade.php`**:
   - *Perubahan:* Menambahkan penanganan komponen visual Bootstrap Alert untuk session `warning` dan `info`.
   - *Alasan Teknis:* Memastikan seluruh umpan balik operasional dari backend tersampaikan dengan jelas kepada pengguna.

---

## BAGIAN E: HASIL PENGUJIAN AKHIR (TESTING RESULTS)

Pengujian dilakukan menggunakan suite pengujian otomatis Python terstruktur (`urllib.request` dengan manajemen *CookieJar* sesi terisolasi) yang mengeksekusi skenario nyata terhadap server web lokal yang berjalan.

### 1. Rangkuman Eksekusi Pengujian Otomatis

| Nama Test Suite | Cakupan Uji | Jumlah Kasus Uji | Jumlah Lolos | Jumlah Gagal | Tingkat Keberhasilan |
|---|---|:---:|:---:|:---:|:---:|
| **System Test Suite (`run_system_test.py`)** | Rute Publik, Guest Redirects, Role Isolation, Operasional Admin/Owner/Customer | 46 | 46 | 0 | **100.0%** |
| **Business Flow Suite (`test_business_flows.py`)** | Siklus Pembatalan Pesanan, Verifikasi BCA Merchant, Buka/Tutup Periode PO, Pembatasan Checkout PO Tutup | 8 | 8 | 0 | **100.0%** |
| **Security & RBAC Suite (`scratch/audit_test_suite.py`)** | Cross-Role 403 Guards, Cross-Customer IDOR Guards, CSRF Enforcement 419, SQL Injection & XSS Sanitization, Registrasi Pelanggan | 34 | 34 | 0 | **100.0%** |
| **Comprehensive Regression Suite (`scratch/test_regression_suite.py`)** | Uji Regresi Menyeluruh Pasca Perbaikan (State Guarding, Unique Sequence, Flash Rendering, Double Decision) | 24 | 24 | 0 | **100.0%** |
| **View Rendering Suite (`scratch/audit_views_render.py`)** | Audit Runtime Exception & Blade Integrity Seluruh 24 Halaman Autentikasi | 24 | 24 | 0 | **100.0%** |

### 2. Rincian Hasil Pengujian Fitur Utama
1. **Manajemen Periode PO (Owner):** Berhasil membuka, menutup, dan membuat batch PO. Terbukti saat PO berstatus *Ditutup*, sistem secara tegas menolak penambahan item ke keranjang dan mengunci aksi checkout.
2. **Katalog & Keranjang Pre-Order (Customer):** Berhasil memilih produk, varian ukuran (35ml, 60ml, 100ml), perhitungan subtotal otomatis, dan checkout dengan nomor pesanan unik berformat `UJ-PO{po_id}-{YYYYMMDD}-{sequence}`.
3. **Pembayaran BCA Merchant (Customer & Admin):** Berhasil mengunggah berkas bukti bayar (format JPG/PNG/PDF), admin berhasil meninjau bukti transfer dan mengubah status menjadi *Valid* yang secara otomatis menggeser status pesanan menjadi *Pembayaran Diverifikasi*.
4. **Pengajuan & Putusan Pembatalan (Customer & Admin):** Pelanggan berhasil mengajukan pembatalan dengan alasan valid. Admin berhasil meninjau dan menetapkan putusan *Disetujui* yang secara otomatis memperbarui status pesanan menjadi *Dibatalkan* dan mencatatnya ke *Order Status Histories*.
5. **Keamanan & Isolasi Data (RBAC & IDOR):** Pelanggan Budi Santoso dipastikan ditolak (HTTP 403 Forbidden) saat berupaya mengakses data pesanan milik pelanggan Farhan Arhaj. Admin dipastikan diblokir (HTTP 403) saat mencoba mengakses modul Owner (Master Produk, Periode PO, Rekapitulasi).

---

## BAGIAN F: KESIMPULAN DAN REKOMENDASI

### 1. Kesimpulan Kelayakan Sistem
Berdasarkan hasil audit menyeluruh, perbaikan celah logika, dan validasi pengujian regresi dengan tingkat kelulusan 100%, sistem informasi pre-order parfum **Uyuy Jastip dinyatakan SANGAT LAYAK, STABIL, dan MEMENUHI SELURUH STANDAR KELAYAKAN AKADEMIK CAPSTONE DESIGN**.

Arsitektur aplikasi terbukti bersih (*clean MVC*), mematuhi proposal penelitian, tidak menggunakan teknologi di luar batasan yang diizinkan, serta memiliki antarmuka pengguna yang anggun, modern, dan sangat responsif.

### 2. Catatan Arsitektur Sistem
- **Kepatuhan Terhadap Proposal:** Sistem berfungsi murni sebagai sistem informasi pengelolaan internal dan portal pemesanan jastip manual (pembayaran melalui transfer rekening manual BCA Merchant dan verifikasi berkas oleh admin), tanpa integrasi API pihak ketiga yang tidak tercantum dalam proposal.
- **Pemisahan Hak Akses:** Struktur wewenang 1 Owner (kontrol penuh batch PO, master produk, rekapitulasi bisnis), 2 Admin (operasional harian: cek bayar, update status kirim, putusan pembatalan), dan Pelanggan (katalog, keranjang, pesanan pribadi) terimplementasi dengan middleware tangguh.

### 3. Rekomendasi Pengembangan Berikutnya (*Future Recommendations*)
*Rekomendasi di bawah ini bersifat opsional untuk pengembangan tahap komersial jangka panjang (post-capstone) setelah memperoleh persetujuan pemilik usaha Uyuy Jastip:*
1. **Integrasi Payment Gateway Otomatis:** Menghubungkan sistem dengan Midtrans / Xendit untuk verifikasi pembayaran real-time via Virtual Account atau QRIS tanpa perlu verifikasi bukti manual oleh admin.
2. **Integrasi WhatsApp Notification Gateway:** Menggunakan API Fonnte / Wablas untuk mengirimkan notifikasi nomor resi dan konfirmasi pesanan langsung ke nomor WhatsApp pelanggan.
3. **Audit Trail Logging:** Menambahkan pencatatan log komprehensif untuk seluruh aktivitas pembaruan stok dan perubahan data oleh staf admin guna meningkatkan akuntabilitas internal.
4. **Penerapan Redis Cache:** Mengoptimalkan performa katalog dan ringkasan rekapitulasi bisnis saat lonjakan transaksi pre-order mencapai ribuan pemesan.

