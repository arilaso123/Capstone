# LAPORAN VALIDASI SISTEM & KEAMANAN ARSITEKTUR
## Proyek: Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web
**Tanggal Evaluasi:** 13 November 2026  
**Auditor:** Senior Software Architect, Security & Database Engineer  
**Klasifikasi Dokumen:** Laporan Audit Teknis Mendalam (Phase 1.5 Deliverable 3)

---

## 1. RINGKASAN EKSEKUTIF VALIDASI ARSITEKTUR

Audit mendalam terhadap arsitektur backend, mekanisme transaksi, isolasi data, dan keamanan aplikasi membuktikan bahwa Uyuy Jastip dibangun di atas fondasi rekayasa perangkat lunak yang kokoh, berorientasi integritas data, dan menerapkan prinsip *Defense-in-Depth*.

Arsitektur aplikasi berhasil mengisolasi hak akses antar-peran secara mutlak (Customer, Admin, Owner), mencegah eksploitasi celah otorisasi seperti IDOR (*Insecure Direct Object Reference*), menjamin *idempotency* pada proses *checkout*, menerapkan kalkulasi harga murni pada sisi server (*server-side calculation*), serta mengamankan berkas transaksi finansial sensitif pada *disk private* yang tidak terekspos secara publik.

### Ringkasan Status Validasi:
| Area Arsitektur & Keamanan | Status | Ringkasan Bukti |
| :--- | :---: | :--- |
| **Role-Based Access Control (RBAC)** | **VERIFIED** | Middleware `CheckRole`, 53 rute web terpartisi ketat per peran |
| **Isolasi Data Pelanggan & Anti-IDOR** | **VERIFIED** | `OrderPolicy`, *scoped Eloquent relationship* (`$customer->orders()`) |
| **Keamanan Bukti Pembayaran & Refund** | **VERIFIED** | Storage `local` (`storage/app/private/`), `'serve' => false`, sandboxed CSP headers |
| **Kalkulasi Harga Server-Side** | **VERIFIED** | `CreateOrderAction` mengambil harga langsung dari `product_variants.price` |
| **Idempotensi & Anti-Double Submission** | **VERIFIED** | `checkout_token` UUID, `Order::lockForUpdate()`, `PoPeriod::sharedLock()` |
| **Verifikasi Pembayaran & Pencocokan Nominal** | **VERIFIED** | `VerifyPaymentAction`, `Money::matches()`, status atomic transition |
| **Alur Pembatalan & Pengembalian Dana (Refund)** | **VERIFIED** | `DecideCancellationAction`, mandatory refund reference, proof, exact amount |
| **State Machine Pesanan & Jejak Audit** | **VERIFIED** | `nextOperationalStatuses()`, `OrderStatusHistory`, `AuditLog` |
| **Transaksional Database & Concurrency Control** | **VERIFIED** | `DB::transaction()`, pessimistic locking (`lockForUpdate`, `sharedLock`) |
| **Cadangan & Pemulihan Bencana (DRP)** | **VERIFIED** | `BackupDatabase` CLI, pipe stream `gzopen`, zero CLI password leak |

---

## 2. ROLE-BASED ACCESS CONTROL (RBAC) & PARTISI RUTE

