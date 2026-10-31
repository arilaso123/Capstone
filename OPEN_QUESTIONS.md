# OPEN QUESTIONS & IMPLEMENTATION DECISIONS
## Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web - Uyuy Jastip

Dokumen ini memetakan batasan antara fakta kebutuhan yang telah tercantum dalam Dokumen Proposal Capstone, keputusan teknis sementara yang diambil untuk implementasi prototipe, serta rekomendasi bisnis yang masih membutuhkan konfirmasi dan persetujuan dari Owner Uyuy Jastip.

---

### 1. Klasifikasi Informasi

| Kategori | Definisi |
| :--- | :--- |
| **[PROPOSAL]** | Fakta dan kebutuhan yang telah tercantum secara eksplisit dalam proposal dan hasil wawancara 2 Oktober 2026. |
| **[KEPUTUSAN TEKNIS]** | Keputusan teknis arsitektural dan implementasi yang diambil pengembang untuk membangun prototipe yang andal, aman, dan dapat diuji. |
| **[REKOMENDASI BISNIS]** | Aturan bisnis atau kebijakan operasional yang belum ditentukan oleh mitra dan memerlukan konfirmasi dari pemilik usaha. |

---

### 2. Daftar Pertanyaan Terbuka & Rekomendasi (Open Questions)

#### A. Kebijakan & Tenggat Pembayaran (Payment Deadline)
- **Status Proposal**: [PROPOSAL] Pembayaran dilakukan melalui BCA Merchant dan bukti pembayaran diperiksa manual oleh Admin. Namun batas waktu pembayaran (payment deadline) belum ditentukan secara spesifik oleh mitra.
- **Keputusan Teknis Sementara**: Pesanan yang baru dibuat berstatus `Menunggu Pembayaran`. Sistem tidak membatalkan otomatis secara sepihak tanpa konfirmasi aturan, namun memberikan penanda visual waktu pembuatan pesanan.
- **Rekomendasi Bisnis**: Apakah Owner menginginkan tenggat waktu pembayaran (misalnya $1 \times 24$ jam atau sampai periode PO ditutup)? Jika melewati batas waktu, apakah pesanan otomatis kedaluwarsa atau harus dibatalkan manual oleh pengelola?

#### B. Kebijakan & Batas Waktu Pengajuan Pembatalan (Cancellation Policy)
- **Status Proposal**: [PROPOSAL] Pembatalan pesanan harus diajukan oleh Customer dan memperoleh persetujuan Admin terlebih dahulu (FR-14, FR-15). Aturan batas waktu pengajuan atau ketentuan pembatalan belum ditentukan spesifik.
- **Keputusan Teknis Sementara**: Customer hanya dapat mengajukan permohonan pembatalan ketika pesanan masih dalam status `Menunggu Pembayaran` atau `Pembayaran Diverifikasi`. Jika pesanan sudah masuk status `Diproses`, tombol ajukan pembatalan dinonaktifkan dengan pesan informatif agar menghubungi admin via WhatsApp.
- **Rekomendasi Bisnis**: Apakah Customer diizinkan mengajukan pembatalan setelah pembayaran diverifikasi? Jika dibatalkan setelah pembayaran diverifikasi, bagaimana mekanisme pengembalian dana (refund) oleh Owner/Admin?

#### C. Masa Retensi & Penghapusan Berkas Bukti Pembayaran (File Retention)
- **Status Proposal**: [PROPOSAL] Bukti pembayaran disimpan dalam bentuk berkas dengan masa retensi maksimal 1 (satu) bulan sesuai ketentuan mitra (FR-22, NFR-11). Tindakan teknis setelah masa retensi berakhir perlu ditetapkan sebelum implementasi final.
- **Keputusan Teknis Sementara**: Sistem mencatat kolom `uploaded_at` dan menyediakan filter bukti pembayaran $> 30$ hari untuk Owner, serta skrip konsol/command `php artisan jastip:purge-payment-proofs` yang hanya menghapus file gambar fisik dari storage namun tetap mempertahankan riwayat log transaksi di database demi integritas data audit. Penghapusan otomatis cron tidak dijalankan secara sepihak sebelum disetujui.
- **Rekomendasi Bisnis**: Apakah bukti pembayaran setelah 30 hari ingin dihapus permanen dari server atau diarsipkan ke cloud storage terpisah?

#### D. Pengiriman & Biaya Ongkos Kirim (Shipping Fee & Logistics)
- **Status Proposal**: [PROPOSAL] Belum ada formulasi ongkos kirim atau pemilihan ekspedisi dalam proposal. Fokus utama proposal adalah pengelolaan PO parfum jastip, penerimaan pesanan, verifikasi pembayaran BCA Merchant, dan pemantauan status pesanan (`Barang Dikirim`, `Selesai`).
- **Keputusan Teknis Sementara**: Pada saat checkout, Customer memasukkan nama penerima, nomor WhatsApp aktif, dan alamat pengiriman lengkap. Ongkos kirim untuk sementara ditandai sebagai "Konfirmasi via WhatsApp / Ditagihkan Terpisah" atau nilai estimasi Rp0 agar tidak mengarang nominal biaya kirim yang belum ditetapkan mitra.
- **Rekomendasi Bisnis**: Bagaimana skema penagihan ongkos kirim yang berjalan saat ini di Uyuy Jastip (apakah sudah termasuk dalam harga botol jastip, ditagih saat barang tiba di Jakarta sebelum dikirim ke ekspedisi lokal seperti JNE/J&T/SiCepat, atau COD)?

#### E. Penetapan Format Nomor Pesanan / Kode Referensi
- **Status Proposal**: [KEPUTUSAN TEKNIS] Sistem membutuhkan nomor unik pesanan yang mudah dilacak dan dibaca oleh Admin maupun Customer saat konfirmasi.
- **Keputusan Teknis**: Menggunakan format `UJ-PO{id_periode}-{YYYYMMDD}-{sequence}`, contoh: `UJ-PO1-20261030-001`.

#### F. Pengelolaan Varian Ukuran / Aroma Parfum
- **Status Proposal**: [PROPOSAL] Uyuy Jastip mengambil parfum dari vendor "In Parfume". Jumlah produk yang ditawarkan dalam satu periode PO tidak dibatasi.
- **Keputusan Teknis Sementara**: Data produk memiliki atribut nama produk, brand/kategori aroma, ukuran (ml), harga satuan, deskripsi, foto produk, dan status aktif/nonaktif.
- **Rekomendasi Bisnis**: Apakah satu produk parfum memiliki beberapa varian ukuran (misal 35ml, 60ml, 100ml) dalam satu halaman produk, atau setiap ukuran didaftarkan sebagai item produk tersendiri di katalog?

