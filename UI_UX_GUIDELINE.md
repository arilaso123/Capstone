# UI/UX DESIGN GUIDELINES & DESIGN SYSTEM
## Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web - Uyuy Jastip

---

### 1. Filosofi & Karakter Visual
Desain antarmuka Uyuy Jastip dirancang dengan konsep:
- **Minimalist & Clean**: Menghindari elemen visual yang ramai, berantakan, atau distraksi yang tidak diperlukan.
- **Elegant & Premium Perfume Store**: Mengadopsi estetika butik parfum mewah dengan palet warna bernuansa natural, hangat, dan berkelas.
- **Modern & Professional**: Tipografi yang sangat mudah dibaca, navigasi yang intuitif, dan responsif di berbagai perangkat.
- **Consistent & Role-Tailored**: Tampilan publik dan customer memberikan pengalaman belanja yang nyaman; sementara dashboard admin dan owner berfokus pada efisiensi operasional tanpa kehilangan sentuhan elegan.

---

### 2. Design System & Palet Warna (Color Palette)

| Nama Warna | Hex Code | Peran dan Penggunaan dalam UI |
| :--- | :---: | :--- |
| **Warm Ivory** | `#F7F4EF` | Latar belakang utama aplikasi (memberikan kesan hangat dan premium, bukan putih dingin standar). |
| **Champagne** | `#D8C5AE` | Aksen sekunder, banner PO aktif, border halus, badge sekunder, efek hover tombol lembut. |
| **Mocha** | `#8B6B50` | Tombol aksi utama (Primary CTA), header tabel operasional, link aktif, elemen fokus brand. |
| **Charcoal** | `#302B27` | Teks utama, heading judul tebal, navbar gelap (opsional), teks dengan kontras tinggi dan ramah mata. |
| **White** | `#FFFFFF` | Latar kartu (card), kontainer form modal, dropdown, dan elemen elevated surfaces. |
| **Soft Gray** | `#E8E2DA` | Garis batas (border), garis pemisah (divider), latar belakang input field, status badge netral. |

#### Palet Warna Status Khusus (Semantic Status Tones)
Menggunakan warna indikator fungsional yang telah disesuaikan agar tetap harmonis dengan nuansa pastel/earthy:
- **Menunggu Pembayaran**: Kuning gading hangat / Amber (`#C28414`, latar `#FEF5E7`)
- **Pembayaran Diverifikasi**: Biru slate lembut (`#2B6CB0`, latar `#EBF8FF`)
- **Diproses**: Ungu lavender elegan (`#6B46C1`, latar `#FAF5FF`)
- **Barang Dikirim**: Cyan toska berkelas (`#0D9488`, latar `#F0FDFA`)
- **Selesai**: Hijau sage botani (`#2E7D32`, latar `#EDF7ED`)
- **Dibatalkan / Ditolak**: Merah bata muted (`#C53030`, latar `#FFF5F5`)

---

### 3. Tipografi (Typography Hierarchy)

1. **Heading & Elemen Branding**: `Playfair Display`, serif.
   - Digunakan untuk: Logo / Brand text, Heading halaman publik (`h1`, `h2`), Hero banner, Judul katalog.
   - Karakter: Anggun, klasik, eksklusif, merefleksikan wewangian premium.
2. **Body, Navigation, Data & Form**: `Inter`, sans-serif.
   - Digunakan untuk: Paragraf deskripsi, label form, teks tombol, tabel data operasional, badge status, angka rekapitulasi.
   - Karakter: Sangat terbaca pada resolusi layar smartphone maupun laptop, modern, netral.

```css
/* Acuan Skala Tipografi */
h1, .font-heading { font-family: 'Playfair Display', Georgia, serif; font-weight: 700; }
body, input, button, table { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
```

---

### 4. Standar Komponen UI

#### A. Header / Navbar
- Tampilan sticky atas dengan latar belakang transparan/Warm Ivory blur (`backdrop-filter: blur(10px)`).
- Menampilkan logo "Uyuy Jastip" dengan tipografi Playfair Display.
- Menu navigasi publik: *Beranda*, *Katalog Parfum*, *Informasi PO*.
- Area kanan: Tombol *Masuk (Login)* & *Daftar*, atau Menu Profil Dropdown jika sudah login (dengan badge role: Customer / Admin / Owner).
- Responsif: Berubah menjadi burger menu yang rapi di layar ponsel.

#### B. Hero Section (Homepage)
- Komposisi seimbang dengan ruang kosong (*negative space*) yang lega.
- Headline puitis tentang aroma favorit dan jastip parfum terpercaya.
- Card indikator status PO Periode saat ini (menampilkan tanggal dan status: *Dibuka* atau *Ditutup*).
- Dua tombol CTA: "Lihat Koleksi Parfum" (Primary - Mocha) dan "Pelajari Alur PO" (Secondary Outline - Mocha).

#### C. Card Produk (Product Card)
- Aspek rasio foto produk seragam ($4:5$ atau $1:1$) dengan sudut melengkung halus (`rounded-3`).
- Efek *subtle shadow* saat disentuh/hover (`transition: transform 0.25s ease`).
- Menampilkan nama parfum, ukuran (contoh: 35ml / 60ml / 100ml), deskripsi aroma singkat, dan status ketersediaan.
- Tombol aksi "Lihat Detail" atau "Pesan Sekarang".

#### D. Badges Status Pesanan
- Kompak, elegan, dengan kontras warna yang jelas:
  * `<span class="badge badge-po-open">PO Dibuka</span>`
  * `<span class="badge badge-status-waiting">Menunggu Pembayaran</span>`
  * `<span class="badge badge-status-verified">Pembayaran Diverifikasi</span>`
  * `<span class="badge badge-status-processing">Diproses</span>`
  * `<span class="badge badge-status-shipped">Barang Dikirim</span>`
  * `<span class="badge badge-status-completed">Selesai</span>`

#### E. Order Timeline Stepper
- Komponen progres visual di halaman detail pesanan Customer dan Admin.
- Menunjukkan 5 langkah status pemenuhan pesanan secara terurut dengan lingkaran indikator dan garis progres.

#### F. Form Input & Modals
- Border lembut `#E8E2DA`, saat fokus berubah ke warna aksen Mocha `#8B6B50` dengan *soft ring glow*.
- Validasi pesan kesalahan di bawah input menggunakan warna merah terkalibrasi (`#C53030`) yang jelas dan ramah pengguna.
- Dialog konfirmasi aksi sensitif (misal: verifikasi pembayaran, tolak permohonan pembatalan, tutup periode PO) menggunakan modal konfirmasi eksplisit.

#### G. Tabel Data Operasional (Data Table)
- Tabel dengan garis batas halus (`table-hover`).
- Header tabel dengan latar belakang Mocha lembut atau Champagne.
- Mekanisme *horizontal scrolling container* (`table-responsive`) di perangkat smartphone agar data tidak terpotong.
- Kolom aksi yang jelas dengan tombol berikon Bootstrap Icons.

---

### 5. Prinsip Responsivitas (Mobile-First & Laptop Optimized)
Sesuai hasil wawancara bahwa pengelola (Owner dan Admin) maupun Customer menggunakan **smartphone** dan **laptop**:
- Grid Bootstrap 5 responsif: `col-12 col-md-6 col-lg-4`.
- Ukuran target sentuh tombol minimal 44x44 piksel pada perangkat mobile.
- Sidebar Dashboard Admin/Owner bertransisi menjadi off-canvas drawer yang mulus pada layar kecil ($< 992\text{px}$).
- Tidak ada overflow horizontal yang menyebabkan halaman bergeser ke samping.

