# SPESIFIKASI KEBUTUHAN SISTEM (SOFTWARE REQUIREMENTS SPECIFICATION)
## Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web — Uyuy Jastip

---

| Informasi Dokumen | Keterangan |
| :--- | :--- |
| **Nama Proyek** | Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web (Uyuy Jastip) |
| **Kategori Proyek** | Capstone Design / Tugas Akhir Rekayasa Perangkat Lunak |
| **Target Platform** | Web Application (Responsive Mobile & Desktop) |
| **Teknologi Utama** | PHP 8.2, Laravel 12 (MVC), MariaDB / MySQL, Bootstrap 5.3.3 |
| **Model Standar Mutu** | ISO/IEC 25010 Software Product Quality Model |
| **Domain Bisnis** | Layanan Jasa Titip (Jastip) Parfum Vendor *In Parfume* Bandung Berbasis Pre-Order |

---

## 1. PENDAHULUAN & IDENTIFIKASI SISTEM

### 1.1 Deskripsi Umum Sistem
Uyuy Jastip adalah aplikasi web yang dirancang khusus untuk mendigitalkan, mengotomatiskan, dan menertibkan proses bisnis jasa titip (*jastip*) produk parfum konsinyasi/vendor *In Parfume* yang berpusat di Bandung. Sistem beroperasi dengan siklus pre-order berkala (sistem *batch* atau gelombang periode PO).

### 1.2 Karakteristik & Peran Pengguna (User Roles)
Sistem membagi pengguna ke dalam 3 (tiga) peran aktor dengan batas tanggung jawab dan wewenang yang terisolasi secara ketat:

1. **Pelanggan (*Customer*)**: Pengguna akhir yang mendaftarkan akun, menjelajahi katalog parfum terpopuler, memantau ketersediaan periode PO, melakukan pemesanan multi-item saat PO dibuka, mengunggah bukti transfer pembayaran rekening BCA Merchant, memantau alur status pesanannya secara real-time, dan mengajukan permohonan pembatalan jika diperlukan.
2. **Staf Operasional (*Admin*)**: Pengguna internal yang bertugas memverifikasi kesesuaian berkas transfer pembayaran manual dengan mutasi bank, memperbarui tahapan status pesanan dari pemrosesan hingga pengiriman, serta memproses permohonan pembatalan dari pelanggan. Admin dibatasi secara tegas dari fungsi manajerial bisnis.
3. **Pemilik Usaha (*Owner*)**: Pemegang hak akses eksekutif dan strategis bisnis yang berwenang membuka dan menutup periode PO, mengelola master katalog parfum (tambah, edit harga/varian, dan status aktif/nonaktif), melihat rekapitulasi kebutuhan belanja belanja ke vendor *In Parfume*, serta memantau data induk pelanggan.

---

## 2. KEBUTUHAN FUNGSIONAL (FUNCTIONAL REQUIREMENTS / FR)

Kebutuhan fungsional mendefinisikan kapabilitas, proses bisnis, dan reaksi sistem terhadap masukan dari setiap aktor yang terbagi ke dalam 7 modul utama (FR-01 s.d. FR-22).

### Modul 1: Autentikasi, Profil & Kontrol Akses (Authentication & RBAC)

#### FR-01: Registrasi Akun Pelanggan (*Customer Registration*)
* **Aktor Terkait**: Pengunjung / Tamu (*Guest*)
* **Masukan (*Input*)**: Nama lengkap, alamat email, nomor telepon/WhatsApp aktif, kata sandi, konfirmasi kata sandi, dan alamat pengiriman awal (termasuk kota dan kode pos).
* **Proses**:
  1. Sistem melakukan validasi format email, keunikan email di tabel `users`, kekuatan kata sandi, dan nomor telepon valid.
  2. Sistem mengenkripsi kata sandi menggunakan algoritma *Bcrypt*.
  3. Sistem membuat entri di tabel `users` dengan role `customer` serta entri data profil terkait di tabel `customers` dalam transaksi basis data terpadu.
