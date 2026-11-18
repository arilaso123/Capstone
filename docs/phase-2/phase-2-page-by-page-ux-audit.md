# AUDIT UX & INTERAKSI HALAMAN-PER-HALAMAN (PAGE-BY-PAGE UX AUDIT)
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Fase:** Phase 2 Deliverable 3  
**Tanggal Evaluasi:** 13 November 2026  
**Auditor:** Senior Product Designer, Usability Analyst, Information Architecture Specialist  
**Klasifikasi:** Analisis Detail Evaluasi & Rekomendasi Solutif Halaman  

---

## 1. STRUKTUR & METODOLOGI ANALISIS

Setiap halaman dievaluasi menggunakan format baku 9-dimensi sesuai standar Phase 2:
1. **Current State:** Kondisi tampilan aktual hasil render live browser.
2. **Strengths:** Aspek visual dan fungsional yang sudah baik dan wajib dipertahankan.
3. **Problems:** Cacat visual, usability, hierarki informasi, atau aksesibilitas yang teramati.
4. **Root Cause:** Akar masalah teknis pada Blade, CSS, struktur data, atau interaksi JS.
5. **Proposed Improvement:** Rekomendasi perubahan konkret dan dapat diterapkan.
6. **Expected User Experience:** Dampak pengalaman pengguna yang diharapkan pasca-perbaikan.
7. **Affected Components:** File Blade, CSS, dan JavaScript yang terdampak.
8. **Priority:** Klasifikasi P0 (Kritis), P1 (Tinggi), P2 (Menengah), atau P3 (Rendah).
9. **Acceptance Criteria:** Kondisi objektif teruji untuk validasi keberhasilan implementasi.

---

## 2. KELOMPOK 1: PUBLIC STOREFRONT (ETALASE PUBLIK)

---

