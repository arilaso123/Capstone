# LAPORAN EVALUASI PERJALANAN PENGGUNA (USER JOURNEY REPORT)
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Fase:** Phase 2 Deliverable 4  
**Tanggal Evaluasi:** 13 November 2026  
**Auditor:** Senior UX Researcher, Product Designer, Lead Frontend Engineer  
**Klasifikasi:** Analisis Alur Pengguna & Titik Gesekan Interaksi (Friction Points)  

---

## 1. PENDAHULUAN & PENDEKATAN EVALUASI

Evaluasi alur pengguna (*User Journey Evaluation*) bertujuan memastikan bahwa antarmuka Uyuy Jastip tidak hanya terlihat estetis saat dilihat per halaman, namun mampu memandu ketiga aktor utama (Guest/Customer, Admin Operasional, dan Owner) menyelesaikan tujuan bisnisnya secara mulus (*seamless*), cepat, dan bebas dari kebingungan (*cognitive overload*) maupun potensi kesalahan manusia (*human error*).

Lima skenario perjalanan bisnis utama diuji secara non-destruktif (*read-only*) melalui live browser:

```
+--------------------------------------------------------------------------------------------------+
|                                  PETA 5 USER JOURNEY BISNIS                                      |
+--------------------------------------------------------------------------------------------------+
| Journey 1 (Guest)    : Home -> Katalog -> Detail Produk -> Info PO -> Registrasi / Masuk         |
| Journey 2 (Customer) : Masuk -> Katalog -> Detail -> Keranjang -> Checkout -> Instruksi Bayar    |
| Journey 3 (Customer) : Dashboard Pelanggan -> Riwayat Pesanan -> Detail Pesanan -> Linimasa Resi |
| Journey 4 (Admin)    : Dashboard Operasional -> Antrean Bayar -> Verifikasi Bukti -> Status Order |
| Journey 5 (Owner)    : Dashboard Eksekutif -> Kontrol PO -> Master Produk -> Rekapitulasi Finansial|
+--------------------------------------------------------------------------------------------------+
```

---

## 2. DETAIL EVALUASI 5 USER JOURNEY

---

### JOURNEY 1 — GUEST DISCOVERS PRODUCT & PRE-ORDER SCHEDULE
*Eksplorasi Katalog & Pemahaman Sistem Jasa Titip oleh Pengunjung Baru*

- **User Goal:** Calon pembeli ingin mengetahui varian parfum apa saja yang tersedia, berapa harganya, bagaimana sistem pre-order bekerja, dan kapan pesanan dapat dibuat.
- **Starting Point:** Halaman Beranda (`/`).
- **Sequence of Steps:**
  1. Pengunjung membuka Beranda (`/`) dan membaca judul hero serta kartu jadwal PO aktif.
  2. Mengklik tombol "Jelajahi katalog ->" menuju halaman Katalog (`/katalog`).
  3. Memfilter produk berdasarkan kategori aroma (misal: *Amber Floral*) dan memilih salah satu produk (misal: *Aura Botanica*).
  4. Membuka halaman Detail Produk (`/produk/1`) dan memilih varian ukuran botol (35ml, 60ml, 100ml) untuk membandingkan harga.
  5. Mengklik menu "Informasi PO" (`/informasi-po`) untuk memahami jadwal pengiriman dan ketentuan transfer BCA.
  6. Mengklik tombol "Daftar" (`/register`) untuk membuat akun pembeli.
- **Expected Result:** Pengunjung memahami konsep pre-order Uyuy Jastip, mengetahui kapan PO dibuka/ditutup, dan tertarik mendaftar akun untuk bertransaksi.
- **Observed Friction (Titik Hambatan):**
  - Pada halaman katalog, saat memfilter kategori aroma tertentu, tidak ada ringkasan jumlah produk yang cocok (hanya grid produk langsung).
  - Pada halaman detail produk, bila periode PO sedang ditutup, tombol disabled menampilkan teks "PO Ditutup" tanpa estimasi tanggal PO berikutnya.
- **Severity:** **Low (P3)**.
- **Recommended Improvement:**
  - Tambahkan teks penghitung hasil filter katalog: *"Menampilkan 8 produk dalam kategori Amber Floral"*.
  - Tambahkan catatan tanggal pembukaan PO berikutnya di bawah tombol disabled saat periode PO tidak aktif.