* **Keluaran (*Output*)**: Akun berhasil terbentuk, pengguna otomatis diautentikasi dan dialihkan ke dasbor pelanggan dengan notifikasi sukses.

#### FR-02: Otentikasi Pengguna (*Login & Logout*)
* **Aktor Terkait**: Seluruh Pengguna (*Customer, Admin, Owner*)
* **Masukan (*Input*)**: Alamat email terdaftar dan kata sandi.
* **Proses**:
  1. Sistem memverifikasi kredensial email dan mencocokkan *hash* kata sandi.
  2. Sistem meregenerasi *Session ID* guna mencegah serangan *Session Fixation*.
  3. Sistem memeriksa nilai kolom `role` pada entitas pengguna yang berhasil masuk.
* **Keluaran (*Output*)**: Pengguna dialihkan otomatis ke antarmuka yang sesuai dengan perannya:
  * Role `customer` $\longrightarrow$ `/pelanggan/dashboard`
  * Role `admin` $\longrightarrow$ `/admin/dashboard`
  * Role `owner` $\longrightarrow$ `/owner/dashboard`
  * Jika kredensial salah, sistem menampilkan pesan error otentikasi.

#### FR-03: Pembatasan Akses Berbasis Peran (*Role-Based Access Control / RBAC*)
* **Aktor Terkait**: Sistem (*Security Middleware*)
* **Masukan (*Input*)**: Permintaan HTTP (*HTTP Request*) dan sesi pengguna aktif.
* **Proses**:
  1. Middleware `RoleMiddleware` mengintersepsi setiap rute yang dilindungi.
  2. Sistem membandingkan peran yang dimiliki pengguna terhadap peran yang disyaratkan oleh rute URL yang diminta.
* **Keluaran (*Output*)**:
  * Mengizinkan akses jika peran sesuai.
  * Menolak akses dan mengembalikan respons HTTP `403 Forbidden` jika pengguna mencoba mengakses modul di luar hak wewenangnya.
  * Mengalihkan ke halaman `/login` dengan kode HTTP `302` jika pengguna belum masuk (*guest*).

---

### Modul 2: Manajemen Periode Pre-Order (PO Period Management)

#### FR-04: Pembukaan dan Penutupan Periode Pre-Order oleh Owner
* **Aktor Terkait**: Pemilik Usaha (*Owner*)
* **Masukan (*Input*)**: Nama batch PO (misal: "PO Batch November 2026"), tanggal buka, tanggal tutup, estimasi tanggal pengiriman, dan keterangan/catatan PO.
* **Proses**:
  1. Owner dapat membuat entitas periode PO baru di basis data.
  2. Owner dapat mengaktifkan atau menutup status periode PO (`Dibuka` atau `Ditutup`) kapan saja melalui tombol aksi di dasbor owner.
  3. Sistem mencatat waktu penutupan dan pembukaan secara akurat.
* **Keluaran (*Output*)**: Status periode PO tersimpan dan langsung berdampak instan terhadap izin transaksi pelanggan di seluruh sistem.

#### FR-06: Penayangan Status & Jadwal Periode PO kepada Pelanggan
* **Aktor Terkait**: Publik & Pelanggan (*Guest & Customer*)
* **Masukan (*Input*)**: Permintaan akses halaman beranda (`/`) atau halaman informasi PO (`/informasi-po`).
* **Proses**: Sistem membaca periode PO dengan status aktif terkini dari basis data.
* **Keluaran (*Output*)**: Tampilan banner status PO aktif (`DIBUKA` dengan tanggal berakhir, atau `DITUTUP` dengan estimasi pembukaan berikutnya), jadwal kerja admin, panduan cara jastip, serta FAQ interaktif.

---

### Modul 3: Manajemen Master Katalog Produk (Product Catalog Management)