### HALAMAN 1: Beranda Utama (`/`)
- **1. Current State:** Hero dua kolom memuat judul besar "Temukan aroma pilihanmu.", pengantar pre-order, dua tombol aksi, dan kartu jadwal PO aktif berdampingan dengan foto produk. Bagian bawah memuat grid katalog unggulan, alur kerja 4-langkah pre-order, dan footer mineral gelap. Bukti tangkapan layar: `docs/phase-2/evidence/public/public-home-desktop.png` dan `public-home-mobile.png`.
- **2. Strengths:** Nilai estetika kurasi parfum sangat terasa; informasi periode PO dibuka langsung terlihat di bagian atas tanpa perlu scroll (*above the fold*); tata letak responsif 100% tanpa horizontal overflow pada 360px.
- **3. Problems:** Pada resolusi ultra-wide 1920px (`public-home-full_hd.png`), jarak antara teks hero kiri dan kartu PO kanan terasa agak renggang karena lebar kontainer tetap 1280px di tengah layar.
- **4. Root Cause:** Properti `--container-content: 1280px` pada CSS membatasi rentang grid hero di layar lebar.
- **5. Proposed Improvement:** Pertahankan layout yang sudah ada (KEEP). Tambahkan sedikit padding horizontal adaptif pada hero section di layar `>= 1600px` untuk menjaga keseimbangan komposisi.
- **6. Expected User Experience:** Pengunjung dengan monitor besar merasakan komposisi yang lebih menyatu secara sinematik.
- **7. Affected Components:** [resources/views/home.blade.php](file:///e:/Codex/resources/views/home.blade.php), [resources/css/uyuy-theme.css](file:///e:/Codex/resources/css/uyuy-theme.css).
- **8. Priority:** **P3 (Low)**
- **9. Acceptance Criteria:** Hero tetap rapi di 1440px dan 1920px tanpa text breaking yang janggal.

---

### HALAMAN 2: Katalog Produk (`/katalog`)
- **1. Current State:** Halaman menyajikan bilah filter aroma horizontal, kolom pencarian nama parfum, status periode PO mini di bagian atas, dan grid kartu produk parfum 3 kolom di desktop serta 1 kolom di mobile compact. Bukti: `docs/phase-2/evidence/public/public-catalog-desktop.png` dan `public-catalog-mobile.png`.
- **2. Strengths:** Filter kategori aroma bekerja cepat; foto produk konsisten rasio 1:1; kartu produk menampilkan lencana ketersediaan ukuran botol (35ml, 60ml, 100ml) dan harga "Mulai Rp ...".
- **3. Problems:** Ketika filter tidak menemukan produk, teks pesan kosong (*empty state*) masih menggunakan alert Bootstrap standar yang agak kaku.
- **4. Root Cause:** Blade template menggunakan div `.alert.alert-info` standar tanpa ilustrasi atau tombol reset filter.
- **5. Proposed Improvement:** Poles (*POLISH*) pesan hasil pencarian kosong menjadi komponen empty state Editorial Stone dengan tombol tautan "Reset filter / Tampilkan semua produk".
- **6. Expected User Experience:** Pengunjung yang salah mengetik kata kunci tidak terbentur jalan buntu dan dapat kembali menjelajah katalog dalam 1 klik.
- **7. Affected Components:** [resources/views/catalog/index.blade.php](file:///e:/Codex/resources/views/catalog/index.blade.php).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Pencarian kata kunci acak memunculkan pesan ramah dan tombol reset yang mengembalikan katalog lengkap.

---

### HALAMAN 3: Detail Produk (`/produk/{id}`)
- **1. Current State:** Tata letak dua kolom: galeri foto produk di sisi kiri dan panel informasi di sisi kanan (kategori, nama, deskripsi aroma, pemilih ukuran botol radio pill, input jumlah, serta tombol "Tambah ke Keranjang"). Bukti: `docs/phase-2/evidence/public/public-product-detail-desktop.png` dan `public-product-detail-mobile.png`.
- **2. Strengths:** Pemilihan radio ukuran botol (35ml, 60ml, 100ml) secara instan memperbarui tampilan harga satuan melalui JavaScript native tanpa reload halaman; tombol kuantitas memiliki batas minimal 1 dan maksimal 50.
- **3. Problems:** Bila periode PO sedang ditutup, tombol submit keranjang berubah menjadi disabled dengan teks "PO Ditutup", namun penjelasan tanggal PO berikutnya belum dicantumkan secara berdampingan di bawah tombol.
- **4. Root Cause:** Komponen belum menyertakan cuplikan informasi jadwal pembukaan PO selanjutnya saat status periode tidak aktif.
- **5. Proposed Improvement:** Tambahkan catatan informatif di bawah tombol disabled: "Periode pemesanan berikutnya akan dibuka pada [Tanggal]. Ikuti pengumuman di WhatsApp/Instagram kami."
- **6. Expected User Experience:** Pengunjung yang datang di luar jadwal PO memahami kapan mereka dapat kembali bertransaksi.
- **7. Affected Components:** [resources/views/product/show.blade.php](file:///e:/Codex/resources/views/product/show.blade.php).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Saat PO ditutup, tombol submit nonaktif dan menampilkan teks penjelasan jadwal berikutnya.

---

### HALAMAN 4: Informasi Pre-Order (`/informasi-po`)
- **1. Current State:** Halaman editorial informatif yang menjelaskan jadwal siklus PO saat ini (tanggal buka, tutup, dan estimasi barang siap kirim), ketentuan pembayaran BCA Merchant 24 jam, dan panduan pengiriman ekspedisi. Bukti: `docs/phase-2/evidence/public/public-po-info-desktop.png`.
- **2. Strengths:** Narasi alur pre-order sangat runtut dan menenangkan calon pembeli yang baru pertama kali menggunakan jasa titip; kartu jadwal PO selaras dengan status database.
- **3. Problems:** Bagian FAQ di bawah halaman masih berupa daftar teks panjang statis tanpa accordion, sehingga pada layar smartphone membutuhkan scroll cukup panjang.
- **4. Root Cause:** Blade template merender pertanyaan dan jawaban dalam div berulang tanpa toggle collapse.
- **5. Proposed Improvement:** Poles (*POLISH*) segmen FAQ menggunakan komponen accordion minimalis khas Editorial Stone.
- **6. Expected User Experience:** Pengguna mobile dapat memindai topik pertanyaan umum dengan cepat dan hanya membuka poin yang diminati.
- **7. Affected Components:** [resources/views/po-info/index.blade.php](file:///e:/Codex/resources/views/po-info/index.blade.php).
- **8. Priority:** **P3 (Low)**
- **9. Acceptance Criteria:** FAQ pada layar mobile tampil ringkas dengan mekanisme expand/collapse yang mulus.

---

## 3. KELOMPOK 2: AUTHENTICATION (OTENTIKASI)

---

### HALAMAN 5 s/d 8: Login, Register, Forgot Password, Reset Password
- **1. Current State:** Seluruh halaman otentikasi menggunakan tata letak kontainer terfokus (`--container-focused: 880px`) berlatar canvas hangat `#e5e3dc` dengan kartu putih ivory `#faf9f6` di tengah. Terdapat logo Uyuy Jastip, judul jelas, field input berpadding nyaman, dan tautan silang antar halaman. Bukti: `evidence/auth/auth-login-desktop.png` s/d `auth-register-mobile.png`.
- **2. Strengths:** Formulir sangat bersih, tidak berantakan; kontras teks label sangat jelas; validasi input dari backend ditandai dengan teks merah dan border halus; tombol submit dilindungi double-submit prevention.
- **3. Problems:** Field input kata sandi belum memiliki tombol ikon mata (*password visibility toggle*) untuk melihat/menyembunyikan kata sandi yang diketik.
- **4. Root Cause:** Komponen input password berupa `<input type="password">` polos tanpa pembungkus interaktif tombol toggle.
- **5. Proposed Improvement:** Tambahkan fitur penampil kata sandi opsional menggunakan skrip Vanilla JS sederhana pada field password di halaman Login dan Register.
- **6. Expected User Experience:** Mengurangi kesalahan pengetikan kata sandi oleh pengguna di smartphone tanpa mengorbankan keamanan.
- **7. Affected Components:** [resources/views/auth/login.blade.php](file:///e:/Codex/resources/views/auth/login.blade.php), [resources/views/auth/register.blade.php](file:///e:/Codex/resources/views/auth/register.blade.php).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Pengguna dapat mengklik ikon mata untuk menampilkan atau menyembunyikan karakter kata sandi.

---

## 4. KELOMPOK 3: CUSTOMER PORTAL (PORTAL PELANGGAN)

---

### HALAMAN 9: Dashboard Pelanggan (`/pelanggan/dashboard`)
- **1. Current State:** Halaman menyambut pengguna dengan sapaan hangat ("Halo, Raka Rahmatullah"), bilah tab portal horizontal (*Ringkasan, Pesanan saya, Keranjang, Katalog*), banner pengingat PO aktif, 4 kartu ringkasan status pesanan, dan tabel pesanan terbaru. Bukti: `evidence/customer/customer-dashboard-desktop.png`.
- **2. Strengths:** Bilah tab navigasi sangat intuitif; kartu pesanan menunggu pembayaran memiliki aksen border amber yang langsung memandu pengguna untuk menyelesaikan transaksi; tabel pesanan terbaru menyajikan tombol detail yang jelas.
- **3. Problems:** Pada smartphone (`customer-dashboard-mobile.png`), baris tab navigasi portal agak sempit jika layar terlalu kecil.
- **4. Root Cause:** Komponen `.customer-portal-nav` menggunakan flex-wrap yang dapat melompat baris jika teks label panjang.
- **5. Proposed Improvement:** Tambahkan properti scroll horizontal halus (`overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch;`) pada navigasi portal mobile agar pengguna dapat menggeser tab dengan nyaman tanpa tumpukan baris yang merusak header.
- **6. Expected User Experience:** Pengguna mobile dapat beralih menu akun dengan gesture geser jari (*horizontal swipe*) yang ergonomis.
- **7. Affected Components:** [resources/views/layouts/customer.blade.php](file:///e:/Codex/resources/views/layouts/customer.blade.php), [resources/css/uyuy-theme.css](file:///e:/Codex/resources/css/uyuy-theme.css).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Tab menu portal pelanggan di layar smartphone dapat di-swipe mulus tanpa pembungkusan vertikal ganda.

---

### HALAMAN 10: Keranjang Belanja (`/pelanggan/keranjang`)
- **1. Current State:** Menampilkan daftar item produk yang dipilih (gambar, nama, ukuran ml, harga satuan, input kuantitas dengan tombol perbarui dan hapus), ringkasan biaya di sisi kanan, serta tombol checkout. Bukti: `evidence/customer/customer-cart-desktop.png`.
- **2. Strengths:** Informasi subtotal dihitung transparan; penghapusan item dilindungi konfirmasi; tombol checkout langsung mengarahkan ke form finalisasi pengiriman.
- **3. Problems:** Perubahan kuantitas produk saat ini mengharuskan pengguna mengklik tombol ikon simpan kecil di samping input angka.
- **4. Root Cause:** Form kuantitas menggunakan submit manual per baris produk.
- **5. Proposed Improvement:** Pertahankan mekanisme saat ini demi keandalan non-JS, namun poles (*POLISH*) visual tombol simpan perbarui agar memiliki tooltip atau teks penjelasan "Simpan perubahan" yang lebih eksplisit saat angka diubah.
- **6. Expected User Experience:** Pelanggan tidak bingung mengapa total belanja belum berubah sebelum menekan tombol simpan kuantitas.
- **7. Affected Components:** [resources/views/customer/cart/index.blade.php](file:///e:/Codex/resources/views/customer/cart/index.blade.php).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Saat kuantitas diubah, indikator simpan tersorot jelas.

---

### HALAMAN 11 & 12: Riwayat & Detail Pesanan (`/pelanggan/pesanan` & `/pelanggan/pesanan/{id}`)
- **1. Current State:** Daftar riwayat menyajikan tabel transaksi berpaginasi dengan filter status. Halaman detail menyajikan nomor pesanan besar berformat ULID, linimasa progres (*Perjalanan pesanan*), daftar item parfum, rincian pembayaran BCA, dan riwayat mutasi status. Bukti: `evidence/customer/customer-orders-desktop.png` dan `customer-order-detail-desktop.png`.
- **2. Strengths:** Komponen visual linimasa tahapan pesanan (*stepper timeline*) sangat jernih dan memberi kepastian posisi barang pelanggan; nomor resi dan kurir tertampil mencolok begitu pesanan berstatus *Barang Dikirim*.
- **3. Problems:** Di layar smartphone 360px, nomor pesanan panjang seperti `UJ-PO10-20261029-01M724...` dapat memakan banyak ruang vertikal judul.
- **4. Root Cause:** Ukuran heading H1 nomor pesanan belum memiliki aturan clamp yang cukup mengecil di layar di bawah 380px.
- **5. Proposed Improvement:** Terapkan styling `word-break: break-all; font-size: clamp(1.25rem, 5vw, 1.75rem);` khusus pada judul nomor pesanan di layar mobile.
- **6. Expected User Experience:** Judul nomor transaksi tampil proporsional tanpa mendorong konten linimasa terlalu ke bawah.
- **7. Affected Components:** [resources/views/customer/orders/show.blade.php](file:///e:/Codex/resources/views/customer/orders/show.blade.php).
- **8. Priority:** **P3 (Low)**
- **9. Acceptance Criteria:** Nomor pesanan panjang terbaca utuh dan rapi di layar 360px.

---

### HALAMAN 13: Unggah Bukti Pembayaran (`/pelanggan/pesanan/{id}/pembayaran`)
- **1. Current State:** Pada pengujian pesanan seeder dengan deadline kedaluwarsa, sistem secara tepat mengalihkan ke detail pesanan dengan alert peringatan. Pada template Blade sumber, halaman ini memuat nomor rekening BCA Merchant, tombol "Salin Nomor Rekening", field input nominal transfer, input unggah berkas (JPG, PNG, PDF maks 2MB), dan peringatan batas waktu 24 jam.
- **2. Strengths:** Desain instruksi rekening BCA sangat jelas dan dilengkapi nomor referensi pesanan untuk berita transfer; validasi berkas mencegah format yang tidak didukung.
- **3. Problems:** Tombol "Salin Nomor Rekening" belum memiliki umpan balik visual (*visual toast/tooltip feedback*) seperti "Tersalin!" saat diklik pengguna.
- **4. Root Cause:** Skrip clipboard hanya memanggil `navigator.clipboard.writeText()` tanpa mengubah label tombol sementara.
- **5. Proposed Improvement:** Tambahkan feedback visual mikro pada tombol salin: ubah teks tombol menjadi "Tersalin!" selama 2 detik sebelum kembali ke normal.
- **6. Expected User Experience:** Pelanggan mendapatkan kepastian bahwa nomor rekening BCA telah tersalin ke clipboard ponsel sebelum membuka aplikasi m-Banking.
- **7. Affected Components:** [resources/views/customer/orders/payment.blade.php](file:///e:/Codex/resources/views/customer/orders/payment.blade.php), [resources/js/app.js](file:///e:/Codex/resources/js/app.js).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Mengklik tombol salin memicu teks konfirmasi "Tersalin!" secara instan.

---

### HALAMAN 14: Permohonan Pembatalan (`/pelanggan/pesanan/{id}/batal`)
- **1. Current State:** Menyajikan ringkasan pesanan yang hendak dibatalkan, peringatan konsekuensi pembatalan, kotak input textarea alasan pembatalan (minimal 10 karakter), dan tombol kirim permohonan. Bukti: `evidence/customer/customer-cancellation-desktop.png`.
- **2. Strengths:** Penjelasan ketentuan pembatalan sangat transparan; sistem secara tegas menginfokan bahwa pesanan yang telah dibayar membutuhkan waktu verifikasi refund oleh admin.
- **3. Problems:** Status permohonan pembatalan yang sedang ditinjau hanya tampil sebagai teks kecil di halaman detail.
- **4. Proposed Improvement:** Tambahkan lencana lencana status mencolok pada halaman detail pesanan jika permohonan pembatalan sedang menunggu putusan admin (*In Review*).
- **5. Expected User Experience:** Pelanggan tidak bingung apakah pengajuan pembatalannya sudah masuk atau belum.
- **6. Affected Components:** [resources/views/customer/orders/show.blade.php](file:///e:/Codex/resources/views/customer/orders/show.blade.php).
- **7. Priority:** **P3 (Low)**
- **8. Acceptance Criteria:** Lencana status review pembatalan terlihat jelas pada kartu pesanan.

---

## 5. KELOMPOK 4: ADMIN WORKSPACE (RUANG KERJA ADMIN OPERASIONAL)

---

### HALAMAN 15: Dashboard Operasional Admin (`/admin/dashboard`)
- **1. Current State:** Sidebar gelap di sisi kiri, jam operasional di kanan atas, 4 kartu metrik antrean tugas utama, kotak tindakan mendesak untuk bukti pembayaran yang tertunda, dan tabel pesanan terbaru. Bukti: `evidence/admin/admin-dashboard-desktop.png`.
- **2. Strengths:** Sangat berorientasi tindakan (*action-oriented*); staf admin langsung mengetahui berapa bukti bayar dan pembatalan yang harus diproses tanpa perlu mencari-cari menu.
- **3. Problems:** Kotak informasi batasan wewenang admin di bagian bawah sidebar memakan ruang vertikal yang cukup besar di layar laptop resolusi 1366×768.
- **4. Root Cause:** Padding kotak catatan batasan wewenang di sidebar diatur statis 16px dengan teks panjang.
- **5. Proposed Improvement:** Poles (*POLISH*) kotak batasan wewenang menjadi tooltip bantuan atau accordion ringkas di bagian bawah profil staf, sehingga tombol "Keluar" selalu terlihat di layar laptop 768px tanpa harus scroll sidebar.
- **6. Expected User Experience:** Sidebar admin terasa lebih ramping dan ergonomis pada layar laptop staf operasional.
- **7. Affected Components:** [resources/views/layouts/admin.blade.php](file:///e:/Codex/resources/views/layouts/admin.blade.php), [resources/css/uyuy-theme.css](file:///e:/Codex/resources/css/uyuy-theme.css).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Seluruh menu sidebar dan tombol keluar tampil utuh pada viewport 1366×768 px tanpa scrollbar vertikal pada sidebar.

---

### HALAMAN 16 & 17: Daftar & Detail Pesanan Admin (`/admin/pesanan` & `/admin/pesanan/{id}`)
- **1. Current State:** Tabel pesanan lengkap dengan pencarian nomor pesanan/nama, filter status pesanan, filter periode PO, dan paginasi. Halaman detail menyajikan data penerima, alamat pengiriman, kontak telepon (dengan tautan cepat WhatsApp `wa.me`), form pembaruan status bertahap, dan input kurir/nomor resi. Bukti: `evidence/admin/admin-orders-desktop.png` dan `admin-order-detail-desktop.png`.
- **2. Strengths:** Alur transisi status sangat aman (hanya menampilkan status valid berikutnya via `nextOperationalStatuses()`); tautan WhatsApp memudahkan staf menghubungi pembeli jika alamat kurang lengkap.
- **3. Problems:** Input nomor resi dan kurir baru muncul setelah status diganti ke "Barang Dikirim", belum ada validasi format nomor resi kurir umum (JNE/J&T/SiCepat).
- **4. Proposed Improvement:** Pertahankan alur saat ini (KEEP). Tambahkan dropdown pilihan nama ekspedisi populer (JNE, J&T, SiCepat, Paxel, GoSend/Grab) di samping input teks bebas untuk mempercepat input admin.
- **5. Expected User Experience:** Admin dapat memilih kurir dalam 1 klik daripada mengetik manual setiap kali pengiriman.
- **6. Affected Components:** [resources/views/admin/orders/show.blade.php](file:///e:/Codex/resources/views/admin/orders/show.blade.php).
- **8. Priority:** **P3 (Low)**
- **9. Acceptance Criteria:** Dropdown kurir memuat opsi ekspedisi standar dan opsi kurir lainnya.

---

### HALAMAN 18 & 19: Antrean & Verifikasi Pembayaran (`/admin/pembayaran` & `/admin/pembayaran/{id}/verifikasi`)
- **1. Current State:** Antrean menyajikan daftar bukti bayar yang menunggu pemeriksaan. Halaman verifikasi menyajikan perbandingan nominal tagihan vs nominal yang dilaporkan pelanggan, nama rekening pengirim, lampiran gambar bukti transfer yang dapat diklik untuk zoom/buka modal, tombol "Validasi Pembayaran" dan tombol "Tolak Pembayaran" (membuka modal input catatan alasan penolakan). Bukti: `evidence/admin/admin-payment-verification-desktop.png`.
- **2. Strengths:** Mencegah kesalahan fatal staf karena nominal yang tidak cocok akan memunculkan peringatan merah tebal; modal penolakan mewajibkan input alasan agar pelanggan tahu mengapa pembayarannya ditolak.
- **3. Problems:** Tampilan gambar bukti transfer saat ini membuka di tab baru jika pengguna ingin melihat resolusi penuh, bukan modal lightbox zoom in-page.
- **4. Proposed Improvement:** Tambahkan modal pratinjau gambar in-page (*in-app image preview modal*) sederhana sehingga admin tidak perlu berpindah tab browser saat memeriksa mutasi bukti transfer.
- **5. Expected User Experience:** Staf operasional memverifikasi bukti transfer lebih cepat dan tetap fokus di halaman kerja yang sama.
- **6. Affected Components:** [resources/views/admin/payments/verify.blade.php](file:///e:/Codex/resources/views/admin/payments/verify.blade.php).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Mengklik bukti bayar memunculkan pop-up zoom gambar di dalam halaman verifikasi.

---

### HALAMAN 20 & 21: Antrean & Tinjau Pembatalan (`/admin/pembatalan` & `/admin/pembatalan/{id}`)
- **1. Current State:** Menampilkan daftar permohonan pembatalan pesanan. Halaman putusan menyajikan detail pesanan, alasan pembatalan dari pelanggan, form persetujuan yang secara otomatis mewajibkan input bukti transfer refund dan nomor referensi jika pesanan telah lunas. Bukti: `evidence/admin/admin-cancellation-detail-desktop.png`.
- **2. Strengths:** Logika bisnis integritas finansial sangat kuat (mencegah persetujuan pembatalan pesanan lunas tanpa bukti refund riil).
- **3. Problems:** Penjelasan instruksi refund bagi admin yang baru bertugas masih agak padat teksnya.
- **4. Proposed Improvement:** Tambahkan checklist panduan singkat di samping form refund: "1. Buka m-Banking BCA -> 2. Transfer ke rekening pelanggan -> 3. Tangkap layar bukti -> 4. Lampirkan berkas di form ini".
- **5. Expected User Experience:** Menghilangkan kebingungan bagi admin baru dalam memproses pembatalan ber-refund.
- **6. Affected Components:** [resources/views/admin/cancellations/show.blade.php](file:///e:/Codex/resources/views/admin/cancellations/show.blade.php).
- **8. Priority:** **P3 (Low)**
- **9. Acceptance Criteria:** Checklist operasional refund terpampang jelas pada panel admin.

---

## 6. KELOMPOK 5: OWNER WORKSPACE (RUANG KERJA OWNER EKSEKUTIF)

---

### HALAMAN 22: Dashboard Eksekutif Owner (`/owner/dashboard`)
- **1. Current State:** Header eksklusif lencana Owner, sidebar manajerial, kartu kontrol periode PO aktif (dengan tombol buka/tutup periode cepat), 4 kartu KPI finansial (Total pesanan, Pembayaran valid Rp, Pelanggan terdaftar, Produk aktif), dan tabel distribusi status pesanan. Bukti: `evidence/owner/owner-dashboard-desktop.png`.
- **2. Strengths:** Sangat berkelas dan menyajikan data makro bisnis yang dibutuhkan pemilik usaha dalam hitungan detik; tombol kontrol buka/tutup PO dilengkapi konfirmasi modal pencegah klik tidak sengaja.
- **3. Problems:** Belum ada visualisasi grafik tren penjualan antar-periode PO (saat ini hanya berupa angka teks metrik).
- **4. Proposed Improvement:** Pertahankan implementasi saat ini (KEEP). Desain sudah sangat proporsional; grafik tren dapat dijadwalkan pada roadmap pasca-capstone tanpa membebani performa.
- **5. Affected Components:** [resources/views/owner/dashboard.blade.php](file:///e:/Codex/resources/views/owner/dashboard.blade.php).
- **8. Priority:** **P3 (Low)**
- **9. Acceptance Criteria:** Dashboard merender seluruh 4 kartu metrik dengan angka presisi.

---

### HALAMAN 23 s/d 25: Manajemen Periode PO (`/owner/periode-po` dkk)
- **1. Current State:** Daftar periode PO dengan penanda status (Dibuka/Ditutup), tanggal mulai, tanggal selesai, estimasi pengiriman, jumlah pesanan tercatat, dan tombol tindakan (Edit, Buka/Tutup Status). Halaman tambah dan edit menyajikan form datepicker yang bersih. Bukti: `evidence/owner/owner-po-periods-desktop.png` dan `owner-po-create-desktop.png`.
- **2. Strengths:** Validasi backend mencegah tanggal selesai mendahului tanggal mulai; penutupan periode PO menerapkan shared lock aman.
- **3. Problems:** Format tanggal pada form input masih mengandalkan input HTML native `type="date"` yang pada beberapa browser lama memiliki gaya tampilan default berbeda.
- **4. Proposed Improvement:** Poles (*POLISH*) gaya input tanggal agar menyatu seragam dengan styling kontrol Editorial Stone.
- **5. Affected Components:** [resources/views/owner/po-periods/form.blade.php](file:///e:/Codex/resources/views/owner/po-periods/form.blade.php).
- **8. Priority:** **P3 (Low)**
- **9. Acceptance Criteria:** Input tanggal tampil konsisten di Chrome, Edge, dan Safari.

---

### HALAMAN 26 s/d 28: Manajemen Master Produk & Varian (`/owner/produk` dkk)
- **1. Current State:** Daftar produk menyajikan tabel katalog dengan thumbnail foto, nama varian, kategori aroma, harga per ukuran (35ml, 60ml, 100ml), status aktif, dan tombol edit. Halaman tambah produk berjalan normal. **Halaman ubah produk (`/owner/produk/{id}/edit`) mengalami error 500.**
- **2. Strengths:** Halaman daftar produk sangat komprehensif; form tambah produk memiliki pemetaan varian harga dan modal per ukuran botol yang sangat terstruktur.
- **3. Problems (CRITICAL DEFECT):** Halaman `/owner/produk/1/edit` mengalami crash total (*HTTP 500 ParseError: unexpected token "endif"*).
- **4. Root Cause:** Penulisan direktif Blade `@if(...) ... @endif` secara inline pada baris atribut input `resources/views/owner/products/edit.blade.php:68-71` merusak kompilasi parser Blade Laravel.
- **5. Proposed Improvement:** **REFACTOR (P0 CRITICAL BUG FIX)**. Rapikan sintaksis Blade pada `resources/views/owner/products/edit.blade.php` dengan memisahkan kondisi atribut error ke dalam sintaksis Blade yang bersih atau menggunakan class ternary helper sederhana, memastikan seluruh tag `@if` memiliki pasangan `@endif` yang valid.
- **6. Expected User Experience:** Owner dapat membuka halaman edit produk parfum, memperbarui harga jual, mengisi harga modal, dan mengganti foto produk tanpa hambatan error 500.
- **7. Affected Components:** [resources/views/owner/products/edit.blade.php](file:///e:/Codex/resources/views/owner/products/edit.blade.php).
- **8. Priority:** **P0 (Critical)**
- **9. Acceptance Criteria:** URL `/owner/produk/{id}/edit` berhasil dibuka dengan status HTTP 200, form terisi data produk eksisting, dan tombol perbarui produk berfungsi normal.

---

### HALAMAN 29: Rekapitulasi Bisnis & Belanja PO (`/owner/rekapitulasi`)
- **1. Current State:** Menyajikan filter periode PO, 5 kartu metrik konsolidasi (Unit terverifikasi, Modal tercatat, Pembayaran valid, Pelanggan terverifikasi, Menunggu pembayaran), dan tabel rincian varian parfum yang harus dibelanjakan ke vendor. Bukti: `evidence/owner/owner-recap-desktop.png`.
- **2. Strengths:** Mengagregasikan kebutuhan botol parfum secara otomatis (misal: "3 botol 60ml Acqua Di Gio"), mempermudah Owner saat belanja grosir tanpa rekap manual; tombol "Cetak Rekap" siap pakai.
- **3. Problems:** Nilai "Modal tercatat" menampilkan `Rp 0` karena data seeder historis belum mengisi kolom `cost_price` pada seluruh varian produk, mengakibatkan metrik margin laba belum terhitung maksimal.
- **4. Proposed Improvement:** Tambahkan keterangan kecil di bawah kartu modal: "Data modal dihitung dari produk yang telah diinput harga modalnya oleh Owner pada menu Master Produk."
- **5. Expected User Experience:** Owner memahami mengapa angka modal nol pada produk-produk lama yang belum diinput harga modalnya.
- **6. Affected Components:** [resources/views/owner/recap/index.blade.php](file:///e:/Codex/resources/views/owner/recap/index.blade.php).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Penjelasan sumber data modal tampil transparan pada kartu rekapitulasi.

---

### HALAMAN 30: Direktori Pelanggan (`/owner/pelanggan`)
- **1. Current State:** Menampilkan daftar seluruh akun pelanggan yang tersimpan di sistem, kontak nomor telepon WhatsApp, alamat pengiriman default, dan jumlah pesanan yang pernah dibuat. Bukti: `evidence/owner/owner-customers-desktop.png`.
- **2. Strengths:** Memberikan visibilitas basis pelanggan bagi Owner untuk program loyalitas atau pengumuman PO via broadcast WhatsApp; memproteksi kata sandi dan informasi sensitif.
- **3. Problems:** Belum ada kolom pencarian cepat nama atau nomor telepon pelanggan pada halaman direktori.
- **4. Proposed Improvement:** Tambahkan field input pencarian nama/telepon pelanggan pada bilah atas tabel pelanggan.
- **5. Expected User Experience:** Owner dapat mencari data riwayat pelanggan tertentu dalam hitungan detik.
- **6. Affected Components:** [resources/views/owner/customers/index.blade.php](file:///e:/Codex/resources/views/owner/customers/index.blade.php).
- **8. Priority:** **P2 (Medium)**
- **9. Acceptance Criteria:** Input pencarian memfilter baris pelanggan secara akurat.

---

## 7. KELOMPOK 6: ERROR PAGES (HALAMAN PENANGANAN KESALAHAN)

---

### HALAMAN 31 & 32: Halaman 403 Forbidden & 404 Not Found
- **1. Current State:** Dirender dengan template kustom berpalet Editorial Stone (`#e5e3dc` Canvas dengan kartu `#faf9f6`), ikon perisai untuk 403, ikon kompas untuk 404, pesan penjelasan bahasa Indonesia yang ramah, dan tombol aksi "Kembali ke Beranda" atau "Ke Halaman Login". Bukti: `evidence/auth/error-403-desktop.png` dan `error-404-desktop.png`.
- **2. Strengths:** Tidak menampilkan pesan teknis mentah (*no stack trace leak*); menjaga citra profesional merek bahkan saat terjadi error navigasi pengguna.
- **3. Rekomendasi:** **KEEP (100% MEMENUHI STANDAR)**. Pertahankan desain halaman error saat ini.

---

## 8. REKAPITULASI KLASIFIKASI KEPUTUSAN HALAMAN

| Kategori Halaman | Total Rute | KEEP | POLISH | REFACTOR | REDESIGN |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Public Storefront** | 4 | 2 | 2 | 0 | 0 |
| **Authentication** | 4 | 4 | 0 | 0 | 0 |
| **Customer Portal** | 6 | 3 | 3 | 0 | 0 |
| **Admin Workspace** | 7 | 5 | 2 | 0 | 0 |
| **Owner Workspace** | 9 | 2 | 5 | 2 | 0 |
| **Error Handling** | 2 | 2 | 0 | 0 | 0 |
| **TOTAL** | **32** | **18 (56%)** | **12 (38%)** | **2 (6%)** | **0 (0%)** |

> **KESIMPULAN AUDIT:**  
> Tidak ada satupun halaman yang membutuhkan **REDESIGN TOTAL**. Seluruh antarmuka saat ini telah memiliki fondasi visual dan struktur komponen yang kuat. Fokus pengembangan Phase 3 adalah penyelesaian **1 bug kritis Refactor P0 (Edit Produk Owner)** dan penerapan **polishing usability terarah pada 12 halaman**.
