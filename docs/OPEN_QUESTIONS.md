# OPEN QUESTIONS & IMPLEMENTATION DECISIONS
## Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web - Uyuy Jastip

Pembaruan keputusan teknis: 10 November 2026. Pilihan Owner pada percakapan menjadi sumber kebijakan ketika berbeda dari proposal lama.

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

#### A. Tenggat Pembayaran
- **Keputusan Teknis Sementara**: Batas default 24 jam (`PAYMENT_DEADLINE_HOURS`), dapat diubah melalui environment. Scheduler memeriksa order per jam. Order dengan bukti transfer yang masih menunggu verifikasi atau permohonan pembatalan pending tidak dibatalkan otomatis.
- **Tindakan Owner**: Konfirmasi tenggat 24 jam sebelum produksi. Penjadwal aplikasi harus diaktifkan pada host.

#### B. Kebijakan Pembatalan dan Refund
- **Status Proposal**: [PROPOSAL] Pembatalan pesanan harus diajukan oleh Customer dan memperoleh persetujuan Admin terlebih dahulu (FR-14, FR-15). Aturan batas waktu pengajuan atau ketentuan pembatalan belum ditentukan spesifik.
- **Keputusan Owner**: Admin boleh menyetujui pembatalan pesanan yang sudah dibayar setelah mencatat transfer refund manual terlebih dahulu.
- **Implementasi**: Admin wajib memasukkan nominal penuh yang sama dengan pembayaran terverifikasi, nomor referensi transfer, dan bukti gambar refund privat sebelum persetujuan. Sistem menyimpan metadata dan bukti pada permohonan pembatalan dan menulis audit trail. Bukti pending tetap memblokir approval; bukti ditolak memerlukan catatan rekonsiliasi.

#### C. Retensi Bukti Pembayaran
- **Keputusan Owner**: Pertahankan bukti sampai kebijakan arsip/purge ditetapkan. Ini menggantikan target proposal lama maksimal satu bulan.
- **Implementasi**: Bukti dan attempt tetap privat dan tidak dihapus otomatis. `payments:secure-proofs` memindahkan file legacy (dry-run secara default). Menetapkan masa retensi, lokasi arsip eksternal, dan jadwal purge tetap memerlukan keputusan Owner.

#### G. Aturan Tanggal Periode Pre-Order
- **Keputusan Owner (7 November 2026)**: Checkout hanya boleh dilakukan saat status periode `Dibuka` dan tanggal saat ini menurut zona waktu `Asia/Jakarta` berada di antara `start_date` dan `end_date`, termasuk kedua tanggal batas. Tanggal akhir berlaku sampai pukul 23.59 WIB.
- **Implementasi**: Katalog dan keranjang memakai cache aktif 30 detik; checkout membaca database langsung, mengecek tanggal lagi, dan mengambil shared lock. Checkout paralel dapat berjalan, sementara penutupan Owner menunggu transaksi aktif selesai. Status administrasi tidak otomatis berubah setelah tanggal akhir.

#### D. Pengiriman & Biaya Ongkos Kirim (Shipping Fee & Logistics)
- **Status Proposal**: [PROPOSAL] Belum ada formulasi ongkos kirim atau pemilihan ekspedisi dalam proposal. Fokus utama proposal adalah pengelolaan PO parfum jastip, penerimaan pesanan, verifikasi pembayaran BCA Merchant, dan pemantauan status pesanan (`Barang Dikirim`, `Selesai`).
- **Implementasi**: Data kurir, nomor resi, ongkir, dan kode pos tercatat terstruktur. Ongkir tetap ditagihkan terpisah dari checkout barang; Admin memasukkan nilai setelah mengetahui biaya ekspedisi. Jangan mengarang nilai ongkir sebelum proses operasional Owner ditetapkan.

#### E. Penetapan Format Nomor Pesanan / Kode Referensi
- **Status Proposal**: [KEPUTUSAN TEKNIS] Sistem membutuhkan nomor unik pesanan yang mudah dilacak dan dibaca oleh Admin maupun Customer saat konfirmasi.
- **Keputusan Teknis**: Menggunakan format `UJ-PO{id_periode}-{YYYYMMDD}-{ULID}` untuk menghindari kontensi counter saat checkout paralel.

#### F. Pengelolaan Varian Ukuran / Aroma Parfum
- **Status Proposal**: [PROPOSAL] Uyuy Jastip mengambil parfum dari vendor "In Parfume". Jumlah produk yang ditawarkan dalam satu periode PO tidak dibatasi.
- **Keputusan Teknis**: Satu produk dapat memiliki ukuran eksplisit pada tabel `product_variants`. Owner menentukan harga jual dan modal untuk tiap ukuran yang benar-benar tersedia. Migrasi hanya mengisi ukuran/harga lama yang sudah tercatat; harga ukuran lain tidak ditebak.

#### H. Backup dan Pemulihan
- Perintah `database:backup` membuat dump MySQL/MariaDB terkompresi pada direktori privat dan scheduler menjadwalkannya setiap hari pukul 02.00 WIB. Host harus menjalankan `php artisan schedule:run` setiap menit.
- Panduan restore dan latihan pemulihan ada di [BACKUP_AND_RECOVERY.md](docs/BACKUP_AND_RECOVERY.md). Restore drill terpisah tetap perlu dilakukan sebelum produksi.