#### FR-05: Pengelolaan Master Data Produk oleh Owner
* **Aktor Terkait**: Pemilik Usaha (*Owner*)
* **Masukan (*Input*)**: Nama parfum, brand inspirasi, volume botol (35ml, 60ml, 100ml), aroma (*notes*), kategori gender (*Unisex, Pria, Wanita*), harga beli modal, harga jual titip, deskripsi wewangian, status aktif/nonaktif, dan unggahan foto produk.
* **Proses**:
  1. Sistem memvalidasi masukan dan berkas gambar (format JPG, PNG, WEBP, maks 2MB).
  2. Menyimpan berkas gambar ke direktori `public/images/products/`.
  3. Menyimpan atau memperbarui data produk pada tabel `products`.
  4. Owner dapat menonaktifkan produk tanpa menghapus data historis transaksi (*soft deactivate*).
* **Keluaran (*Output*)**: Katalog master produk terbarui di seluruh sistem.

#### FR-08A: Penayangan Katalog Produk Publik dan Halaman Rincian Produk
* **Aktor Terkait**: Publik & Pelanggan (*Guest & Customer*)
* **Masukan (*Input*)**: Kata kunci pencarian, filter kategori aroma/gender, nomor halaman katalog, atau klik pada kartu produk.
* **Proses**: Sistem mengambil data produk aktif dengan pagination (12 item per halaman) dan menampilkan kalkulator simulasi harga dinamis pada halaman detail produk (`/produk/{id}`).
* **Keluaran (*Output*)**: Antarmuka katalog responsif dan halaman rincian spesifikasi aroma parfum.

---

### Modul 4: Pengelolaan Keranjang & Transaksi Pemesanan (Order Management)

#### FR-07: Pembatasan Pemesanan Hanya pada Periode PO Berstatus Dibuka
* **Aktor Terkait**: Pelanggan (*Customer*)
* **Masukan (*Input*)**: Tombol "Tambah ke Keranjang" atau "Checkout Sekarang".
* **Proses**: Sistem memeriksa status periode PO yang sedang aktif saat ini.
* **Keluaran (*Output*)**:
  * Jika PO `Dibuka`: Item berhasil dimasukkan ke keranjang dan proses checkout dapat dilanjutkan.
  * Jika PO `Ditutup` atau tidak ada PO aktif: Sistem memblokir aksi dan menampilkan pesan peringatan: *"Pemesanan tidak dapat dilakukan karena periode Pre-Order sedang DITUTUP."*

#### FR-08B: Penyimpanan Transaksi Terintegrasi (*Order Creation*)
* **Aktor Terkait**: Pelanggan (*Customer*)
* **Masukan (*Input*)**: Konfirmasi checkout dari halaman keranjang, pilihan alamat pengiriman, dan catatan khusus.
* **Proses**:
  1. Sistem menghitung subtotal belanja produk.
  2. Sistem men-generate kode faktur transaksi unik (format: `ORD-YYYYMMDD-XXXX`).
  3. Sistem menyimpan pesanan ke tabel `orders` yang berelasi dengan `customer_id` dan `po_period_id` aktif dengan status awal `Menunggu Pembayaran`.
  4. Mengosongkan keranjang belanja pelanggan.
* **Keluaran (*Output*)**: Transaksi tersimpan dan pengguna diarahkan ke halaman detail pesanan untuk instruksi pembayaran.

#### FR-09: Dukungan Pemesanan Multi-Item (*Order Details*)
* **Aktor Terkait**: Pelanggan (*Customer*)
* **Masukan (*Input*)**: Keranjang belanja yang berisi beberapa varian parfum dengan ukuran dan kuantitas berbeda.
* **Proses**: Sistem menyimpan setiap item varian ke dalam tabel `order_details` (merekam `order_id`, `product_id`, kuantitas, ukuran botol, harga satuan saat transaksi, dan subtotal).
* **Keluaran (*Output*)**: Satu transaksi pesanan dapat menampung banyak item parfum secara akurat tanpa redundansi data.

---

### Modul 5: Pengelolaan & Verifikasi Pembayaran Manual (Payment Management)

