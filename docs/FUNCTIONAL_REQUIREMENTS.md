# Functional Requirements
## Sistem Informasi Pre-Order Parfum Uyuy Jastip

**Versi:** 1.2
**Tanggal:** 8 November 2026
**Sumber:** `PROJECT_PLAN.md`, `DATABASE_DESIGN.md`, `IMPLEMENTATION_PROGRESS.md`, `OPEN_QUESTIONS.md`, dan pemeriksaan alur aplikasi.

Dokumen ini merinci kebutuhan fungsional sistem. Status implementasi menjelaskan kondisi kode yang diperiksa, bukan pernyataan bahwa setiap alur sudah lulus UAT atau pengujian produksi.

## Tiga Aktor

- **Customer:** termasuk calon Customer yang belum login dan pengguna terdaftar; dapat melihat halaman publik/katalog, mendaftar, mengelola keranjang, membuat pesanan, mengunggah bukti pembayaran, melihat pesanan miliknya, dan mengajukan pembatalan.
- **Admin:** menangani pesanan, verifikasi pembayaran, dan permohonan pembatalan.
- **Owner:** memiliki kewenangan Admin serta mengelola periode PO, katalog produk, dan rekapitulasi.

Dokumen ini menggunakan **tepat tiga aktor**: Customer, Admin, dan Owner. Validasi, penyimpanan transaksi, pembatasan akses, dan pencatatan riwayat merupakan perilaku sistem di dalam kebutuhan, bukan aktor tambahan.

## Daftar Kebutuhan Fungsional

### A. Akun dan Hak Akses

#### FR-01 — Registrasi akun Customer

- **Aktor:** Customer (calon Customer).
- **Kebutuhan:** Sistem harus menyediakan registrasi akun Customer dan membuat profil Customer yang terhubung dengan akun tersebut.
- **Kriteria penerimaan:**
  - Nama, email, nomor telepon/WhatsApp, kata sandi, dan konfirmasi kata sandi wajib divalidasi sebelum disimpan.
  - Kata sandi minimal 8 karakter; kota dan alamat bersifat opsional.
  - Email yang sudah digunakan ditolak dengan pesan yang dapat dipahami.
  - Akun baru memperoleh role Customer dan profilnya tersimpan secara konsisten.
- **Status implementasi:** Terimplementasi.

#### FR-02 — Login dan logout

- **Aktor:** Customer, Admin, Owner.
- **Kebutuhan:** Sistem harus mengautentikasi pengguna dan mengakhiri sesi saat logout.
- **Kriteria penerimaan:**
  - Pengguna dapat masuk menggunakan kredensial yang valid.
  - Kredensial tidak valid menghasilkan pesan gagal tanpa membuka halaman terlindungi.
  - Logout mengakhiri sesi terautentikasi pengguna.
- **Status implementasi:** Terimplementasi.

#### FR-03 — Pembatasan akses berdasarkan role

- **Aktor:** Customer, Admin, Owner.
- **Kebutuhan:** Sistem harus membatasi halaman dan aksi sesuai role pengguna.
- **Kriteria penerimaan:**
  - Customer hanya dapat melakukan aksi Customer.
  - Admin dapat mengakses fungsi operasional, tetapi tidak dapat mengelola periode PO, produk, atau rekap Owner.
  - Owner dapat mengakses fungsi operasional dan fungsi khusus Owner.
  - Pengguna yang tidak memiliki role yang sesuai ditolak dari route terlindungi meskipun membuka URL secara langsung; halaman publik tetap dapat diakses tanpa login.
- **Status implementasi:** Terimplementasi pada middleware/route; pengujian lintas-role menyeluruh masih diperlukan.

### B. Periode PO dan Katalog

#### FR-04 — Pengelolaan periode PO

- **Aktor:** Owner.
- **Kebutuhan:** Owner harus dapat membuat periode PO dan mengatur statusnya menjadi `Dibuka` atau `Ditutup`.
- **Kriteria penerimaan:**
  - Owner dapat mengisi nama periode, tanggal mulai, tanggal akhir, dan catatan opsional.
  - Sistem menolak rentang tanggal yang tidak valid.
  - Pada satu waktu, hanya satu periode yang berstatus administrasi `Dibuka`; ketika periode lain dibuka, periode terbuka sebelumnya ditutup.
  - Hanya Owner yang dapat membuat periode atau mengubah status periode.
- **Status implementasi:** Terimplementasi.

#### FR-05 — Pengelolaan produk parfum

