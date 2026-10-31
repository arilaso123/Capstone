# Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web pada Uyuy Jastip

Proyek Capstone Design Program Studi S1 Sistem Informasi - Universitas Telkom Kampus Jakarta (2026).

**Judul Capstone**:
> **"Perancangan Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web Menggunakan Metode Prototyping pada Uyuy Jastip."**

**Disusun Oleh**:
- Muhammad Anshari Hakam (102042300087)
- Raka Rahmatullah Firdaus (102042330146)
- Muhammad Fathir Arhaj (102042300162)
- Farhan (102042300164)

---

## 1. Gambaran Umum Sistem
Uyuy Jastip merupakan usaha yang bergerak dalam bidang jasa titip parfum (vendor resmi: *In Parfume*). Dalam konteks operasional bisnis:
- Satu periode Pre-Order (PO) berlangsung selama 1 minggu dan melayani sekitar 200 pelanggan.
- Jam operasional staf admin berlangsung pada Selasa s.d. Minggu pukul 09.00 – 19.00 WIB (Senin fokus rekapitulasi data).
- Pembayaran dilakukan melalui **BCA Merchant** (transfer non-tunai) dan diverifikasi manual oleh Admin.
- Sistem ini memusatkan data produk, pelanggan, pesanan, verifikasi pembayaran, pembatalan pesanan, dan pemantauan status pesanan dalam satu mekanisme web terintegrasi untuk mengatasi kendala pesanan terlewat dan catatan yang tersebar di WhatsApp, Instagram, TikTok, maupun Threads.

---

## 2. Tumpukan Teknologi (Technology Stack)
Sesuai batasan proposal dan ketentuan perancangan:
- **Backend Framework**: Laravel 12 (Arsitektur MVC, Eloquent ORM, Blade Components, Form Requests Validation, Middleware RBAC, Policies)
- **Frontend**: Laravel Blade + Bootstrap 5.3.3
- **CSS Framework**: Bootstrap 5
- **Icons**: Bootstrap Icons 1.11.3
- **Tipografi**:
  * Heading & Branding: *Playfair Display* (Google Fonts)
  * Body Text, Tabel, & Form: *Inter* (Google Fonts)
- **Basis Data**: MySQL / MariaDB 10.4 (XAMPP)
- **Palet Warna Desain**:
  * Warm Ivory: `#F7F4EF` (Latar utama aplikasi)
  * Champagne: `#D8C5AE` (Aksen sekunder & banner PO)
  * Mocha: `#8B6B50` (Tombol utama & identitas brand)
  * Charcoal: `#302B27` (Warna teks utama berjarak kontras tinggi)
  * White: `#FFFFFF` (Permukaan kartu & modal)
  * Soft Gray: `#E8E2DA` (Border halus & pemisah tabel)

---

## 3. Pembagian Peran Pengguna (Role-Based Access Control)

### A. Customer (Pelanggan)
- Registrasi akun baru dan login/logout.
- Menjelajahi katalog parfum dan melihat detail aroma (*top, middle, base notes*).
- Mengetahui status periode PO (*Dibuka* atau *Ditutup*).
- Memilih satu atau beberapa produk parfum dan melakukan checkout saat periode PO dibuka.
- Mengunggah berkas bukti transfer BCA Merchant (masa retensi berkas maksimal 1 bulan).
- Memantau alur tahapan status pesanan miliknya sendiri (5 tahap baku: *Menunggu Pembayaran*, *Pembayaran Diverifikasi*, *Diproses*, *Barang Dikirim*, *Selesai*).
- Mengajukan permohonan pembatalan pesanan yang memerlukan pemeriksaan dan persetujuan Admin.
- **Isolasi Data**: Hanya dapat mengakses data pesanan miliknya sendiri (ditegakkan pada level backend oleh Policy dan Controller Guard).

### B. Admin (Staf Operasional)
- Mengakses dashboard operasional harian.
- Memeriksa rincian pesanan pelanggan dalam batas kewenangannya.
- Memeriksa fisik berkas bukti pembayaran BCA Merchant dan mencatat hasil verifikasi (*Valid* / *Ditolak*).
- Memperbarui status pemenuhan pesanan secara terstruktur (dengan pencatatan log audit otomatis di `order_status_histories`).
- Meninjau alasan dan memutuskan (*Disetujui* / *Ditolak*) permohonan pembatalan dari pelanggan.
- **Batasan Ketat Admin**: Tidak berwenang membuka/menutup periode PO, tidak berwenang mengubah master produk, dan tidak dapat mengakses rekapitulasi bisnis Owner.

### C. Owner (Pemilik Usaha)
- Mengakses dashboard eksekutif bisnis (omzet nilai transaksi, total pelanggan, jumlah pesanan, dan distribusi status).
- Mengelola master produk (menambah produk baru, mengubah harga/ukuran, menonaktifkan produk).
- Membuat periode PO baru serta membuka dan menutup periode PO.
- Melihat rekapitulasi kebutuhan botol parfum per varian untuk pengadaan belanja langsung ke vendor *In Parfume* (dapat dicetak langsung).
- Mengakses direktori pelanggan jastip.

---

## 4. Struktur Dokumentasi Lengkap Proyek
Dokumentasi disusun secara mendalam dan komprehensif pada file-file berikut:

1. [**`PROJECT_PLAN.md`**](file:///e:/CAPSTONE/Capstone/PROJECT_PLAN.md): Rencana kerja lengkap, pemetaan kebutuhan fungsional FR-01 s.d. FR-22, NFR-01 s.d. NFR-11, dan matriks hak akses peran.
2. [**`UI_UX_GUIDELINE.md`**](file:///e:/CAPSTONE/Capstone/UI_UX_GUIDELINE.md): Panduan lengkap sistem desain, hierarki tipografi, palet warna, komponen stepper, dan responsivitas.
3. [**`DATABASE_DESIGN.md`**](file:///e:/CAPSTONE/Capstone/DATABASE_DESIGN.md): Spesifikasi skema basis data, diagram relasi ERD (7 entitas utama + 3 tabel pendukung), kamus data, dan aturan integritas data.
4. [**`OPEN_QUESTIONS.md`**](file:///e:/CAPSTONE/Capstone/OPEN_QUESTIONS.md): Pemetaan pertanyaan terbuka, pemisahan fakta proposal, keputusan teknis implementasi, dan rekomendasi bisnis untuk Owner.
5. [**`IMPLEMENTATION_PROGRESS.md`**](file:///e:/CAPSTONE/Capstone/IMPLEMENTATION_PROGRESS.md): Log perkembangan pengerjaan proyek dan matriks keterlacakan (*traceability matrix*).
6. [**`TESTING_REPORT.md`**](file:///e:/CAPSTONE/Capstone/TESTING_REPORT.md): Laporan komprehensif hasil pengujian otomasi (52 tes lulus 100%), uji isolasi keamanan data, dan kepatuhan UI/UX.

---

## 5. Cara Menjalankan Aplikasi

### Prasyarat
- PHP $\ge$ 8.2 (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`)
- Composer $\ge$ 2.0
- MySQL / MariaDB (XAMPP)

### Langkah Menjalankan
1. **Pastikan Layanan MySQL Berjalan**:
   Start modul MySQL via XAMPP Control Panel, atau jalankan via terminal:
   ```powershell
   C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone
   ```

2. **Masuk ke Direktori Proyek**:
   ```bash
   cd e:\CAPSTONE\Capstone
   ```

3. **Pastikan Konfigurasi `.env` Sesuai**:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=uyuy_jastip
   DB_USERNAME=root
   DB_PASSWORD=
   SESSION_DRIVER=file
   QUEUE_CONNECTION=sync
   ```

4. **Jalankan Migrasi Basis Data & Seeder (Jika Belum)**:
   ```bash
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```

5. **Jalankan Server Lokal Laravel**:
   ```bash
   php artisan serve --port=8000
   ```

6. **Buka Aplikasi di Peramban Web**:
   Akses [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 6. Kredensial Akun Demonstrasi (Demo Accounts)

Semua akun pengujian menggunakan kata sandi (password): `password`.

| Peran (Role) | Nama Pengguna | Alamat Email | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Owner** | Pemilik Uyuy Jastip | `owner@uyuy.test` | Buka/Tutup PO, Master Produk, Rekap Belanja Vendor, Database Pelanggan |
| **Admin 1** | Siti Nurhaliza (Admin) | `admin1@uyuy.test` | Verifikasi Pembayaran BCA, Alur Status Pesanan, Putusan Pembatalan |
| **Admin 2** | Ahmad Fauzi (Admin) | `admin2@uyuy.test` | Verifikasi Pembayaran BCA, Alur Status Pesanan, Putusan Pembatalan |
| **Customer 1** | Muhammad Anshari | `customer@uyuy.test`| Lihat Katalog, Checkout PO, Unggah Bukti Transfer, Pengajuan Batal |
| **Customer 2** | Budi Santoso | `budi@uyuy.test` | Pesanan Batch 10, Isolasi Data Pesanan Pribadi |
| **Customer 3** | Dewi Lestari | `dewi@uyuy.test` | Pesanan Batch 9, Riwayat Selesai |
| **Customer 4** | Farhan Arhaj | `farhan@uyuy.test`| Pesanan Batch 10, Riwayat Pembatalan |

*Catatan: Pada halaman Login ([http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)), tersedia fitur tombol cepat (quick demo buttons) untuk mempermudah login ketiga peran.*

> **Tips Simulasi 3 Peran Sekaligus (Multi-Tab)**:
> Agar sesi login tidak saling menimpa saat menguji ketiga peran secara bersamaan, jalankan `php artisan serve --host=0.0.0.0 --port=8000` dan buka 3 tab di browser:
> - Tab 1 (Customer): `http://localhost:8000`
> - Tab 2 (Admin): `http://127.0.0.1:8000`
> - Tab 3 (Owner): `http://127.0.0.2:8000`

---

## 7. Menjalankan Pengujian Otomatis (Automated Test Suite)

Proyek dilengkapi dengan pengujian terotomasi menyeluruh:

1. **Pengujian Semua Rute & Otorisasi RBAC (46 Uji)**:
   ```bash
   python run_system_test.py
   ```

2. **Pengujian Alur Bisnis Lanjutan & Isolasi Keamanan (8 Uji)**:
   ```bash
   python test_business_flows.py
   ```
   *Menguji proteksi isolasi data antar-customer (403 Forbidden), pengajuan & persetujuan pembatalan, verifikasi pembayaran manual, serta pemblokiran checkout saat PO ditutup.*
