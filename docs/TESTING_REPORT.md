# Laporan Pengujian dan Verifikasi
## Sistem Informasi Pengelolaan Pre-Order Parfum — Uyuy Jastip

Pembaruan: **10 November 2026**. Dokumen ini menggantikan angka 52 test pada laporan lama: hasil tersebut tidak dipakai sebagai bukti suite saat ini karena skrip Python-nya sudah dipensiunkan.

## Ringkasan Hasil

| Pemeriksaan | Hasil |
|---|---|
| Feature Test prioritas | **16 lulus, 96 assertion, 0 gagal** |
| PHP lint | **83 file PHP lulus** |
| Vite production build | **Lulus**, Vite 6.4.5, 61 modul ditransformasi |
| Blade view cache | **Lulus** |
| Route list/action | **54 route**, tidak ada controller action hilang; mencakup password reset, refund proof, update cart, update PO, dan update shipping |
| Git diff check | **Lulus**; Git menampilkan peringatan line ending CRLF/LF pada `composer.lock` |
| Migrasi MySQL lokal | **Lulus**, seluruh migrasi berstatus `Ran` setelah backup privat |

## Cakupan Feature Test

Suite `tests/Feature/BusinessFlowHardeningTest.php` menguji checkout idempotent, harga varian dan snapshot modal, pembatasan tanggal PO, rejection approval saat bukti pending, riwayat re-upload, blokir upload sesudah deadline, rekap Owner, expiry order, refund wajib, password reset, rate limit dan security headers, isolasi order customer, field pengiriman wajib, filter ukuran/koreksi jumlah keranjang, penambahan varian ke keranjang, serta edit periode PO.

Pengujian menggunakan PHP 8.2.12, PHPUnit 11.5.57, dan SQLite in-memory. Karena host Windows dalam sandbox melaporkan `is_readable=false` pada path workspace untuk bootstrap standar PHPUnit, suite dijalankan dengan bootstrap sementara di `%TEMP%` yang memuat `E:/Codex/vendor/autoload.php`; file sementara tersebut bukan bagian repository.

Ringkasan pemanggilan yang berhasil:

```powershell
$bootstrapPath = Join-Path $env:TEMP 'codex-phpunit-bootstrap.php'
@'
<?php
require 'E:/Codex/vendor/autoload.php';
'@ | Set-Content -LiteralPath $bootstrapPath -Encoding ascii
$env:APP_ENV='testing'
$env:DB_CONNECTION='sqlite'
$env:DB_DATABASE=':memory:'
$env:CACHE_STORE='array'
$env:SESSION_DRIVER='array'
$env:MAIL_MAILER='array'
php vendor/bin/phpunit --no-configuration --bootstrap $bootstrapPath tests/Feature/BusinessFlowHardeningTest.php
```

## Migrasi dan Pemeriksaan Data MySQL

Backup dibuat sebelum migration: `storage/app/private/backups/uyuy-20261110-063239.sql.gz` (7.947 byte); gzip berhasil didekompresi penuh menjadi 40.208 byte dengan 18 statement `CREATE TABLE` dan marker dump selesai. Setelah migration, pemeriksaan agregat menunjukkan 15 produk / 15 varian, 0 produk tanpa varian, 0 order menunggu bayar tanpa tenggat, 7 payment dengan bukti / 7 attempt, dan 0 bukti terbaru tanpa attempt. Satu order legacy tidak mempunyai baris status history; riwayat yang tidak diketahui tidak dibuat secara sintetis.

## Yang Belum Diuji

- Uji integrasi seluruh Feature Test terhadap MySQL/MariaDB.
- Request checkout paralel, balapan verifikasi dengan pembatalan, dan uji beban.
- Browser smoke/UAT, aksesibilitas, serta layout responsif pada berbagai layar.
- Restore backup ke database uji kosong dan validasi disaster recovery.
- Penetration test, pemeriksaan advisory dependency online, dan konfigurasi server production.

Lihat [IMPLEMENTATION_PROGRESS.md](IMPLEMENTATION_PROGRESS.md) untuk daftar pekerjaan pra-rilis dan [COMPREHENSIVE_AUDIT_REPORT.md](COMPREHENSIVE_AUDIT_REPORT.md) untuk status temuan audit.