- **Aktor:** Owner.
- **Kebutuhan:** Owner harus dapat membuat, melihat, mengubah, dan mengaktifkan/menonaktifkan produk parfum.
- **Kriteria penerimaan:**
  - Data produk mencakup nama, merek, kategori/aroma, ukuran, harga, deskripsi, dan gambar bila tersedia.
  - Harga, ukuran, dan file gambar divalidasi di server.
  - Produk nonaktif tidak dapat ditambahkan ke keranjang atau dipesan.
- **Status implementasi:** Terimplementasi.

#### FR-06 — Katalog, detail produk, dan informasi PO

- **Aktor:** Customer, Admin, Owner.
- **Kebutuhan:** Sistem harus menampilkan produk aktif, informasi produk, dan status periode PO.
- **Kriteria penerimaan:**
  - Pengguna dapat melihat daftar produk aktif dan membuka detail produk.
  - Pencarian mencocokkan nama, deskripsi, dan kategori; filter terpisah tersedia untuk kategori dan ukuran 35, 60, atau 100 ml.
  - Hasil katalog ditampilkan dengan pagination.
  - Halaman publik menampilkan apakah pemesanan tersedia berdasarkan status dan tanggal PO.
- **Status implementasi:** Terimplementasi.

### C. Keranjang, Checkout, dan Pembayaran

#### FR-07 — Pembatasan checkout berdasarkan periode PO

- **Aktor:** Customer.
- **Kebutuhan:** Sistem hanya boleh menerima checkout ketika status PO `Dibuka` dan tanggal saat ini berada di dalam rentang PO.
- **Kriteria penerimaan:**
  - Pemeriksaan menggunakan zona waktu `Asia/Jakarta`.
  - Tanggal mulai dan tanggal akhir berlaku inklusif; tanggal akhir berlaku sampai pukul 23.59 WIB.
  - Sistem memeriksa ulang status dan rentang tanggal pada saat checkout diproses.
  - Checkout di luar aturan ditolak dan keranjang tetap dapat ditinjau Customer.
- **Status implementasi:** Terimplementasi.

#### FR-08 — Pembuatan pesanan terkait Customer dan PO

- **Aktor:** Customer.
- **Kebutuhan:** Sistem harus membuat pesanan yang terhubung dengan Customer dan periode PO yang berlaku.
- **Kriteria penerimaan:**
  - Pesanan menyimpan data penerima, alamat, catatan opsional, total, nomor pesanan, dan periode PO.
  - Pesanan baru berstatus `Menunggu Pembayaran` dan memiliki data Payment berstatus menunggu verifikasi.
  - Pesanan, detail item, data Payment, dan riwayat status awal tersimpan sebagai satu transaksi yang konsisten.
- **Status implementasi:** Terimplementasi.

#### FR-09 — Pesanan multi-item dan perhitungan harga

- **Aktor:** Customer.
- **Kebutuhan:** Customer harus dapat memesan satu atau lebih produk dalam satu pesanan.
- **Kriteria penerimaan:**
  - Customer dapat menambahkan produk/ukuran ke keranjang dan menghapus item dari keranjang sebelum checkout.
  - Setiap item menyimpan produk, ukuran, kuantitas, harga satuan, dan subtotal.
  - Ukuran yang didukung saat ini adalah 35, 60, dan 100 ml; kuantitas setiap varian harus 1–50 botol.
  - Harga satuan dihitung server dari harga dasar produk dan tier ukuran; nilai yang dikirim browser tidak dipercaya.
  - Total pesanan dihitung ulang dari data produk saat checkout. Rumus harga tier yang sedang dipakai harus disahkan Owner sebelum dijadikan kebijakan harga produksi.
  - Pengiriman ulang token checkout yang sama mengembalikan pesanan yang telah tercatat, bukan membuat pesanan dan Payment duplikat.
- **Status implementasi:** Terimplementasi; request konkuren pada MySQL masih perlu diuji.

#### FR-10 — Informasi pembayaran dan unggah bukti

- **Aktor:** Customer.
- **Kebutuhan:** Sistem harus menampilkan instruksi pembayaran BCA Merchant dan menyediakan unggah bukti transfer untuk pesanan Customer.
- **Kriteria penerimaan:**
  - Customer hanya dapat mengunggah bukti untuk pesanan miliknya yang masih menunggu pembayaran.
  - Sistem memvalidasi tipe dan ukuran file serta menyimpan file pada storage privat.
  - Customer mengirim nominal transfer; backend menerima unggahan hanya jika nominal sama dengan total pesanan yang dihitung server.
  - Sistem menampilkan pesan hasil unggah dan status bukti kepada Customer.
- **Status implementasi:** Terimplementasi untuk unggahan baru; beberapa path bukti legacy yang eksternal perlu ditinjau operasional.