#### FR-10: Informasi Rekening BCA Merchant & Fasilitas Unggah Bukti Transfer
* **Aktor Terkait**: Pelanggan (*Customer*)
* **Masukan (*Input*)**: Nama bank pengirim, nama pemilik rekening pengirim, tanggal transfer, catatan, dan berkas foto bukti transfer (JPG/PNG/PDF maks 2MB).
* **Proses**:
  1. Sistem menyajikan informasi nomor rekening resmi BCA Merchant atas nama Uyuy Jastip.
  2. Sistem memvalidasi berkas gambar dan menyimpannya ke `storage/app/public/payments/` dengan nama berkas acak yang aman (*hash*).
  3. Sistem membuat entri di tabel `payments` dengan status `Menunggu Verifikasi`.
* **Keluaran (*Output*)**: Berkas bukti tersimpan dan status pembayaran terbarui menjadi menunggu pemeriksaan admin.

#### FR-11: Fasilitas Pemeriksaan Bukti Pembayaran oleh Admin
* **Aktor Terkait**: Staf Operasional (*Admin*)
* **Masukan (*Input*)**: Klik pada menu daftar pembayaran atau detail pembayaran pesanan (`/admin/pembayaran/{id}/verifikasi`).
* **Proses**: Sistem menampilkan informasi pesanan, nominal yang harus dibayar, data rekening pengirim, dan pratinjau (*preview*) berkas gambar bukti transfer dengan modal pembesar gambar.
* **Keluaran (*Output*)**: Admin dapat membandingkan bukti transfer fisik dengan mutasi rekening koran BCA Merchant secara visual.

#### FR-12: Pencatatan Hasil Verifikasi Pembayaran oleh Admin
* **Aktor Terkait**: Staf Operasional (*Admin*)
* **Masukan (*Input*)**: Keputusan verifikasi (`Valid` atau `Ditolak`) dan catatan/alasan verifikasi dari admin.
* **Proses**:
  1. Jika dipilih **Valid**: Status pembayaran diubah menjadi `Valid`, dan status pesanan utama secara otomatis bertransisi menjadi `Pembayaran Diverifikasi`.
  2. Jika dipilih **Ditolak**: Status pembayaran diubah menjadi `Ditolak`, status pesanan tetap `Menunggu Pembayaran`, dan pelanggan dapat mengunggah ulang bukti transfer baru yang benar.
* **Keluaran (*Output*)**: Status transaksi terbarui seketika dan tercatat di riwayat log.

---

### Modul 6: Pelacakan Alur Status & Pembatalan Pesanan (Order Tracking & Lifecycle)

#### FR-13: Visualisasi Tahapan Alur Status Pesanan
* **Aktor Terkait**: Seluruh Pengguna (*Customer, Admin, Owner*)
* **Masukan (*Input*)**: Akses halaman detail pesanan.
* **Proses**: Sistem mengambil status terkini dan memetakan ke dalam bilah visualisasi progres 5 tahap baku:
  $$\text{Menunggu Pembayaran} \longrightarrow \text{Pembayaran Diverifikasi} \longrightarrow \text{Diproses} \longrightarrow \text{Barang Dikirim} \longrightarrow \text{Selesai}$$
* **Keluaran (*Output*)**: Komponen *stepper timeline* interaktif dengan penanda status aktif, selesai, atau status khusus `Dibatalkan`.

#### FR-14: Pengajuan Permohonan Pembatalan oleh Pelanggan
* **Aktor Terkait**: Pelanggan (*Customer*)
* **Masukan (*Input*)**: Alasan pembatalan pada formulir pembatalan pesanan (`/pelanggan/pesanan/{id}/batal`).
* **Proses**:
  1. Sistem memverifikasi bahwa status pesanan belum berstatus `Barang Dikirim` atau `Selesai`.
  2. Sistem mencatat permohonan ke dalam tabel `cancellation_requests` dengan status `Menunggu Persetujuan`.
* **Keluaran (*Output*)**: Permohonan pembatalan diteruskan ke antrean peninjauan admin.

