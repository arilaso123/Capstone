# PROJECT PLAN
## Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web - Uyuy Jastip

**Judul Capstone**: *Perancangan Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web Menggunakan Metode Prototyping pada Uyuy Jastip*  
**Penyusun**: Tim Capstone Universitas Telkom Kampus Jakarta (2026)  
**Metodologi**: Prototyping (Iteratif)  
**Teknologi Utama**: Laravel (MVC), Laravel Blade, Bootstrap 5, Bootstrap Icons, MySQL/MariaDB, Vanilla JavaScript.

---

### 1. Ringkasan Eksekutif & Latar Belakang
Uyuy Jastip merupakan usaha jasa titip parfum (vendor *In Parfume*). Dalam satu siklus periode pre-order (PO) yang berlangsung selama 1 minggu, Uyuy Jastip melayani sekitar 200 pelanggan dengan dukungan 1 Owner dan 2 Admin. Operasional berjalan Selasa s.d. Minggu pukul 09.00–19.00.

Sebelumnya, aktivitas promosi dan pemesanan tersebar di berbagai kanal (Instagram, WhatsApp, TikTok, Threads) serta pencatatan bukti pembayaran BCA Merchant dilakukan secara terpisah. Hal ini menimbulkan risiko pesan terlewat, penelusuran perubahan pesanan yang rumit, dan kesulitan dalam rekapitulasi data.

Sistem informasi berbasis web ini dirancang untuk memusatkan dan mengintegrasikan:
1. Informasi periode PO (Dibuka / Ditutup).
2. Katalog dan detail produk parfum.
3. Pemesanan terstruktur (1 Order memuat 1 atau lebih item produk).
4. Pengunggahan bukti pembayaran BCA Merchant dan verifikasi manual oleh Admin.
5. Pemantauan alur status pesanan secara transparan bagi Customer.
6. Penanganan permohonan pembatalan pesanan yang membutuhkan persetujuan Admin.
7. Rekapitulasi pesanan per periode PO untuk pelaporan Owner.

---

### 2. Matriks Hak Akses Pengguna (Role-Based Access Control)

| Fitur / Hak Akses | Customer | Admin | Owner | Catatan Ketentuan Proposal |
| :--- | :---: | :---: | :---: | :--- |
| **Registrasi & Login Akun** | ✅ | ✅ | ✅ | Akun dipisah berdasarkan role `customer`, `admin`, `owner`. |
| **Melihat Katalog & Detail Produk** | ✅ | ✅ | ✅ | Tampilan publik & customer. |
| **Melihat Informasi & Status PO** | ✅ | ✅ | ✅ | Mengetahui apakah PO sedang `Dibuka` atau `Ditutup`. |
| **Melakukan Pemesanan (Checkout)** | ✅ | ❌ | ❌ | Hanya bisa dilakukan saat periode PO aktif berstatus `Dibuka`. |
| **Unggah Bukti Pembayaran** | ✅ | ❌ | ❌ | Customer mengunggah berkas transfer BCA Merchant untuk pesanannya. |
| **Melihat Detail & Status Pesanan** | ✅ (Milik sendiri) | ✅ | ✅ | Customer dibatasi strictly oleh Policy (tidak boleh melihat ID lain). |
| **Mengajukan Pembatalan Pesanan** | ✅ | ❌ | ❌ | Mengajukan alasan pembatalan; status menunggu keputusan Admin. |
| **Pemeriksaan & Verifikasi Pembayaran** | ❌ | ✅ | ✅ | Memeriksa berkas bukti transfer & menetapkan valid/ditolak. |
| **Pembaruan Status Pesanan** | ❌ | ✅ | ✅ | Menunggu Pembayaran $\rightarrow$ Pembayaran Diverifikasi $\rightarrow$ Diproses $\rightarrow$ Barang Dikirim $\rightarrow$ Selesai. |
| **Persetujuan / Penolakan Pembatalan** | ❌ | ✅ | ✅ | Admin memproses permohonan pembatalan dari Customer. |
| **Membuka & Menutup Periode PO** | ❌ | ❌ | ✅ | **Dilarang untuk Admin**. Mutlak wewenang Owner (FR-04, FR-21). |
| **Manajemen Master Produk** | ❌ | ❌ | ✅ | Menambah, mengubah, menonaktifkan produk wewenang Owner (FR-05). |
| **Rekapitulasi Lengkap Per Periode PO** | ❌ | ❌ | ✅ | Admin dibatasi dari rekapitulasi menyeluruh milik Owner (FR-17, FR-21). |
| **Laporan Operasional & Penjualan** | ❌ | ❌ | ✅ | Hak akses laporan Owner. |

---

### 3. Pemetaan Kebutuhan Fungsional (FR) & Nonfungsional (NFR)