#### FR-11 — Pemeriksaan bukti pembayaran

- **Aktor:** Admin, Owner.
- **Kebutuhan:** Admin/Owner harus dapat memeriksa bukti pembayaran pesanan yang dapat diverifikasi.
- **Kriteria penerimaan:**
  - Antrean pemeriksaan menampilkan pembayaran menunggu verifikasi yang terkait pesanan menunggu pembayaran.
  - Berkas bukti hanya dapat dibuka oleh pengguna yang berwenang.
  - Riwayat unggahan sebelumnya dapat dilihat dari halaman pemeriksaan.
  - Path eksternal atau file yang tidak tersedia tidak diperlakukan sebagai file privat yang aman.
- **Status implementasi:** Terimplementasi; path bukti legacy eksternal mungkin memerlukan unggah ulang atau arsip.

#### FR-12 — Pencatatan keputusan verifikasi pembayaran

- **Aktor:** Admin, Owner.
- **Kebutuhan:** Admin/Owner harus dapat menetapkan hasil verifikasi sebagai `Valid` atau `Ditolak` dan mencatat keputusan.
- **Kriteria penerimaan:**
  - Keputusan hanya dapat diproses untuk bukti menunggu verifikasi pada pesanan yang masih menunggu pembayaran.
  - Catatan alasan wajib diisi ketika bukti ditolak.
  - Jika bukti valid, status pesanan berubah menjadi `Pembayaran Diverifikasi`; jika ditolak, pesanan tetap `Menunggu Pembayaran` agar Customer dapat mengunggah bukti baru.
  - Keputusan, petugas, waktu, status Payment, status pesanan, dan riwayat perubahan tersimpan konsisten.
- **Status implementasi:** Terimplementasi.

### D. Status Pesanan dan Pembatalan

#### FR-13 — Perubahan status pesanan dan timeline

- **Aktor:** Customer, Admin, Owner.
- **Kebutuhan:** Sistem harus mencatat perkembangan pesanan dan menampilkan timeline status.
- **Kriteria penerimaan:**
  - Tahap operasional mengikuti urutan `Pembayaran Diverifikasi` → `Diproses` → `Barang Dikirim` → `Selesai`.
  - Aksi status hanya tersedia untuk Admin/Owner sesuai aturan transisi.
  - Setiap perubahan menyimpan status sebelumnya, status baru, waktu, petugas, dan catatan bila ada.
  - Customer dapat melihat timeline untuk pesanan miliknya.
- **Status implementasi:** Terimplementasi.

#### FR-14 — Pengajuan pembatalan oleh Customer

- **Aktor:** Customer.
- **Kebutuhan:** Customer harus dapat mengajukan pembatalan pesanan yang masih memenuhi syarat.
- **Kriteria penerimaan:**
  - Customer hanya dapat mengajukan untuk pesanan miliknya.
  - Pengajuan berisi alasan dan masuk ke status menunggu persetujuan.
  - Sistem mencegah pengajuan aktif ganda dan mengizinkan pengajuan ulang setelah penolakan.
  - Aksi dibatasi oleh status pesanan yang dapat dibatalkan.
- **Status implementasi:** Terimplementasi.

#### FR-15 — Keputusan permohonan pembatalan

- **Aktor:** Admin, Owner.
- **Kebutuhan:** Admin/Owner harus dapat menyetujui atau menolak permohonan pembatalan sesuai status pembayaran.
- **Kriteria penerimaan:**
  - Keputusan hanya dapat diambil satu kali untuk permohonan yang masih menunggu.
  - Persetujuan diblokir saat bukti pembayaran masih menunggu verifikasi.
  - Jika bukti ditolak tetapi tersimpan, persetujuan memerlukan catatan rekonsiliasi.
  - Persetujuan pembatalan pada pesanan dengan pembayaran valid ditahan sampai kebijakan refund tersedia.
  - Keputusan dan perubahan status pesanan dicatat dalam riwayat.
- **Status implementasi:** Terimplementasi sebagian; alur refund untuk pembayaran valid belum ditetapkan.

#### FR-16 — Riwayat pesanan Customer

- **Aktor:** Customer.
- **Kebutuhan:** Customer harus dapat melihat daftar, detail, status, Payment, dan riwayat pesanan miliknya.
- **Kriteria penerimaan:**
  - Daftar dan detail hanya berisi pesanan milik Customer yang sedang masuk.
  - Customer dapat melihat total, item, alamat, status pembayaran, timeline, dan status permohonan pembatalan.
  - Customer tidak dapat membuka pesanan Customer lain dengan mengganti ID URL.