#### FR-15: Pemrosesan Permohonan Pembatalan oleh Admin
* **Aktor Terkait**: Staf Operasional (*Admin*)
* **Masukan (*Input*)**: Keputusan admin (`Disetujui` atau `Ditolak`) dan catatan putusan admin.
* **Proses**:
  1. Jika **Disetujui**: Status permohonan berubah menjadi `Disetujui`, status pesanan utama diperbarui menjadi `Dibatalkan`, dan seluruh proses pesanan dihentikan.
  2. Jika **Ditolak**: Status permohonan berubah menjadi `Ditolak`, dan status pesanan utama tetap berjalan pada tahapan semula.
* **Keluaran (*Output*)**: Keputusan tersimpan dan pelanggan dapat melihat alasan putusan admin pada detail pesanannya.

#### FR-16: Penayangan Riwayat Pesanan Pelanggan
* **Aktor Terkait**: Pelanggan (*Customer*)
* **Masukan (*Input*)**: Permintaan halaman `/pelanggan/pesanan`.
* **Proses**: Sistem memuat daftar seluruh pesanan yang pernah dibuat oleh akun pelanggan yang sedang aktif login dengan pengurutan waktu terbaru.
* **Keluaran (*Output*)**: Tabel riwayat pesanan dengan badge status warna, total harga, jumlah item, dan tombol tautan detail pesanan.

#### FR-19: Audit Trail Riwayat Perubahan Status Pesanan (*Order Status Histories*)
* **Aktor Terkait**: Sistem
* **Masukan (*Input*)**: Setiap kali terjadi perubahan status pesanan pada alur operasional.
* **Proses**: Sistem secara otomatis memasukkan rekaman log ke tabel `order_status_histories` yang mencatat: `order_id`, `status_sebelum`, `status_sesudah`, `user_id` pengubah, catatan perubahan, dan stempel waktu (*timestamp*).
* **Keluaran (*Output*)**: Jejak audit historis lengkap yang dapat diaudit kapan saja.

#### FR-20: Penegakan Isolasi Data Pelanggan (*Customer Data Isolation*)
* **Aktor Terkait**: Sistem (*Authorization Policy*)
* **Masukan (*Input*)**: Upaya akses URL detail pesanan pelanggan lain (misal Customer A membuka `/pelanggan/pesanan/99` milik Customer B).
* **Proses**: Sistem memeriksa kepemilikan relasi antara `customer_id` pada pesanan dengan ID profil pengguna yang sedang login melalui method otorisasi controller.
* **Keluaran (*Output*)**: Sistem memblokir akses dan mengembalikan respons HTTP `403 Forbidden` jika identitas pemilik tidak cocok.

---

### Modul 7: Rekapitulasi & Pelaporan Eksekutif (Recap & Boundary Enforcement)

#### FR-17: Rekapitulasi Belanja Vendor Per Periode PO untuk Owner
* **Aktor Terkait**: Pemilik Usaha (*Owner*)
* **Masukan (*Input*)**: Pilihan filter periode PO pada halaman `/owner/rekapitulasi`.
* **Proses**: Sistem mengagregasikan seluruh pesanan berstatus valid (`Pembayaran Diverifikasi`, `Diproses`, `Barang Dikirim`, `Selesai`) dalam periode bersangkutan, lalu menghitung:
  * Total botol yang wajib dibeli ke vendor *In Parfume* dirinci per varian aroma dan ukuran volume botol.
  * Total modal belanja yang harus disiapkan.
  * Total omzet penjualan jastip.
  * Estimasi laba bersih (*profit margin*).
* **Keluaran (*Output*)**: Lembar rekapitulasi belanja operasional dan ringkasan eksekutif finansial.

