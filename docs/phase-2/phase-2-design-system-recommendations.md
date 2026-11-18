# REKOMENDASI SISTEM DESAIN EDITORIAL STONE v2 (DESIGN SYSTEM AUDIT)
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Fase:** Phase 2 Deliverable 6  
**Tanggal Evaluasi:** 13 November 2026  
**Auditor:** Design System Architect, Lead UI/UX Designer, Senior Frontend Engineer  
**Klasifikasi:** Audit Token Desain & Standardisasi Komponen Bersama (Shared Components)  

---

## 1. PENDAHULUAN & PRINSIP DESAIN

Sistem desain aktif Uyuy Jastip adalah **Editorial Stone v2** (*Elevated Warm Mineral Stone Design System*). Sistem ini mengintegrasikan keanggunan kurasi editorial parfum mewah dengan efisiensi tata kerja operasional pergudangan pre-order.

Prinsip Utama:
- **Warm Mineral Canvas:** Menggunakan palet mineral batuan hangat (canvas `#e5e3dc` dan surface `#faf9f6`), menolak warna abu-abu dingin teknologi (*cold tech gray*) maupun putih murni silau (*harsh stark white*).
- **Crisp Sans Typography:** Menggunakan tipografi sans modern multi-weight (`400/500/600`) dengan rasio keterbacaan tinggi, nol dependensi jaringan luar.
- **Micro-radii & Structural Borders:** Garis pemisah mineral setebal 1px (`rgba(30,30,26,.13)`) dan sudut membulat mikro (`4px` hingga `6px`), menghindari efek rounded berlebihan (*no bubble/pill childish styling*).
- **Tabular Financial Figures:** Seluruh angka moneter, kuantitas, dan persentase diwajibkan menggunakan `font-variant-numeric: tabular-nums`.

---

## 2. AUDIT TOKEN DESAIN GLOBAL (GLOBAL TOKENS AUDIT)

---

### 2.1. Color Tokens (Palet Warna Mineral)
| Token CSS | Nilai HEX / RGBA | Peran Semantik | Status Evaluasi |
| :--- | :--- | :--- | :---: |
| `--color-canvas` | `#e5e3dc` | Latar belakang kanvas mineral dasar | **KEEP** |
| `--color-surface` | `#faf9f6` | Latar kartu putih ivory, panel konten | **KEEP** |
| `--color-surface-subtle` | `#f1efe9` | Latar header tabel, bilah filter, hover sel | **KEEP** |
| `--color-soot` | `#1e1e1a` | Teks judul utama (H1, H2), teks heading tebal | **KEEP** |
| `--color-graphite` | `#2f2f2a` | Tombol utama, teks body standar, ikon | **KEEP** |
| `--color-muted` | `#52514b` | Teks sekunder, label pembantu, breadcrumb | **KEEP** |
| `--color-line` | `rgba(30, 30, 26, .13)` | Garis batas kartu, pemisah baris tabel | **KEEP** |
| `--color-accent-warm` | `#8b5e34` | Aksen amber: cincin fokus, status prioritas | **KEEP** |
| `--color-accent-sage` | `#2e523c` | Aksen sage: lencana PO dibuka, status selesai | **KEEP** |
| `--color-accent-alert` | `#9c3d2e` | Aksen terracotta: peringatan error, tombol batal| **KEEP** |

---