- **Status implementasi:** Terimplementasi; pengujian IDOR menyeluruh masih diperlukan.

### E. Rekapitulasi dan Audit Trail

#### FR-17 — Rekapitulasi Owner

- **Aktor:** Owner.
- **Kebutuhan:** Owner harus dapat melihat ringkasan dan rincian pesanan berdasarkan periode PO.
- **Kriteria penerimaan:**
  - Ringkasan membedakan total pesanan, pesanan dengan payment valid, pesanan menunggu pembayaran, pesanan selesai, dan pesanan dibatalkan.
  - Nilai pembayaran terverifikasi dan kebutuhan pengadaan hanya berasal dari pesanan dengan payment berstatus `Valid`.
  - Jumlah pelanggan pada rekap adalah jumlah Customer unik yang memiliki pesanan dengan payment valid pada periode terpilih.
  - Rincian pengadaan mengelompokkan produk dan ukuran serta menampilkan kuantitas dan nilai subtotal.
- **Status implementasi:** Terimplementasi; rekonsiliasi dengan definisi akuntansi Owner masih perlu dilakukan.

#### FR-18 — Filter data berdasarkan periode PO

- **Aktor:** Owner.
- **Kebutuhan:** Owner harus dapat menyaring rekapitulasi berdasarkan periode PO.
- **Kriteria penerimaan:**
  - Owner dapat memilih periode PO yang tersedia.
  - Semua ringkasan dan rincian pada halaman mengikuti periode terpilih.
  - Periode yang tidak valid tidak boleh menghasilkan data di luar hak akses.
- **Status implementasi:** Terimplementasi.

#### FR-19 — Riwayat perubahan dan percobaan pembayaran

- **Aktor:** Customer, Admin, Owner.
- **Kebutuhan:** Sistem harus menyimpan dan menampilkan riwayat perubahan status pesanan, pembatalan, unggahan bukti, dan keputusan verifikasi.
- **Kriteria penerimaan:**
  - Setiap unggahan bukti baru memperoleh nomor percobaan dan waktu unggah.
  - Catatan/verifikasi percobaan lama tetap tersedia setelah Customer mengunggah bukti baru.
  - Riwayat status pesanan serta keputusan pembatalan menyimpan pelaku, waktu, dan catatan.
  - Riwayat bukti ditampilkan hanya kepada pengguna yang berwenang.
- **Status implementasi:** Terimplementasi.

### F. Isolasi Data dan Kewenangan

#### FR-20 — Isolasi data pesanan Customer

- **Aktor:** Customer.
- **Kebutuhan:** Sistem harus menjaga agar Customer hanya dapat membaca dan mengubah data pesanan miliknya.
- **Kriteria penerimaan:**
  - Pemeriksaan kepemilikan berlaku pada detail pesanan, pembayaran, unggah bukti, pembatalan, dan akses file bukti.
  - Permintaan ke ID pesanan atau attempt milik Customer lain ditolak.
  - Pemeriksaan dilakukan di backend, tidak hanya dengan menyembunyikan tautan di antarmuka.
- **Status implementasi:** Terimplementasi pada jalur utama; test IDOR lintas endpoint belum lengkap.

#### FR-21 — Pembatasan kewenangan Admin dan Owner

- **Aktor:** Admin, Owner.
- **Kebutuhan:** Sistem harus menerapkan pembagian hak akses operasional dan pengelolaan usaha.
- **Kriteria penerimaan:**
  - Admin dapat mengelola operasi order, verifikasi pembayaran, dan keputusan pembatalan.
  - Admin tidak dapat mengubah produk, membuka/menutup PO, atau mengakses rekap penuh Owner.
  - Owner memiliki akses ke pengelolaan PO, produk, pelanggan, dan rekapitulasi.
  - Backend menolak aksi yang tidak sesuai role meskipun dipanggil langsung melalui HTTP.
- **Status implementasi:** Terimplementasi melalui grup route dan middleware role; uji matriks role penuh masih diperlukan.

### G. Retensi Bukti Pembayaran

#### FR-22 — Pencatatan dan pengelolaan retensi bukti

- **Aktor:** Admin, Owner.
- **Kebutuhan:** Sistem harus mencatat waktu unggah bukti dan mendukung pengelolaan masa simpannya.
- **Kriteria penerimaan:**
  - Metadata waktu unggah tersimpan untuk setiap percobaan pembayaran.
  - File baru disimpan pada storage privat dan aksesnya dibatasi.
  - **Baseline proposal:** masa retensi maksimal satu bulan.
  - **Keputusan operasional sementara (7 November 2026):** bukti dipertahankan sampai Owner menetapkan aturan arsip/purge; jangan menghapus otomatis sebelum kebijakan itu disahkan.