#### FR-18: Fasilitas Penyaringan (*Filtering*) dan Pencarian Data Transaksi
* **Aktor Terkait**: Admin & Owner
* **Masukan (*Input*)**: Filter periode PO, filter status pesanan, dan kotak pencarian nomor faktur atau nama pelanggan.
* **Proses**: Sistem menjalankan query pencarian terindeks dengan filter bersyarat pada tabel `orders`.
* **Keluaran (*Output*)**: Daftar transaksi yang sesuai kriteria pencarian dalam waktu singkat.

#### FR-21: Penegakan Batasan Kewenangan Operasional Admin (*Administrative Boundary*)
* **Aktor Terkait**: Sistem (*Route Protection*)
* **Masukan (*Input*)**: Upaya staf Admin mengakses rute manajerial Owner (`/owner/periode-po`, `/owner/produk`, `/owner/rekapitulasi`, `/owner/pelanggan`).
* **Proses**: Sistem mendeteksi role pengguna adalah `admin`, bukan `owner`.
* **Keluaran (*Output*)**: Sistem langsung menolak permintaan dengan HTTP `403 Forbidden`, memastikan Admin murni hanya menjalankan fungsi verifikasi dan pergerakan status logistik.

#### FR-22: Pengelolaan dan Kebijakan Retensi Berkas Bukti Pembayaran
* **Aktor Terkait**: Sistem
* **Masukan (*Input*)**: Berkas bukti transfer yang telah berumur lebih dari 30 hari kalender sejak pesanan berstatus `Selesai` atau `Dibatalkan`.
* **Proses**: Sistem mencatat stempel waktu pengunggahan pada tabel `payments` dan menyediakan logika pengelolaan berkas untuk membersihkan bukti fisik lama demi menghemat kapasitas penyimpanan server tanpa merusak integritas angka transaksi keuangan.
* **Keluaran (*Output*)**: Tata kelola penyimpanan berkas bukti transfer yang bersih dan tertib.

---

## 3. MATRIKS KEBUTUHAN FUNGSIONAL VS AKTOR SISTEM

Tabel berikut menyajikan pemetaan keterkaitan antara setiap butir Functional Requirement dengan hak akses para aktor:

| Kode FR | Nama Kebutuhan Fungsional | Customer | Admin | Owner | Guest |
| :---: | :--- | :---: | :---: | :---: | :---: |
| **FR-01** | Registrasi Akun Pelanggan Baru | ❌ | ❌ | ❌ | ✅ |
| **FR-02** | Otentikasi Login dan Logout Sistem | ✅ | ✅ | ✅ | ✅ |
| **FR-03** | Pembatasan Hak Akses Berbasis Peran (RBAC) | ✅ | ✅ | ✅ | ✅ |
| **FR-04** | Buka / Tutup Periode Pre-Order | ❌ | ❌ | ✅ | ❌ |
| **FR-05** | Manajemen Master Data Produk Parfum | ❌ | ❌ | ✅ | ❌ |
| **FR-06** | Penayangan Status & Jadwal Periode PO | ✅ | ✅ | ✅ | ✅ |
| **FR-07** | Pembatasan Checkout saat Periode PO Dibuka | ✅ | ❌ | ❌ | ❌ |
| **FR-08** | Penyimpanan Transaksi Terintegrasi | ✅ | ❌ | ❌ | ❌ |
| **FR-09** | Pemesanan Multi-Item (*Order Details*) | ✅ | ❌ | ❌ | ❌ |
| **FR-10** | Info Rekening BCA & Unggah Bukti Bayar | ✅ | ❌ | ❌ | ❌ |
| **FR-11** | Fasilitas Pemeriksaan Berkas Bukti Bayar | ❌ | ✅ | ✅ | ❌ |
| **FR-12** | Penetapan Status Verifikasi Pembayaran | ❌ | ✅ | ❌ | ❌ |
| **FR-13** | Visualisasi Alur Status 5 Tahap Baku | ✅ | ✅ | ✅ | ❌ |
| **FR-14** | Pengajuan Pembatalan oleh Pelanggan | ✅ | ❌ | ❌ | ❌ |
| **FR-15** | Pemrosesan Permohonan Pembatalan | ❌ | ✅ | ❌ | ❌ |
| **FR-16** | Penayangan Riwayat Pesanan Pribadi | ✅ | ❌ | ❌ | ❌ |
| **FR-17** | Rekapitulasi Belanja Vendor Per Periode PO | ❌ | ❌ | ✅ | ❌ |
| **FR-18** | Filter & Pencarian Data Transaksi Pesanan | ❌ | ✅ | ✅ | ❌ |
| **FR-19** | Pencatatan Audit Trail Riwayat Status Pesanan | Sistem | Sistem | Sistem | ❌ |
| **FR-20** | Penegakan Isolasi Data Antar-Pelanggan | Guard | Guard | Guard | ❌ |
| **FR-21** | Pembatasan Kewenangan Operasional Admin | Guard | Guard | Guard | ❌ |
| **FR-22** | Pengelolaan Retensi Berkas Bukti Transfer | Sistem | Sistem | Sistem | ❌ |

