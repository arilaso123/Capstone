# KRITERIA PENERIMAAN PENGUJIAN FRONTEND (ACCEPTANCE CRITERIA)
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Fase:** Phase 2 Deliverable 8  
**Tanggal Penyusunan:** 13 November 2026  
**Auditor:** QA Lead, Usability Analyst, Application Security Engineer  
**Klasifikasi:** Tolok Ukur Kualitas & Kriteria Verifikasi Phase 3  

---

## 1. PENDAHULUAN & PRINSIP PENGUJIAN

Dokumen ini mendefinisikan kriteria objektif yang dapat diuji (*testable acceptance criteria*) untuk menilai keberhasilan implementasi penyempurnaan frontend pada Phase 3. Setiap kriteria dirancang dengan formula **Given-When-Then** atau parameter terukur (*measurable metrics*) sehingga pengujian dapat diverifikasi secara empiris tanpa ambiguitas subjektif.

---

## 2. KRITERIA PENERIMAAN BERDASARKAN DOMAIN

---

### DOMAIN 1: KRITERIA VISUAL & DESIGN SYSTEM
- **AC-VIS-01 (Kepatuhan Palet Warna Mineral):**
  - Seluruh halaman wajib menggunakan palet Editorial Stone v2: Canvas `#e5e3dc`, Surface `#faf9f6`, Soot `#1e1e1a`, Graphite `#2f2f2a`, Warm Amber `#8b5e34`, dan Sage `#2e523c`.
  - Tidak diperbolehkan adanya latar belakang abu-abu dingin `#f8f9fa` atau putih silau `#ffffff` yang merusak atmosfer mineral batuan hangat.
- **AC-VIS-02 (Keterbacaan Tipografi Minimum 12px):**
  - Tidak ada satupun teks pada seluruh website yang memiliki ukuran di bawah `0.75rem` (`12px`).
  - Teks uppercase eyebrow dan table header minimal `12px` (`--text-micro`).
  - Teks body standar minimal `15px` hingga `16px` (`--text-body-sm` / `--text-body`).
- **AC-VIS-03 (Format Angka Finansial Tabular):**
  - Seluruh elemen data moneter (harga produk, total tagihan, nominal transfer, modal, laba) wajib memiliki properti CSS `font-variant-numeric: tabular-nums` dan kelas `.text-nowrap` agar simbol `Rp` tidak terpisah dari nominal angka.

---

### DOMAIN 2: KRITERIA NAVIGASI & KONSISTENSI STATUS
- **AC-NAV-01 (Indikator Menu Aktif):**
  - Setiap menu navigasi aktif (pada header publik, tab portal pelanggan, maupun sidebar admin/owner) wajib memiliki penanda visual kontras berupa lencana pil berwarna soot gelap `#1e1e1a` dengan teks ivory `#faf9f6`.
- **AC-NAV-02 (Breadcrumbs Navigasi Berfungsi):**
  - Setiap sub-halaman (detail pesanan, form tambah produk, form verifikasi) wajib memiliki breadcrumbs hierarkis yang dapat diklik untuk kembali ke halaman induknya.
- **AC-NAV-03 (Pemberitahuan Keluar Akun Terproteksi):**
  - Tombol "Keluar / Logout" pada seluruh dashboard wajib memicu form POST dengan token `@csrf`, mencegah serangan logout via CSRF.

---

### DOMAIN 3: KRITERIA INTERAKSI & FORM USABILITY
- **AC-INT-01 (Pencegahan Double Submission Formulir):**
  - **Given:** Pengguna berada di halaman Checkout, Verifikasi Pembayaran, atau Buka/Tutup Periode PO.
  - **When:** Pengguna mengklik tombol submit formulir.
  - **Then:** Tombol submit langsung berubah status menjadi `disabled = true`, teks tombol berubah menjadi "Memproses...", dan klik berulang diabaikan oleh peramban.
- **AC-INT-02 (Umpan Balik Salin Nomor Rekening BCA):**
  - **Given:** Pelanggan berada di halaman instruksi pembayaran BCA Merchant.
  - **When:** Pelanggan mengklik tombol "Salin Nomor Rekening".
  - **Then:** Nomor rekening tersalin ke clipboard sistem dan teks tombol berubah menjadi "Tersalin!" dengan ikon centang selama 2 detik sebelum kembali ke normal.
- **AC-INT-03 (Kontrol Penampil Kata Sandi / Password Toggle):**
  - **Given:** Pengguna berada di halaman Login atau Register.
  - **When:** Pengguna mengklik ikon mata di sisi kanan field kata sandi.
  - **Then:** Tipe input berubah antara `type="password"` dan `type="text"`, memungkinkan pengguna memeriksa ketikan kata sandi.
- **AC-INT-04 (Lightbox Pratinjau Bukti Transfer):**
  - **Given:** Admin berada di halaman verifikasi pembayaran.
  - **When:** Admin mengklik thumbnail foto bukti transfer.
  - **Then:** Gambar bukti transfer muncul dalam modal lightbox zoom di dalam halaman yang sama tanpa membuka tab baru di peramban.

---

