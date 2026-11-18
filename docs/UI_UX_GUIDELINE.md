# UI/UX DESIGN GUIDELINES & DESIGN SYSTEM
## Sistem Informasi Pre-Order Parfum Berbasis Web — Uyuy Jastip

Dokumen ini menjadi acuan standar desain dan implementasi frontend Uyuy Jastip (**Editorial Stone v2 — Elevated Warm Mineral Stone Design System**), selaras dengan `DESIGN_SYSTEM.md`, `references/DESIGN.md`, `references/uy-studio-variables.css`, dan sumber CSS terpusat di [`uyuy-theme.css`](file:///e:/Codex/resources/css/uyuy-theme.css).

---

### 1. Filosofi & Arah Visual (Editorial Stone v2)

Antarmuka Uyuy Jastip mengusung karakter **elevated warm mineral stone** yang bersih, terstruktur, dan fungsional lintas tiga peran:
- **Multi-Level Mineral Surface Hierarchy**: Memisahkan kanvas latar (*Warm Canvas* `#e5e3dc`), permukaan kartu utama (*Pure Ivory/Chalk* `#faf9f6` dengan hairline border dan bayangan lembut `--shadow-card`), serta zona sekunder/sidebar (*Subtle Stone* `#f1efe9` & *Tinted Stone* `#ebe8e0`). Konten tidak lagi tenggelam dalam blok abu-abu datar.
- **Controlled Multi-Weight Typography**: Hierarki teks memadukan bobot **`400`** (copy isi), **`500`** (label, navigasi, breadcrumb), dan **`600`** (heading halaman, angka KPI, nama produk, dan item menu aktif) dengan skala `12px–58px` serta `tabular-nums` pada seluruh nominal uang dan angka metrik.
- **Controlled Semantic Accents**: Menggunakan aksen *Warm Amber* (`#8b5e34` / `#f8f2e8`) secara terkendali untuk prioritas antrean pembayaran/pembatalan dan *Sage Stone* (`#2e523c` / `#ebf2ed`) untuk status valid, PO dibuka, dan transaksi selesai.
- **Three Clear Role Identities**:
  1. **Customer / Storefront**: *Editorial Fragrance E-Commerce* bersih di atas rel `1280px` dengan fokus kurasi botol parfum `1:1` dan jadwal PO transparan.
  2. **Admin Operasional**: Topbar **Graphite (`#2f2f2a`)**, KPI antrean prioritas bergaris aksen kiri, panel `TINDAKAN DIPERLUKAN` yang tegas, serta tabel pesanan dan pemeriksaan bukti pembayaran berdensitas nyaman.
  3. **Owner Eksekutif**: Topbar **Soot (`#1e1e1a`)**, panel ringkasan PO aktif, empat kartu metrik bisnis ber-ikon, manajemen periode PO & produk terfokus, serta rekapitulasi siap cetak (`@media print`).

---

### 2. Palet Warna & Token Desain (`resources/css/uyuy-theme.css`)

| Nama Token | Nilai Hex / CSS | Peran dan Penggunaan dalam UI |
| :--- | :---: | :--- |
| **Canvas** (`--color-canvas`) | `#e5e3dc` | Latar kanvas utama aplikasi dan workspace di belakang panel putih gading. |
| **Surface** (`--color-surface`) | `#faf9f6` | Latar elevated card, panel ringkasan, tabel, header publik, dan frame produk. |
| **Subtle Surface** (`--color-surface-subtle`) | `#f1efe9` | Latar kolom sidebar desktop, header kolom tabel (`thead th`), serta latar badge ikon netral. |
| **Tinted Surface** (`--color-surface-tint`) | `#ebe8e0` | Zona halus aksen, tombol sekunder hover, dan footer section. |
| **Soot** (`--color-soot`) | `#1e1e1a` | Teks utama, topbar Owner, focus ring aksesibilitas `2px`, dan heading utama. |
| **Graphite** (`--color-graphite`) | `#2f2f2a` | Tombol aksi utama (*Primary CTA*), topbar Admin, item navigasi sidebar aktif, dan dark footer. |
| **Muted Text** (`--color-muted`) | `#52514b` | Label form, header kolom tabel uppercase, timestamp, dan deskripsi pendukung. |
| **Warm Amber Accent** (`--color-accent-warm*`) | `#8b5e34` / bg `#f8f2e8` | Kartu KPI prioritas Admin (`.is-priority`), kartu perhatian Customer (`.is-attention`), dan proof status menunggu/perlu verifikasi. |
| **Sage Stone Accent** (`--color-accent-sage*`) | `#2e523c` / bg `#ebf2ed` | Badge PO terbuka (`Dibuka`), bukti pembayaran valid (`.is-valid`), dan pesanan selesai. |

#### Perbedaan Status Pesanan vs Bukti Pembayaran
- **Status Pesanan (`<x-status-badge />`, radius `4px`, `13px`)**: Merepresentasikan tahapan siklus PO/pesanan melalui kombinasi ikon (`aria-hidden="true"`), border, dan fill (`Dibuka`, `Menunggu Pembayaran`, `Pembayaran Diverifikasi`, `Diproses`, `Dikirim`, `Selesai`, `Dibatalkan`).
- **Status Bukti Pembayaran (`.admin-payment-status` & `.customer-proof-status`, radius `4px`, `12.5px–13px`)**: Menggunakan pill terpisah berkelas semantik `.is-valid`, `.is-pending`, `.is-rejected`, dan `.is-empty` agar pemindaian di kolom tabel Admin maupun Pelanggan instan tanpa bercampur dengan status pesanan.

---

### 3. Tipografi, Kontainer & Skala Geometri

- **Font Stack**: `'Inter', 'Segoe UI Variable', 'Segoe UI', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif` (bebas ketergantungan font eksternal/CDN).
- **Bobot Teks**: `--font-weight-regular: 400`, `--font-weight-medium: 500`, `--font-weight-semibold: 600`.
- **Skala Ukuran Desktop & Responsif**:
  - `--text-micro` / `--text-table-head`: `12px` (`0.75rem`) — khusus label eyebrow uppercase & header kolom tabel (`letter-spacing: .055em–.075em`).
  - `--text-meta`: `13px` (`0.8125rem`) — breadcrumb, timestamp tanggal WIB, helper formulir, tombol inline `.btn-sm`.
  - `--text-label` / `--text-caption`: `14px` (`0.875rem`) — tautan navigasi publik, label form, teks tabel utama, deskripsi kartu.
  - `--text-nav-workspace`: `14.5px` (`0.90625rem`) — item navigasi utama di sidebar Admin & Owner (`min-height: 44px`).
  - `--text-body-sm`: `15px` (`0.9375rem`) — input form (`46px`), subjudul halaman workspace, tombol CTA hero/checkout.
  - `--text-body`: `16px` (`1rem`, `line-height: 1.62`) — copy informasi PO, detail produk, dan deskripsi storefront.
  - `--text-card-title` `17–18px` | `--text-subsection` `21–26px` | `--text-subheading` `23–28px` | `--text-page-heading` & `--text-kpi` `32–38px` (`tabular-nums`) | Hero `--text-heading` `44–58px`.
- **Kontainer Layout**:
  - Storefront & Customer Portal (`--container-wide`): `1280px`
  - Workspace Admin & Owner (`--container-workspace`): `1480px` (di samping sidebar `256px`)
  - Formulir Terfokus (`--container-focused`): `880px`
- **Radius & Kedalaman Geometri**:
  - Kartu, panel ringkasan, wrapper tabel, dan kartu profil sidebar: `6px` (`--radius-cards`) + `--shadow-card`.
  - Tombol (`44px` standar, `50px` hero/submit utama, `36px` `.btn-sm`), input (`46px`), dan link navigasi: `5px` (`--radius-buttons` / `--radius-inputs`).
  - Gambar kartu, pill status, dan badge: `4px` (`--radius-images` / `--radius-badge`).

---

### 4. Standar Tata Letak Role Workspace & Responsivitas

1. **Topbar & Sticky Sidebar Anti-Clipping (`admin` & `owner`)**:
   - `.role-topbar` memiliki `min-height: 68px` dan posisi sticky `top: 0; z-index: 1030`.
   - `.role-sidebar` selebar `256px` menggunakan `position: sticky; top: 68px; height: calc(100vh - 68px); overflow-y: auto;` sehingga kartu profil di atas tidak pernah terpotong di bawah topbar ketika pengguna menggulir halaman.
   - Struktur internal `.dashboard-sidebar` memakai flex-column 3 zona (`sidebar-profile-card` -> `sidebar-nav-section` dengan `.sidebar-menu-link.active` gelap kontras tinggi -> `sidebar-footer` dengan tombol **Keluar** terjangkar rapi di dasar sidebar).
   - Pada tablet/mobile (`<= 991.98px`), sidebar beralih ke bilah navigasi horizontal single-row yang dapat digeser lancar.

2. **Integritas Tabel Desktop & Nomor Pesanan Panjang**:
   - Seluruh tabel utama (`admin-dashboard-table`, `admin-orders-table`, `admin-cancellations-table`, `admin-payments-table`, `owner-products-table`, `owner-periods-table`, `customer-orders-table`) memakai `width: 100%; min-width: 0;` pada desktop sehingga muat penuh di dalam `1366×768` tanpa menabrak area sidebar atau memaksa scrollbar horizontal desktop.
   - Kolom nomor pesanan (`UJ-PO4-...`) memakai elipsis satu baris (`max-width: 195px; overflow: hidden; text-overflow: ellipsis`) yang dapat diklik serta dilengkapi atribut `title="{{ $order->order_number }}"` agar nomor lengkap tetap mudah diakses.
   - Kolom nominal rupiah dan nomor telepon menerapkan `white-space: nowrap !important` dan `font-variant-numeric: tabular-nums` agar simbol `Rp` tidak pernah terpecah dari nominal angkanya.

3. **Target Viewport & Keamanan CSP**:
   - Target uji tata letak manual: **`1440px`**, **`1366px`**, **`768px`**, dan **`390px`** (+ guardrail `< 380px`).
   - Seluruh skrip UI interaktif berpusat di [`resources/js/app.js`](file:///e:/Codex/resources/js/app.js) tanpa inline `<script>` maupun handler `on*=`, menjaga kepatuhan `SecurityHeaders` CSP.