---

## 4. KEBUTUHAN NON-FUNGSIONAL (NON-FUNCTIONAL REQUIREMENTS / NFR)

Kebutuhan non-fungsional disusun berdasarkan parameter standar mutu internasional **ISO/IEC 25010 Software Quality Model**:

### 4.1 Usability (Kegunaan & Desain Antarmuka)
* **NFR-01 (Kerapian & Konsistensi Desain Visual)**: Antarmuka sistem mengusung estetika *Earthy Luxury* yang merefleksikan identitas wewangian premium (*warm cream*, *soft beige*, *rich warm brown*, dan *gold accents*). Menggunakan tipografi terpadu: font serif *Playfair Display* untuk judul/heading dan sans-serif *Inter* untuk konten tubuh. Komponen antarmuka (kartu produk, tabel, form, modal, tombol) harus seragam di seluruh modul.
* **NFR-02 (Responsivitas & Kompatibilitas Multi-Perangkat)**: Sistem wajib responsif (*mobile-friendly*), dapat digunakan tanpa hambatan horizontal scrolling pada perangkat ponsel pintar (*smartphone* dengan lebar mulai dari 360px), tablet, laptop, hingga layar monitor desktop (1920px).

### 4.2 Security & Data Privacy (Keamanan & Privasi Data)
* **NFR-03 (Keamanan Otentikasi & Masukan Web)**:
  * Kata sandi akun wajib dienkripsi satu arah dengan algoritma *Bcrypt* dengan *work factor* standar Laravel.
  * Setiap formulir HTTP POST/PUT/DELETE wajib menyertakan token *Cross-Site Request Forgery (CSRF)* yang divalidasi oleh framework.
  * Semua masukan form wajib disanitasi dan divalidasi melalui *Form Request Validation* untuk mencegah injeksi kode berbahaya (*Cross-Site Scripting / XSS*).
  * Seluruh interaksi query basis data wajib menggunakan *Prepared Statements* via Eloquent ORM guna mengeliminasi kerentanan *SQL Injection*.
* **NFR-04 (Isolasi & Privasi Data Pelanggan / Anti-IDOR)**: Sistem wajib menjamin bahwa data profil, nomor telepon, alamat rumah, berkas bukti transfer, dan riwayat pesanan milik seorang pelanggan terisolasi secara mutlak dari pelanggan lain. Percobaan pembacaan paksa data pelanggan lain melalui manipulasi parameter ID pada URL (*Insecure Direct Object Reference / IDOR*) wajib ditangkal oleh *Controller Authorization Gate*.
* **NFR-10 (Penegakan Otorisasi Tingkat Backend)**: Pembatasan hak akses tidak boleh hanya dilakukan di sisi tampilan (*UI hiding*), melainkan ditegakkan secara mutlak pada tingkat *backend route middleware* dan *controller logic*.