### DOMAIN 4: KRITERIA RESPONSIVITAS MULTI-VIEWPORT
- **AC-RES-01 (Nol Overflow Horizontal pada 360px s/d 1920px):**
  - Pada seluruh pengujian viewport (`360×800`, `390×844`, `768×1024`, `1366×768`, `1440×900`, `1920×1080`), evaluasi script:
    ```javascript
    document.body.scrollWidth <= document.body.clientWidth
    ```
    harus bernilai `TRUE` (Nol scrollbar horizontal pada body).
- **AC-RES-02 (Adaptasi Grid Katalog pada Layar Ponsel Kompak):**
  - Pada layar dengan lebar `< 380px`, grid produk katalog wajib otomatis menjadi 1 kolom penuh agar teks nama dan harga terbaca utuh.
- **AC-RES-03 (Navigasi Swipe Horizontal Tab Portal):**
  - Pada layar smartphone, tab portal pelanggan (`.customer-portal-nav`) dapat digeser secara horizontal tanpa memecah navigasi menjadi tumpukan vertikal berantakan.

---

### DOMAIN 5: KRITERIA AKSESIBILITAS (WCAG 2.1 LEVEL AA)
- **AC-ACC-01 (Rasio Kontras Minimum 4.5:1):**
  - Seluruh teks body memiliki rasio kontras terhadap latar belakangnya minimal `4.5:1` (Soot `#1e1e1a` di atas Ivory `#faf9f6` memiliki rasio `15.1:1`).
- **AC-ACC-02 (Cincin Fokus Keyboard Terarah):**
  - Saat menavigasi dengan tombol `Tab`, elemen yang sedang aktif wajib menampilkan outline setebal `2px solid #8b5e34` dengan offset 2px.
- **AC-ACC-03 (Target Sentuh Minimum 44px):**
  - Seluruh tombol utama dan target sentuh interaktif memiliki tinggi visual minimal `44px`.
- **AC-ACC-04 (Accessible Form Error Announcement):**
  - Field input yang gagal divalidasi memiliki atribut `aria-invalid="true"` dan `aria-describedby` yang merujuk pada elemen ID pesan kesalahan.

---

### DOMAIN 6: KRITERIA INTEGRITAS PROSES BISNIS & REGRESI
- **AC-BUS-01 (Kalkulasi Harga Sisi Server Utuh):**
  - Harga barang dihitung murni di sisi server dari `product_variants.price`. Tidak ada kalkulasi harga total yang mengandalkan input dari klien.
- **AC-BUS-02 (Pencegahan IDOR Mutlak):**
  - Pelanggan A yang mencoba membuka URL pesanan milik Pelanggan B wajib menerima respons **HTTP 403 Forbidden**.
- **AC-BUS-03 (Kelulusan Automated Test 100% / Zero Regression):**
  - Seluruh rangkaian pengujian otomatis PHPUnit harus lulus penuh tanpa kegagalan:
    ```
    Tests: 31 passed (256 assertions)
    ```

---

### DOMAIN 7: KRITERIA KHUSUS PERBAIKAN CRITICAL BUG P0
- **AC-BUG-01 (Resolusi Error 500 pada Edit Produk Owner):**
  - **Given:** Akun Owner yang telah login membuka URL `/owner/produk/{id}/edit`.
  - **When:** Halaman dimuat.
  - **Then:**
    1. Server merespons dengan status HTTP `200 OK` (Nol ParseError).
    2. Formulir terisi dengan nama produk, kategori aroma, harga per ukuran (35ml, 60ml, 100ml), modal per ukuran, dan pratinjau foto produk saat ini.
    3. Mengubah harga atau foto produk dan menekan "Perbarui Produk" berhasil menyimpan data ke database dan mengalihkan kembali ke daftar produk dengan alert sukses.

---

## 3. CHECKLIST VERIFIKASI SEBELUM SIGN-OFF PHASE 3

| ID Kriteria | Deskripsi Pengujian | Metode Verifikasi | Penanggung Jawab |
| :---: | :--- | :--- | :---: |
| **AC-BUG-01** | Buka `/owner/produk/1/edit` di browser | Live Browser Inspection (HTTP 200) | QA Lead |
| **AC-INT-01** | Klik ganda form checkout & verifikasi | Interaksi Browser (Disabled Button) | Frontend Lead |
| **AC-INT-02** | Klik salin rekening BCA | Evaluasi Clipboard Browser | Frontend Lead |
| **AC-INT-04** | Klik pratinjau bukti bayar admin | Buka Modal Lightbox In-Page | UI/UX Lead |
| **AC-RES-01** | Evaluasi 6 viewport (360px s/d 1920px) | Script DOM Overflow Evaluation | Frontend Lead |
| **AC-ACC-01** | Periksa rasio kontras teks inti | Color Contrast Analyzer (>= 4.5:1) | Accessibility Lead |
| **AC-BUS-03** | Eksekusi `php artisan test` | CLI Automated Runner (31/31 Pass)| Backend Lead |

---

## 4. KESIMPULAN KRITERIA PENERIMAAN

Seluruh kriteria penerimaan di atas telah dirancang secara presisi dan dapat diuji secara mekanis (*deterministic & reproducible*). Phase 3 dinyatakan selesai hanya jika **100% kriteria dalam dokumen ini terpenuhi tanpa kompromi**.