---

### JOURNEY 2 — CUSTOMER PLACES AN ORDER
*Proses Pembelian & Checkout oleh Pelanggan saat Periode PO Dibuka*

- **User Goal:** Pelanggan yang telah masuk ingin memesan parfum pilihan, menentukan ukuran dan kuantitas, mengisi alamat penerima, dan menerima petunjuk transfer BCA Merchant.
- **Starting Point:** Halaman Masuk (`/login`).
- **Sequence of Steps:**
  1. Pelanggan memasukkan email dan password, berhasil login dan diarahkan ke Dashboard Pelanggan (`/pelanggan/dashboard`).
  2. Mengklik tab "Katalog parfum" dan memilih produk yang diminati.
  3. Memilih ukuran botol (misal: 60ml), memasukkan jumlah (misal: 2 botol), dan mengklik "Tambah ke Keranjang".
  4. Diarahkan ke halaman Keranjang Belanja (`/pelanggan/keranjang`) yang memuat rincian item dan total belanja.
  5. Mengklik tombol "Lanjut ke Pembayaran / Checkout".
  6. Memeriksa ringkasan pesanan, mengisi formulir nama penerima, nomor WhatsApp, alamat tujuan, kode pos, dan catatan.
  7. Mengklik "Konfirmasi & Buat Pesanan" (Dilindungi double-submit guard).
  8. Menerima nomor pesanan berformat ULID dan diarahkan ke halaman instruksi pembayaran BCA Merchant.
- **Expected Result:** Transaksi pesanan tercipta secara atomik dengan kalkulasi harga server-side murni, keranjang dikosongkan, dan tagihan pesanan berstatus *Menunggu Pembayaran*.
- **Observed Friction (Titik Hambatan):**
  - Pada halaman keranjang, mengubah angka kuantitas membutuhkan klik manual tombol simpan kecil di samping input. Beberapa pengguna awam mungkin mengira kuantitas otomatis tersimpan hanya dengan mengetik angka.
- **Severity:** **Medium (P2)**.
- **Recommended Improvement:**
  - Poles tombol simpan kuantitas dengan indikator visual *"Simpan perubahan"* yang lebih menonjol ketika nilai angka dalam input berubah, atau tambahkan tombol plus/minus (+/-) yang mempermudah interaksi di layar sentuh ponsel.

---

### JOURNEY 3 — CUSTOMER TRACKS AN ORDER & PAYMENT
*Pemantauan Status Pesanan & Unggah Bukti Transfer oleh Pelanggan*

- **User Goal:** Pelanggan ingin memeriksa status pembayarannya, mengunggah struk transfer bank jika diminta ulang, dan memantau posisi pengiriman paket parfum.
- **Starting Point:** Dashboard Pelanggan (`/pelanggan/dashboard`).
- **Sequence of Steps:**
  1. Pelanggan membuka dashboard akun dan melihat 4 kartu status (Menunggu pembayaran, Sedang diproses, Selesai).
  2. Mengklik tab "Pesanan saya" (`/pelanggan/pesanan`) untuk melihat daftar seluruh transaksi.
  3. Mengklik baris pesanan aktif (misal: pesanan `#UJ-PO10-20261029-001`) menuju halaman Detail Pesanan.
  4. Memeriksa komponen visual stepper *Perjalanan pesanan* untuk melihat di mana posisi pesanan saat ini.
  5. Jika pesanan membutuhkan pembayaran dan belum kedaluwarsa, mengklik tombol "Unggah Bukti Pembayaran" (`/pelanggan/pesanan/{id}/pembayaran`).
  6. Mengunggah struk transfer dan memasukkan nominal transfer.
  7. Jika pesanan telah dikirim, menyalin nomor resi pengiriman untuk dicek di situs ekspedisi.