### 4.3 Reliability & Data Integrity (Keandalan & Integritas Data)
* **NFR-05 (Konsistensi Transaksional Basis Data)**: Proses pembentukan transaksi pesanan (*checkout* multi-item), pembayaran, dan pembaruan status wajib dibungkus dalam mekanisme *Database Transactions* (`DB::transaction`). Apabila terjadi kegagalan sistem pada salah satu sub-proses, seluruh transaksi wajib dibatalkan (*rollback*) guna mencegah inkonsistensi data.
* **NFR-09 (Keterlacakan Sistem & Auditability)**: Setiap perubahan status transaksi dan berkas pembayaran wajib tercatat pada tabel `order_status_histories` dengan mencantumkan stempel waktu (*timestamp*), status sebelumnya, status sesudahnya, serta identitas akun yang mengeksekusi perubahan.

### 4.4 Performance & Scalability (Performa & Skalabilitas)
* **NFR-07 (Kapasitas Operasional Batch Pre-Order)**: Infrastruktur basis data dan logika aplikasi harus mampu menangani sedikitnya **200 pelanggan aktif** per periode PO dengan ratusan item transaksi secara simultan dengan waktu respons halaman di bawah 2 detik pada kondisi koneksi internet normal.
* **NFR-08 (Skalabilitas Master Produk)**: Sistem tidak menerapkan batasan arbitrer terhadap jumlah item katalog produk parfum (mampu menampung ribuan varian aroma *In Parfume* dengan query terindeks pada kolom kategori dan nama).

### 4.5 Maintainability (Kemudahan Pemeliharaan)
* **NFR-06 (Arsitektur Perangkat Lunak Terstruktur)**: Sistem dibangun berlandaskan arsitektur **Model-View-Controller (MVC)** dari Laravel 12. Terdapat pemisahan tegas antara logika bisnis (*Controllers*), pemodelan entitas (*Eloquent Models & Relationships*), dan antarmuka (*Blade Layouts & Components*), sehingga kode program bersih, mudah dibaca, dan mudah dikembangkan lebih lanjut.

### 4.6 Storage Management & Retention (Manajemen Berkas)
* **NFR-11 (Kebijakan Retensi Berkas Pembayaran)**: Berkas bukti transfer yang diunggah pelanggan diberi penamaan acak unik (*hash filename*) dan disimpan terisolasi di direktori privat `storage/app/public/payments/`. Sistem mencatat stempel waktu pengunggahan yang mendukung kebijakan pembersihan berkas berumur lebih dari 30 hari demi menjaga efisiensi kapasitas disk server.

---

## 5. STATUS KETERLACAKAN & VERIFIKASI (TRACEABILITY STATUS)

Seluruh butir spesifikasi kebutuhan di atas telah diimplementasikan dan diverifikasi secara komprehensif melalui rangkaian pengujian otomatis (*Automated Integration Test Suite*):

* **Total Skenario Pengujian**: 52 Uji Fungsional & Batasan Akses
* **Hasil Pengujian**: 52 Lulus (*Passed*), 0 Gagal (*Failed*) — Tingkat Keberhasilan 100%
* **Dokumen Rujukan Terkait**:
  1. [PROJECT_PLAN.md](file:///e:/CAPSTONE/Capstone/PROJECT_PLAN.md) — Kerangka kerja dan perencanaan proyek.
  2. [DATABASE_DESIGN.md](file:///e:/CAPSTONE/Capstone/DATABASE_DESIGN.md) — Desain skema relasional 7 entitas utama dan 3 tabel pendukung.
  3. [TESTING_REPORT.md](file:///e:/CAPSTONE/Capstone/TESTING_REPORT.md) — Laporan lengkap hasil uji 52 skenario otomasi.
  4. [UI_UX_GUIDELINE.md](file:///e:/CAPSTONE/Capstone/UI_UX_GUIDELINE.md) — Panduan desain visual *Earthy Luxury* dan komponen Bootstrap.

---
*Dokumen ini diterbitkan sebagai spesifikasi resmi Kebutuhan Fungsional (FR) dan Non-Fungsional (NFR) untuk Proyek Capstone Design Sistem Informasi Uyuy Jastip.*