- **Status implementasi:** Sebagian dan belum memenuhi baseline retensi proposal. Metadata waktu unggah dan storage privat tersedia, tetapi purge/arsip belum diimplementasikan. Keputusan mempertahankan file sementara berkonflik dengan batas maksimal satu bulan pada proposal dan perlu persetujuan formal perubahan kebutuhan.

### H. Fungsi Tambahan yang Teridentifikasi pada Implementasi

FR-23 sampai FR-25 berikut melengkapi daftar FR proposal setelah inventarisasi route dan controller. Ketiganya mendokumentasikan fungsi yang sudah ada di project.

#### FR-23 — Dashboard dan pencarian pesanan operasional

- **Aktor:** Admin, Owner.
- **Kebutuhan:** Admin/Owner harus dapat memantau antrean pekerjaan dan menemukan pesanan untuk ditangani.
- **Kriteria penerimaan:**
  - Dashboard menampilkan jumlah pembayaran menunggu verifikasi, permohonan pembatalan menunggu keputusan, pesanan perlu diproses, pesanan terkirim, serta beberapa pesanan terbaru.
  - Daftar pesanan dapat difilter berdasarkan status dan dicari menggunakan nomor order, nama/telepon penerima, atau nama/telepon Customer.
  - Daftar hasil ditampilkan dengan pagination dan tautan ke detail pesanan.
- **Status implementasi:** Terimplementasi.

#### FR-24 — Dashboard ringkasan Owner

- **Aktor:** Owner.
- **Kebutuhan:** Owner harus dapat melihat ringkasan kondisi usaha dan PO aktif.
- **Kriteria penerimaan:**
  - Dashboard menampilkan total order, jumlah Customer, total payment berstatus `Valid`, dan jumlah produk aktif.
  - Dashboard menampilkan distribusi status pesanan untuk PO aktif; bila tidak ada PO aktif, distribusi menggunakan seluruh pesanan.
  - Tautan ringkasan menuju halaman kelola Customer dan katalog produk tersedia.
- **Status implementasi:** Terimplementasi.

#### FR-25 — Direktori Customer untuk Owner

- **Aktor:** Owner.
- **Kebutuhan:** Owner harus dapat melihat dan mencari data Customer terdaftar.
- **Kriteria penerimaan:**
  - Daftar menampilkan nama, email, nomor telepon, kota, jumlah pesanan, dan tanggal pendaftaran yang tersedia.
  - Pencarian dapat mencocokkan nama, telepon, kota, atau email.
  - Daftar ditampilkan dengan pagination dan hanya dapat diakses Owner.
- **Status implementasi:** Terimplementasi.

## Aturan Bisnis yang Menjadi Acuan

1. Checkout hanya tersedia jika status periode PO `Dibuka` dan tanggal saat ini berada pada rentang `start_date` sampai `end_date` secara inklusif menurut zona waktu WIB.
2. Harga dan total checkout dihitung ulang oleh server dari data produk; nilai dari browser tidak menjadi sumber harga. Tier harga 35/60/100 ml yang ada masih perlu disahkan Owner.
3. Retry dengan token checkout yang sama harus mengembalikan pesanan sebelumnya dan tidak membuat transaksi duplikat.
4. Pembayaran manual menggunakan BCA Merchant. Gateway pembayaran otomatis belum termasuk dalam kebutuhan yang telah disepakati.
5. Bukti yang masih menunggu verifikasi menahan persetujuan pembatalan. Pembatalan atas pembayaran valid menunggu kebijakan refund.
6. Ongkir/resi dan tenggat pembayaran belum ditetapkan sebagai aturan bisnis; karena itu keduanya tidak diasumsikan oleh dokumen ini.
7. FR-22 masih memiliki konflik antara batas maksimal satu bulan pada proposal dan keputusan sementara mempertahankan bukti sampai kebijakan Owner ditetapkan. Perubahan baseline perlu persetujuan formal.

## Di Luar Cakupan Kebutuhan Saat Ini

- Payment gateway dan rekonsiliasi otomatis.
- Kalkulasi ongkos kirim, pilihan ekspedisi, nomor resi, dan tracking.
- Kedaluwarsa pesanan otomatis berdasarkan tenggat pembayaran.
- Workflow refund untuk pembayaran valid.
- Notifikasi WhatsApp otomatis.
- Varian/SKU dengan harga eksplisit per ukuran; aturan harga perlu dikonfirmasi terlebih dahulu.