- **Expected Result:** Pelanggan mendapatkan transparansi penuh atas uang yang telah ditransfer dan progres fisik barang belanjaannya.
- **Observed Friction (Titik Hambatan):**
  - Pada pesanan seeder lama yang batas bayarnya telah lampau (`2026-10-30`), mengklik unggah pembayaran mengalihkan ke detail pesanan dengan alert amber. Penjelasan alasan pengalihan sudah tepat, namun tidak menyertakan tombol bantuan "Hubungi Admin via WhatsApp" jika pelanggan merasa sudah transfer sebelum batas waktu.
- **Severity:** **Medium (P2)**.
- **Recommended Improvement:**
  - Tambahkan tombol pintasan *"Hubungi Admin Jastip via WhatsApp"* pada banner pesanan kedaluwarsa agar pelanggan dapat mengonfirmasi transfer manual jika terjadi keterlambatan konfirmasi mutasi bank.

---

### JOURNEY 4 — ADMIN PROCESSES ORDER & PAYMENT QUEUE
*Pengelolaan Transaksi Harian & Penanganan Antrean Pembayaran oleh Staf Operasional*

- **User Goal:** Staf admin (2 orang) yang bekerja pada jam 09.00–19.00 WIB ingin memproses antrean verifikasi pembayaran, menyetujui/menolak bukti transfer berdasarkan mutasi BCA Merchant, dan memperbarui status pesanan yang siap dikirim.
- **Starting Point:** Dashboard Admin Operasional (`/admin/dashboard`).
- **Sequence of Steps:**
  1. Staf membuka dashboard operasional dan langsung melihat kartu antrean *"1 Menunggu verifikasi pembayaran (Prioritas)"*.
  2. Mengklik tombol *"Buka antrean ->"* langsung menuju `/admin/pembayaran`.
  3. Memilih pembayaran yang perlu diperiksa dan mengklik *"Verifikasi"* menuju `/admin/pembayaran/{id}/verifikasi`.
  4. Memeriksa kecocokan nominal tagihan vs mutasi bank dan melihat pratinjau foto struk transfer.
  5. Mengklik tombol gelap *"Verifikasi Valid"* (Pesanan otomatis berpindah ke *Pembayaran Diverifikasi*).
  6. Buka menu *"Daftar Pesanan"* (`/admin/pesanan`), buka pesanan yang telah dipacking, perbarui status menjadi *"Barang Dikirim"*, serta masukkan nama kurir dan nomor resi pengiriman.
- **Expected Result:** Antrean terproses dengan cepat (*high task efficiency*), tanpa risiko salah klik, dan histori perubahan status tercatat secara permanen dengan identitas staf (`changed_by`).
- **Observed Friction (Titik Hambatan):**
  - Pratinjau bukti transfer membuka gambar di tab peramban baru saat diklik, memaksa staf berpindah-pindah tab browser ketika menangani puluhan pembayaran berturut-turut.
  - Pilihan nama kurir harus diketik teks manual (*free text*), rawan kesalahan ketik (*typo*) nama kurir (misal: "JNE", "jne reguler", "J&T Express").
- **Severity:** **Medium (P2)**.
- **Recommended Improvement:**
  - Sediakan pop-up modal lightbox zoom in-page pada gambar struk bukti transfer.
  - Ubah input kurir menjadi dropdown pilihan cepat dengan opsi ekspedisi populer (JNE, J&T, SiCepat, Paxel, GoSend) ditambah opsi "Lainnya".

---

### JOURNEY 5 — OWNER MANAGES PO PERIOD & RECAPITULATION
*Manajemen Siklus PO, Kontrol Katalog, dan Analisis Belanja oleh Pemilik Usaha*

- **User Goal:** Owner ingin memantau omzet mingguan, menutup periode PO yang telah selesai, membawa daftar rekapitulasi belanja parfum ke vendor grosir, dan memperbarui harga produk.
- **Starting Point:** Dashboard Eksekutif Owner (`/owner/dashboard`).
- **Sequence of Steps:**
  1. Owner membuka dashboard bisnis dan memeriksa total omzet pembayaran valid (`Rp 1.025.000`) dan distribusi status pesanan.
  2. Mengklik tombol *"Tutup Periode"* pada kartu PO aktif ketika rentang tanggal pre-order telah berakhir.
  3. Membuka menu *"Rekapitulasi PO"* (`/owner/rekapitulasi`) untuk periode yang baru ditutup.
  4. Memeriksa tabel pengadaan barang: melihat total botol yang harus dibeli per aroma dan per ukuran botol (35ml, 60ml, 100ml).
  5. Mengklik tombol *"Cetak Rekap"* untuk mencetak daftar belanja dalam format kertas atau PDF.
  6. Membuka menu *"Manajemen Produk"* (`/owner/produk`) untuk mengubah harga atau foto parfum yang stoknya berubah di vendor grosir.