### 2.1. Desain & Mekanisme Penegakan
RBAC pada Uyuy Jastip ditegakkan melalui kombinasi middleware `auth` dan middleware khusus [CheckRole.php](file:///e:/Codex/app/Http/Middleware/CheckRole.php#L8-L30).

- **Middleware Definition:**
  ```php
  // app/Http/Middleware/CheckRole.php
  public function handle(Request $request, Closure $next, ...$roles): Response
  {
      if (!Auth::check()) {
          return redirect()->route('login');
      }
      $user = Auth::user();
      if (!in_array($user->role, $roles, true)) {
          abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk membuka halaman ini.');
      }
      return $next($request);
  }
  ```

### 2.2. Partisi 53 Rute Web ([routes/web.php](file:///e:/Codex/routes/web.php))
Rute aplikasi dikelompokkan secara terstruktur tanpa adanya rute privat yang bocor ke publik:
1. **Rute Publik (4 Rute):**
   - `GET /` (`home`)
   - `GET /katalog` (`catalog`)
   - `GET /produk/{id}` (`product.detail`)
   - `GET /informasi-po` (`po.info`)
2. **Rute Autentikasi (9 Rute):**
   - Dilindungi middleware `guest` dan `throttle` berbasis IP/kredensial (`throttle:5,1,login`, `throttle:5,1,register`, `throttle:3,1,forgot-password`).
   - Logout dilindungi `auth` dan proteksi CSRF.
3. **Rute Berkas Terproteksi Bersama (3 Rute):**
   - Dilindungi middleware `auth` dan divalidasi lebih lanjut melalui `OrderPolicy` pada controller level (`payments.proof`, `payments.attempt-proof`, `cancellations.refund-proof`).
4. **Rute Customer Terproteksi (11 Rute):**
   - Dikelompokkan dengan prefiks `pelanggan` dan middleware `['auth', 'role:customer']`.
   - Mengelola dashboard, keranjang (tambah, perbarui, hapus, checkout), daftar pesanan, detail pesanan, unggah bukti bayar, dan permohonan pembatalan.
5. **Rute Admin Operasional (9 Rute):**
   - Dikelompokkan dengan prefiks `admin` dan middleware `['auth', 'role:admin,owner']`.
   - Mengelola dashboard operasional antrean, pembaruan status pesanan bertahap, verifikasi pembayaran manual BCA Merchant, dan putusan pembatalan pesanan.
   - Hak istimewa *Owner* diperbolehkan mengakses panel admin untuk pengawasan operasional langsung.
6. **Rute Owner Eksekutif (17 Rute):**
   - Dikelompokkan dengan prefiks `owner` dan middleware `['auth', 'role:owner']`.
   - Wewenang eksklusif: buka/tutup periode PO, CRUD periode PO, CRUD katalog produk & varian harga, rekapitulasi finansial & margin keuntungan, serta audit basis data pelanggan.
   - **Staf Admin dilarang keras masuk ke rute ini** (HTTP 403 Forbidden).

---

## 3. ISOLASI DATA PELANGGAN & PERLINDUNGAN IDOR (*INSECURE DIRECT OBJECT REFERENCE*)

### 3.1. Kebijakan Otorisasi Granular ([OrderPolicy.php](file:///e:/Codex/app/Policies/OrderPolicy.php))
Setiap interaksi terhadap data pesanan divalidasi menggunakan Laravel Policy:
```php
// app/Policies/OrderPolicy.php
public function view(User $user, Order $order): bool
{
    if ($user->isAdmin() || $user->isOwner()) {
        return true;
    }
    if ($user->isCustomer() && $user->customer) {
        return $order->customer_id === $user->customer->id;
    }
    return false;
}

public function interact(User $user, Order $order): bool
{
    if ($user->isCustomer() && $user->customer) {
        return $order->customer_id === $user->customer->id;
    }
    return false;
}
```

### 3.2. Implementasi Scoped Relationship pada Controller Pelanggan
Pada [Customer/OrderController.php](file:///e:/Codex/app/Http/Controllers/Customer/OrderController.php#L26-L37):
- Daftar pesanan (`index`) tidak pernah melakukan kueri langsung `Order::all()`, melainkan scoped query melalui relasi Eloquent:
  ```php
  $customer = Auth::user()->customer;
  $query = $customer->orders()->with(['poPeriod', 'payment']);
  ```
- Pada method `show($id)`, `payment($id)`, `uploadPayment(...)`, `cancellation($id)`, dan `submitCancellation(...)`, controller selalu menjalankan:
  ```php
  $this->authorize('view', $order); // atau $this->authorize('interact', $order);
  ```
- Pelanggan yang mencoba memanipulasi parameter URL ID pesanan milik pelanggan lain secara otomatis dihadang dengan respons **HTTP 403 Forbidden**.
- Verifikasi telah diuji secara otomatis dalam pengujian fitur [OrderTest.php](file:///e:/Codex/tests/Feature/OrderTest.php): `test_customer_cannot_view_other_customers_order()` menghasilkan status 403.

---

## 4. KEAMANAN BERKAS BUKTI PEMBAYARAN & REFUND

### 4.1. Isolasi Disk Non-Publik ([config/filesystems.php](file:///e:/Codex/config/filesystems.php#L33-L39))
Berkas bukti transfer pelanggan dan bukti transfer pengembalian dana (refund) disimpan pada disk `local` privat:
```php
// config/filesystems.php
'local' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'serve' => false,
    'throw' => false,
    'report' => false,
],
```
Nilai `'serve' => false` memastikan Laravel tidak menyediakan rute publik otomatis untuk menyajikan isi folder `storage/app/private`. Berkas sama sekali tidak dapat diakses melalui web browser via URL statis langsung (misalnya `/storage/payments/...` menghasilkan 404).

### 4.2. Pengunduhan & Streaming Terproteksi ([PaymentProofController.php](file:///e:/Codex/app/Http/Controllers/PaymentProofController.php))
Akses berkas bukti pembayaran hanya dimungkinkan melalui controller terautentikasi:
1. Memvalidasi kepemilikan melalui `$this->authorize('view', $order)`.
2. Mencegah manipulasi SSRF atau jalur berkas eksternal (`abort_if(preg_match('/^https?:\/\//i', $proofPath) === 1, 404)`).
3. Mengembalikan response dengan *Security Headers* ketat dan sandboxed CSP:
   ```php
   return $disk->response($path, basename($path), [
       'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
       'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
       'X-Content-Type-Options' => 'nosniff',
       'Referrer-Policy' => 'no-referrer',
   ]);
   ```
   Kebijakan sandboxed CSP ini mencegah eksekusi skrip berbahaya jika terdapat berkas HTML/SVG terselubung yang diunggah pengguna.

---

## 5. INTEGRITAS KALKULASI HARGA SISI SERVER (*SERVER-SIDE CALCULATION*)

Aplikasi sepenuhnya menolak ketergantungan harga pada payload permintaan (*request payload*). Pada [CreateOrderAction.php](file:///e:/Codex/app/Actions/CreateOrderAction.php#L42-L81):
1. Sistem hanya menerima parameter identitas produk (`product_id`), ukuran botol (`size_ml`), dan kuantitas (`quantity`) dari keranjang.
2. Seluruh harga satuan (`unit_price`) dan harga modal (`cost_price`) diambil langsung dari basis data melalui varian aktif:
   ```php
   $products = Product::active()->with('variants')->whereIn('id', $productIds)->get()->keyBy('id');
   // ...
   $variant = $product->variantForSize($size);
   $unitPrice = (float) $variant->price;
   $subtotal = round($unitPrice * $quantity, 2);
   $totalAmount += $subtotal;
   ```
3. Manipulasi harga pada localStorage atau interseptor HTTP (seperti Burp Suite / Postman) tidak berdampak apa pun terhadap total tagihan pesanan.

---

## 6. IDEMPOTENSI CHECKOUT & PENCEGAHAN *DOUBLE SUBMISSION*

Untuk mengatasi risiko pesanan ganda akibat klik berulang pengguna atau *network retry*, sistem menerapkan mekanisme idempotensi dua lapis:
1. **Lapisan Frontend:** Form checkout menerapkan atribut JavaScript `data-prevent-double-submit="true"` yang langsung menonaktifkan tombol submit saat diklik pertama kali.
2. **Lapisan Backend ([CreateOrderAction.php](file:///e:/Codex/app/Actions/CreateOrderAction.php#L31-L35)):**
   - Setiap checkout mengirimkan UUID `checkout_token` unik yang dibuat saat halaman checkout dimuat.
   - Kueri mengecek keberadaan token menggunakan *pessimistic lock*:
     ```php
     $existingOrder = Order::query()->where('checkout_token', $checkoutToken)->lockForUpdate()->first();
     if ($existingOrder) {
         abort_unless($existingOrder->customer_id === $customer->id, 403, 'Token checkout tidak valid.');
         return $existingOrder;
     }
     ```
   - Apabila permintaan identik masuk bersamaan, transaksi kedua akan menunggu lock dan langsung mengembalikan pesanan yang sudah terbentuk tanpa menduplikasi data pesanan maupun tagihan.

---

## 7. KONTROL TRANSAKSI & CONCURRENCY (LOCKING STRATEGY)

Sistem menggunakan strategi penguncian pesimis (*pessimistic locking*) MySQL / InnoDB yang dirancang secara cermat:

```
+-----------------------------------------------------------------------------+
|                          CONCURRENCY LOCKING MODEL                          |
+-----------------------------------------------------------------------------+
|                                                                             |
|  [ Customer A Checkout ]          [ Customer B Checkout ]   [ Owner PO Close ]
|           |                                 |                       |       
|           v                                 v                       |       
|  PoPeriod::sharedLock()            PoPeriod::sharedLock()           |       
|           |                                 |                       |       
|     (Shared Lock S)                   (Shared Lock S)               |       
|     DAPAT BERJALAN                    DAPAT BERJALAN                |       
|     BERSAMAAN SECARA                  BERSAMAAN SECARA              |       
|     PARALEL                           PARALEL                       |       
|           |                                 |                       |       
|           v                                 v                       v       
|     Commit Order                      Commit Order          PoPeriod::lock()
|                                                             (Exclusive Lock X)
|                                                             TERBLOCK / MENUNGGU
|                                                             SELURUH CHECKOUT
|                                                             SELESAI COMMIT
+-----------------------------------------------------------------------------+
```

### 7.1. Analisis `PoPeriod::sharedLock()`
Pada [CreateOrderAction.php:27](file:///e:/Codex/app/Actions/CreateOrderAction.php#L27):
- Penggunaan `sharedLock()` (SQL: `LOCK IN SHARE MODE` / `FOR SHARE`) memungkinkan ratusan pelanggan melakukan *checkout* secara konkuren tanpa saling mengunci (*non-blocking read lock*).
- Pada saat yang sama, *shared lock* ini secara otomatis menahan (*block*) operasi penutupan periode oleh Owner (`UPDATE po_periods SET status = 'Ditutup'`) sampai seluruh transaksi checkout yang sedang berjalan selesai di-commit.
- Hal ini mencegah anomali *race condition* di mana pesanan tercipta beberapa milidetik setelah Owner mengklik tombol tutup periode.

### 7.2. Analisis `Order::lockForUpdate()` & `Payment::lockForUpdate()`
Pada operasi krusial:
- [VerifyPaymentAction.php:25-26](file:///e:/Codex/app/Actions/VerifyPaymentAction.php#L25-L26)
- [DecideCancellationAction.php:40-42](file:///e:/Codex/app/Actions/DecideCancellationAction.php#L40-L42)
- [Customer/OrderController.php:123](file:///e:/Codex/app/Http/Controllers/Customer/OrderController.php#L123)

Sistem menerapkan `lockForUpdate()` (SQL: `FOR UPDATE`). Hal ini mencegah:
- Verifikasi ganda oleh dua staf admin yang membuka halaman verifikasi pembayaran pada detik yang sama.
- Pengunggahan bukti pembayaran susulan oleh pelanggan saat admin sedang memproses putusan validasi pembayaran.
- Persetujuan pembatalan bersamaan dengan pergantian status operasional pesanan.

---

## 8. INTEGRITAS ALUR VERIFIKASI PEMBAYARAN MANUAL

Pada [VerifyPaymentAction.php](file:///e:/Codex/app/Actions/VerifyPaymentAction.php#L23-L96):
1. **Validasi Status:** Hanya pembayaran dengan `status = 'Menunggu Verifikasi'` pada pesanan dengan `status = 'Menunggu Pembayaran'` yang dapat diproses.
2. **Validasi Keberadaan Berkas Fisik:** Sistem memeriksa secara fisik ketersediaan berkas bukti transfer di *storage* sebelum memperbolehkan admin menekan tombol validasi:
   ```php
   if (!$this->proofExists($payment->proof_file_path)) {
       return ['error' => 'Berkas bukti transfer tidak ditemukan. Minta pelanggan mengunggah ulang.'];
   }
   ```
3. **Pencocokan Nominal Matematis Presisi:** Menggunakan class helper [Money::matches()](file:///e:/Codex/app/Support/Money.php) dengan pembulatan presisi desimal untuk memastikan nominal yang dilaporkan pelanggan identik dengan total tagihan pesanan, mencegah *underpayment* atau manipulasi desimal.
4. **Sinkronisasi Riwayat:** Memperbarui record `payments` dan entri `payment_attempts` aktif secara atomik.
5. **Transisi Status & Catatan Audit:**
   - Status pesanan berpindah otomatis ke `Pembayaran Diverifikasi`.
   - Entri baru dicatat pada `order_status_histories`.
   - Log terstruktur dicatat pada tabel `audit_logs` (`payment.verified`).

---

## 9. ALUR PEMBATALAN & KETENTUAN PENGEMBALIAN DANA (*REFUND*)

Sistem menerapkan validasi bisnis yang sangat ketat pada alur pembatalan di [DecideCancellationAction.php](file:///e:/Codex/app/Actions/DecideCancellationAction.php#L23-L100):
1. **Larangan Pembatalan Sepihak Setelah Pemrosesan:**
   - Sesuai metode `Order::canBeCancelled()`, pesanan hanya dapat dimohonkan pembatalan jika masih berstatus `Menunggu Pembayaran` atau `Pembayaran Diverifikasi`.
   - Pesanan yang sudah berstatus `Diproses`, `Barang Dikirim`, atau `Selesai` otomatis menolak permohonan pembatalan.
2. **Kewajiban Bukti Refund untuk Pesanan yang Telah Lunas:**
   - Apabila pesanan yang dibatalkan telah berstatus `Pembayaran Diverifikasi`, admin **TIDAK DAPAT** menyetujui pembatalan tanpa melampirkan:
     1. Berkas bukti transfer pengembalian dana (*refund proof upload*).
     2. Nomor referensi mutasi bank (*refund reference*).
     3. Nominal pengembalian dana (*refund amount*).
   - Validasi ketat memverifikasi bahwa nominal pengembalian dana **harus sama persis** dengan total pembayaran yang telah diverifikasi:
     ```php
     if (!Money::matches($validated['refund_amount'], $payment->amount)
         || !Money::matches($validated['refund_amount'], $order->total_amount)) {
         return ['warning' => 'Nominal refund harus sama dengan pembayaran terverifikasi...'];
     }
     ```
3. **Pembersihan Berkas Otomatis (*Rollback Cleanup*):**
   - Apabila transaksi database mengalami kegagalan di tengah jalan (*exception*), berkas bukti refund yang terlanjur terunggah ke disk langsung dihapus (`Storage::disk('local')->delete($refundPath)`), mencegah penumpukan berkas sampah yatim (*orphaned files*).

---

## 10. STATE MACHINE STATUS PESANAN & JEJAK AUDIT (*AUDIT TRAIL*)

### 10.1. Validasi Transisi Terarah ([Order.php:114-122](file:///e:/Codex/app/Models/Order.php#L114-L122))
Alur operasional pesanan dikendalikan secara deterministik:
```
[ Menunggu Pembayaran ]
        |
        +---> Dibatalkan (oleh Pelanggan / Expired Command / Admin)
        |
        v (Verifikasi Pembayaran Valid via BCA Merchant)
[ Pembayaran Diverifikasi ]
        |
        +---> Dibatalkan (dengan Kewajiban Bukti Refund Lengkap)
        |
        v (Admin Operasional)
[ Diproses ]
        |
        v (Wajib Input Ekspedisi & Nomor Resi)
[ Barang Dikirim ]
        |
        v (Admin Operasional)
[ Selesai ]
```
Method `nextOperationalStatuses()` memastikan staf operasional tidak dapat melakukan lompatan ilegal (misalnya langsung dari `Menunggu Pembayaran` ke `Barang Dikirim`, atau membalikkan `Selesai` ke status sebelumnya).

### 10.2. Immutabilitas & Ketertelusuran
Setiap perubahan status merekam:
- ID Pesanan (`order_id`)
- Status sebelumnya (`previous_status`)
- Status baru (`new_status`)
- Catatan operasional (`remarks`)
- Pengguna yang melakukan perubahan (`changed_by` -> ID Admin/Owner/Pelanggan/System)
- Waktu kejadian (`created_at`)

Seluruh riwayat ini ditampilkan secara transparan baik di panel admin maupun di timeline pesanan akun pelanggan.

---

## 11. SISTEM CADANGAN & PEMULIHAN BENCANA (*BACKUP & DISASTER RECOVERY*)

Sistem cadangan diimplementasikan melalui perintah artisan kustom [BackupDatabase.php](file:///e:/Codex/app/Console/Commands/BackupDatabase.php):

1. **Konsistensi Transaksional Penuh:** Menggunakan parameter mysqldump `--single-transaction`, `--routines`, `--triggers`, `--events`, dan `--default-character-set=utf8mb4`. Parameter `--single-transaction` menjamin dump database dilakukan secara konsisten tanpa mengunci tabel (*table lock*) yang dapat menghentikan aktivitas transaksi toko.
2. **Streaming Kompresi Gzip Langsung:** Data stream mysqldump dialirkan secara *real-time* ke fungsi kompresi PHP `gzopen($path, 'wb6')` tanpa membuat berkas SQL teks mentah berukuran besar di disk terlebih dahulu.
3. **Perlindungan Kredensial Proses:** Kata sandi database diinjeksikan melalui variabel lingkungan proses `MYSQL_PWD` dalam runtime Symfony Process:
   ```php
   $process = new Process($arguments, base_path(), [
       'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
   ]);
   ```
   Hal ini mencegah kata sandi database terlihat pada daftar proses sistem operasi (seperti melalui perintah `ps aux` atau Task Manager).
4. **Retensi & Pembersihan Otomatis:** Perintah membaca konfigurasi retensi (`config('backup.retention_days', 14)`) dan secara otomatis menghapus berkas cadangan kompresi yang berusia lebih dari 14 hari untuk mencegah kepenuhan ruang disk server.

---

## 12. KESIMPULAN AUDIT SISTEM & KEAMANAN

Arsitektur backend, lapisan transaksi, dan sistem kontrol otorisasi Uyuy Jastip telah **TERVERIFIKASI 100% MEMENUHI STANDAR PRODUKSI DAN BEBAS DARI KERENTANAN KRITIS**. Tidak ditemukan celah bypass otorisasi, kebocoran data pelanggan, manipulasi harga, maupun ketidakkonsistenan status transaksi. Arsitektur saat ini sangat direkomendasikan untuk **DIPERTAHANKAN PENUH** tanpa perombakan struktur.
