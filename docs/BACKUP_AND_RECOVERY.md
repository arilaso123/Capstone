# Backup dan pemulihan database

Perintah `php artisan database:backup` membuat dump MySQL/MariaDB terkompresi di `storage/app/private/backups`. Scheduler menjalankannya setiap hari pukul 02.00 WIB dan menghapus arsip lokal yang lebih lama dari masa retensi yang dikonfigurasi. Arsip berisi data pelanggan dan transaksi; batasi akses direktori, salin ke media terenkripsi di luar server secara berkala, dan jangan letakkan di `public/`.

## Mengaktifkan scheduler

- Linux: daftarkan cron `* * * * * cd /path/aplikasi && php artisan schedule:run >> /dev/null 2>&1`.
- Windows/XAMPP: buat tugas Task Scheduler yang berjalan setiap menit dan menjalankan `php artisan schedule:run` dengan `E:\Codex` sebagai working directory.
- Pastikan `MYSQLDUMP_PATH`, `DB_*`, dan `DATABASE_BACKUP_DIRECTORY` sesuai mesin. Password database diberikan ke proses dump melalui environment proses dan tidak ditulis ke file backup atau log aplikasi.

## Restore dan latihan pemulihan

1. Hentikan aplikasi yang menulis transaksi dan salin arsip backup ke direktori kerja privat.
2. Dekompresi ke SQL melalui `gzip -dc backup.sql.gz` (Linux/macOS) atau 7-Zip pada Windows. Jangan restore langsung ke database produksi.
3. Buat database uji kosong, lalu impor hasil dekompresi menggunakan klien `mysql` dengan kredensial dari secret manager atau environment yang terlindungi.
4. Jalankan `php artisan migrate:status`, cek jumlah tabel dan beberapa alur baca transaksi, lalu catat waktu backup, waktu pemulihan, dan hasil pemeriksaan.
5. Lakukan latihan restore per kuartal dan setelah perubahan infrastruktur besar. Salinan lokal harian sendiri bukan disaster recovery; simpan salinan terpisah dari host aplikasi.