### 2.2. Typography Tokens (Skala Tipografi Keterbacaan)
| Token CSS | Ukuran Rem / Px | Target Penggunaan | Status Evaluasi |
| :--- | :--- | :--- | :---: |
| `--text-micro` | `0.75rem` (`12px`) | Uppercase table header, label lencana kecil | **KEEP** |
| `--text-meta` | `0.8125rem` (`13px`) | Waktu transaksi, helper text, breadcrumbs | **KEEP** |
| `--text-label` | `0.875rem` (`14px`) | Label form, sel data tabel, tombol standar | **KEEP** |
| `--text-nav-workspace`| `0.90625rem` (`14.5px`)| Menu navigasi sidebar Admin & Owner | **KEEP** |
| `--text-body-sm` | `0.9375rem` (`15px`) | Teks input form, ringkasan deskripsi kartu | **KEEP** |
| `--text-body` | `1rem` (`16px`) | Deskripsi editorial parfum, paragraf panduan| **KEEP** |
| `--text-card-title` | `clamp(1.0625rem, 1.25vw, 1.125rem)` | Judul kartu, sub-panel dashboard | **KEEP** |
| `--text-subsection` | `clamp(1.3125rem, 1.65vw, 1.625rem)` | Judul seksi halaman (H2) | **KEEP** |
| `--text-page-heading`| `clamp(2rem, 2.55vw, 2.375rem)` | Judul halaman utama H1 dashboard | **KEEP** |
| `--text-kpi` | `clamp(2rem, 2.5vw, 2.375rem)` | Angka metrik ringkasan bisnis | **KEEP** |
| `--text-heading` | `clamp(2.75rem, 4.4vw, 3.625rem)` | Judul hero etalase publik (H1 storefront) | **KEEP** |

---

### 2.3. Spacing & Container Scale
| Token CSS | Nilai | Cakupan Penggunaan | Status Evaluasi |
| :--- | :--- | :--- | :---: |
| `--spacing-4` s/d `16`| `4px`, `8px`, `12px`, `16px` | Gap antar-ikon, padding tombol, margin label | **KEEP** |
| `--spacing-24` s/d `32`| `24px`, `32px` | Jarak antar kartu dashboard, padding formulir | **KEEP** |
| `--section-gap` | `clamp(3rem, 5vw, 4.75rem)` | Jarak vertikal antar seksi halaman | **KEEP** |
| `--container-focused` | `880px` | Halaman otentikasi (login/register), form checkout | **KEEP** |
| `--container-content` | `1280px` | Konten publik (beranda, katalog, info PO) | **KEEP** |
| `--container-workspace`| `1480px` | Workspace dashboard Admin, Owner, Pelanggan | **KEEP** |

---

## 3. AUDIT STANDARDISASI KOMPONEN BERSAMA (COMPONENT AUDIT)

```
+------------------------------------------------------------------------------------------------------+
|                           MATRIKS AUDIT KOMPONEN EDITORIAL STONE v2                                  |
+------------------------------------------------------------------------------------------------------+
| Komponen          | Implementasi Saat Ini       | Isu yang Ditemukan         | Keputusan | Standar   |
+-------------------+-----------------------------+----------------------------+-----------+-----------+
| Button System     | .btn-dark, .btn-outline     | Sedikit variasi padding    | POLISH    | Standard  |
| Form Controls     | .form-control, .form-select | Datepicker native beda OS  | POLISH    | Standard  |
| Card KPI Metric   | .admin-dashboard-metric     | Struktur sudah sangat rapi | KEEP      | Reusable  |
| Table Data        | .table, .table-responsive   | Format nominal Rp rapi     | KEEP      | Standard  |
| Status Badges     | .badge-po-status, pills     | Warna status sudah semantik| KEEP      | Standard  |
| Stepper Timeline  | .order-stepper              | Visualisasi status pesanan | KEEP      | Reusable  |
| Modal Dialog      | Bootstrap standard modal    | Perlu styling mineral halus| POLISH    | Standard  |
| Alert Banner      | .alert-custom               | Sudah berikon dan dismiss  | KEEP      | Standard  |
| Image Preview     | Membuka tab baru            | Mengganggu alur admin      | REFACTOR  | Lightbox  |
| Form Edit Produk  | Syntax error Blade inline   | Crash HTTP 500             | REFACTOR  | Bugfix P0 |
+------------------------------------------------------------------------------------------------------+
```

### 3.1. Button System (Sistem Tombol Aksi)
- **Kondisi Saat Ini:** Tombol utama menggunakan kelas `.btn-dark` dengan latar soot `#2f2f2a`, teks putih ivory `#faf9f6`, sudut `5px`, dan transisi warna halus. Tombol sekunder menggunakan `.btn-outline-secondary` dengan garis batas mineral.
- **Penyempurnaan (POLISH):** Standardisasi tinggi tombol:
  - Tombol Primer / Besar: tinggi `44px`, padding `10px 24px`, font size `14px` medium (`--text-label`).
  - Tombol Sekunder / Tabel: tinggi `34px`, padding `6px 14px`, font size `13px` (`--text-meta`).
  - Efek Hover: Transformasi translateY(-1px) dengan bayangan mineral lembut `0 2px 6px rgba(30,30,26,.12)`.