#### Functional Requirements (FR-01 s.d. FR-22)
- **FR-01**: Registrasi akun Customer.
- **FR-02**: Otentikasi Login dan Logout pengguna sesuai peran.
- **FR-03**: Pembatasan akses berbasis peran (Middleware & Policies).
- **FR-04**: Pembukaan dan penutupan periode PO oleh Owner.
- **FR-05**: Pengelolaan data produk oleh Owner.
- **FR-06**: Tampilan katalog informasi produk dan periode PO kepada Customer.
- **FR-07**: Pembuatan pesanan hanya ketika periode PO berstatus `Dibuka`.
- **FR-08**: Penyimpanan data pesanan terhubung dengan Customer dan Periode PO.
- **FR-09**: Dukungan pesanan multi-item (Order Details).
- **FR-10**: Informasi pembayaran BCA Merchant dan fasilitas unggah berkas bukti pembayaran.
- **FR-11**: Fasilitas pemeriksaan bukti pembayaran oleh Admin.
- **FR-12**: Pencatatan hasil verifikasi pembayaran oleh Admin.
- **FR-13**: Tampilan tahapan alur status pesanan.
- **FR-14**: Pengajuan permohonan pembatalan pesanan oleh Customer.
- **FR-15**: Fasilitas persetujuan atau penolakan pembatalan oleh Admin.
- **FR-16**: Penampilan status pesanan kepada Customer bersangkutan.
- **FR-17**: Penyajian rekapitulasi pesanan per periode PO sesuai hak akses (Owner).
- **FR-18**: Penyaringan / filter data pesanan berdasarkan periode PO.
- **FR-19**: Penyimpanan riwayat log perubahan status pesanan & pembatalan.
- **FR-20**: Pembatasan isolasi data pesanan Customer (Data Isolation).
- **FR-21**: Pembatasan kewenangan Admin (no edit master data, no close PO, no full recap).
- **FR-22**: Pengelolaan berkas bukti transfer dengan retensi maksimal 1 bulan.

#### Non-Functional Requirements (NFR-01 s.d. NFR-11)
- **NFR-01 (Usability)**: Antarmuka elegan, bersih, intuitif, konsisten.
- **NFR-02 (Perangkat)**: Kompatibel sempurna pada smartphone dan laptop/desktop.
- **NFR-03 (Keamanan)**: Otentikasi bcrypt, proteksi CSRF, sanitasi masukan.
- **NFR-04 (Privasi Data)**: Keterpisahan data pribadi dan pesanan antar-customer.
- **NFR-05 (Integritas Data)**: Foreign keys, transactions, status consistency.
- **NFR-06 (Maintainability)**: Arsitektur Laravel MVC rapi, clean separation, Blade components.
- **NFR-07 (Kapasitas Operasional)**: Mendukung 200 pelanggan per periode PO.
- **NFR-08 (Fleksibilitas Produk)**: Tidak ada batas arbitrer jumlah produk.
- **NFR-09 (Traceability)**: Kode dan tabel dapat ditelusuri ke kode FR/NFR.
- **NFR-10 (Pembatasan Kewenangan)**: Backend route middleware & authorization gate.
- **NFR-11 (Retensi Bukti Pembayaran)**: Metadata waktu unggah dan mekanisme pengelolaan masa simpan 30 hari.

---

### 4. Rencana Tahapan Kerja (Phases)

| Tahap | Aktivitas Utama | Output | Status |
| :--- | :--- | :--- | :---: |
| **PHASE 1** | Analisis project, telaah proposal, penyiapan framework Laravel, pembentukan dokumen `PROJECT_PLAN.md` & `OPEN_QUESTIONS.md`. | Kerangka project & dokumen analisis | In Progress |
| **PHASE 2** | UI/UX Design System, Layout Master (Public, Customer, Admin, Owner), Blade Components, Wireframe/Prototipe Halaman Publik & Dashboard. | Template visual lengkap & responsif | Up Next |
| **PHASE 3** | Database Design (ERD), Laravel Migrations (7 entitas + tabel pendukung), Eloquent Models & Relations, Database Seeders dummy realistis. | Database skema siap pakai | Scheduled |
| **PHASE 4** | Backend Core Development: Auth, Middleware Role, Controller untuk PO Period, Product, Cart/Checkout, Payment Upload/Verify, Order Status Engine, Cancellation Request. | Logika bisnis aktif | Scheduled |
| **PHASE 5** | Frontend-Backend Integration: Binding Blade dengan Model ORM, Form Requests validation, Flash alerts, Search & Filter, Status Timeline, Pagination. | Aplikasi fungsional penuh | Scheduled |
| **PHASE 6** | Pengujian & Verifikasi: Black-box testing FR-01 s.d. FR-22, Role boundary security check, Responsive check, Dokumentasi hasil pengujian. | `TESTING_REPORT.md` & Aplikasi teruji | Scheduled |