- **Expected Result:** Owner memiliki kontrol penuh terhadap siklus PO, belanja grosir dapat dilakukan secara akurat tanpa kalkulasi manual, dan katalog produk selalu terbarui.
- **Observed Friction (Titik Hambatan):**
  - **[CRITICAL BLOCKER]:** Saat Owner mencoba mengklik tombol *"Edit"* pada salah satu produk di daftar katalog (`/owner/produk/1/edit`), halaman mengalami crash **HTTP 500 ParseError**. Owner tidak dapat memperbarui harga atau foto produk sama sekali.
  - Pada laporan rekapitulasi, data modal nol (`Rp 0`) karena produk historis belum diinput harga modalnya, sehingga kolom estimasi laba bersih belum mencerminkan angka riil.
- **Severity:** **Critical (P0) pada halaman edit produk; Medium (P2) pada rekapitulasi modal.**
- **Recommended Improvement:**
  - **Prioritas Utama P0:** Perbaiki sintaksis Blade pada `resources/views/owner/products/edit.blade.php` agar halaman edit produk dapat dibuka kembali.
  - Tambahkan label penjelasan pada kartu modal di rekapitulasi bahwa modal dihitung dari produk yang memiliki data harga modal.

---

## 3. MATRIKS FRICTION POINTS & REKOMENDASI TERPRIORITAS

| Journey | Titik Gesekan Interaksi | Dampak Pengguna | Severity | Prioritas | Solusi yang Direkomendasikan |
| :---: | :---| :---| :---: | :---: | :---|
| **J-05** | Crash HTTP 500 saat membuka Edit Produk | Owner tidak dapat mengubah harga atau foto produk | **Critical** | **P0** | Perbaiki parsing Blade inline `@if` di `edit.blade.php` |
| **J-04** | Pratinjau bukti bayar membuka tab peramban baru | Memperlambat staf admin memproses antrean verifikasi | **Medium** | **P2** | Implementasi modal lightbox zoom in-page |
| **J-04** | Input kurir pengiriman berupa teks manual bebas | Rawan salah ketik format ekspedisi oleh admin | **Low** | **P3** | Sediakan dropdown ekspedisi standar (JNE, J&T, SiCepat) |
| **J-02** | Tombol perbarui kuantitas keranjang kurang mencolok | Pelanggan bingung mengapa total belanja belum berubah | **Medium** | **P2** | Beri penanda visual "Simpan" aktif saat angka berubah |
| **J-03** | Rekening BCA belum memiliki feedback visual "Tersalin!" | Pelanggan ragu apakah nomor rekening berhasil disalin | **Medium** | **P2** | Tambahkan tooltip feedback mikro "Tersalin!" selama 2 detik |
| **J-03** | Pesanan kedaluwarsa tidak memiliki tombol kontak admin | Pelanggan bingung jika sudah transfer terlambat | **Low** | **P3** | Tambahkan tombol tautan cepat WhatsApp Admin |
| **J-01** | Filter katalog tidak menampilkan counter hasil pencarian | Pengunjung tidak tahu jumlah varian yang sesuai filter | **Low** | **P3** | Tampilkan teks counter *"Menampilkan X produk"* |

---

## 4. KESIMPULAN USER JOURNEY

Kelima perjalanan pengguna utama pada Uyuy Jastip telah **terancang dengan sangat logis dan berorientasi proses bisnis jasa titip nyata**. Tidak ditemukan jalan buntu navigasi pada alur belanja maupun alur operasional verifikasi. Dengan menyelesaikan hambatan kritis pada Journey 5 (Edit Produk Owner) dan mengimplementasikan polishing pada Journey 2, 3, dan 4, seluruh alur bisnis akan mencapai tingkat kematangan dan kenyamanan operasional yang optimal.