### 3.2. Form Control System (Sistem Input Formulir)
- **Kondisi Saat Ini:** Input teks, select, dan textarea telah memiliki border mineral `1px solid rgba(30,30,26,.18)` dengan sudut `5px`. Saat fokus, cincin 2px amber muncul (`outline: 2px solid var(--color-accent-warm)`).
- **Penyempurnaan (POLISH):** Berikan padding internal seragam `10px 14px` pada seluruh input formulir agar placeholder dan teks ketikan tidak menyentuh batas border.

### 3.3. Card & Panel Hierarchy (Sistem Kartu & Kontainer)
- **Kondisi Saat Ini:** Kartu menggunakan latar belakang putih ivory `--color-surface` (`#faf9f6`), border halus `--color-line` (`rgba(30,30,26,.13)`), radius `--radius-card: 6px`, dan bayangan mikro `0 1px 3px rgba(30,30,26,.05)`.
- **Keputusan:** **KEEP (100% DIPERTAHANKAN)**. Kartu memiliki hierarki kontras yang sempurna di atas kanvas mineral `#e5e3dc`.

### 3.4. Table Component System (Sistem Tabel Data Operasional)
- **Kondisi Saat Ini:** Tabel dibungkus dalam `.table-responsive` dengan header abu-abu mineral halus (`#f1efe9`), teks tebal soot, sel padding `12px 14px`, dan format angka `tabular-nums` non-wrapping.
- **Keputusan:** **KEEP (100% DIPERTAHANKAN)**.

### 3.5. Status Badge System (Lencana Status Transaksi & Periode)
- **Kondisi Saat Ini:**
  - Status Valid / PO Dibuka / Selesai: Latar sage tint (`#ebf2ed`), teks sage gelap (`#2e523c`), border sage (`#b6ccbe`).
  - Status Menunggu Pembayaran / Prioritas: Latar amber tint (`#f8f2e8`), teks amber ink (`#6d4623`), border amber (`#ddc6ad`).
  - Status Diproses: Latar slate tint, teks slate gelap.
  - Status Dibatalkan / Ditolak: Latar terracotta tint, teks merah terracotta.
- **Keputusan:** **KEEP (100% DIPERTAHANKAN)**. Palet semantik sudah sangat konsisten dan terbaca jelas.

---

## 4. DAFTAR KOMPONEN YANG PERLU DIREFAKTOR & DISTANDARDISASI

1. **Refactoring Komponen Form Edit Produk (`owner/products/edit.blade.php`):**
   - **Masalah:** Inline Blade `@if` di atribut tag memicu ParseError 500.
   - **Solusi:** Bersihkan sintaksis Blade menjadi blok template terstruktur yang valid.
2. **Standardisasi Modal Lightbox Pratinjau Gambar (`admin/payments/verify.blade.php`):**
   - **Masalah:** Mengklik gambar struk transfer membuka tab browser baru.
   - **Solusi:** Buat komponen modal lightbox zoom in-page menggunakan JavaScript Vanilla ringan.
3. **Penyatuan Aset Bootstrap CDN ke dalam Bundle Vite:**
   - **Masalah:** Ketergantungan CDN eksternal `jsdelivr.net`.
   - **Solusi:** Pasang paket npm bootstrap dan compile ke aset lokal Vite (`app.css` & `app.js`).

---

## 5. KESIMPULAN AUDIT SISTEM DESAIN

Sistem desain **Editorial Stone v2** telah membuktikan keunggulannya secara estetis dan fungsional pada seluruh platform Uyuy Jastip. **TIDAK DIPERLUKAN PENGGANTIAN NAMA ATAU PERUBAHAN TEMA BARU**. Rencana kerja Phase 3 harus berfokus pada standardisasi token yang ada dan penyelesaian refactoring komponen yang mengalami galat.
