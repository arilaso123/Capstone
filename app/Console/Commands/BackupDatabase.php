<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'database:backup';

    protected $description = 'Create a compressed, private MySQL/MariaDB logical backup';

    public function handle(): int
    {
        $connection = config('database.connections.mysql');
        if (!$connection || empty($connection['database'])) {
            $this->error('Konfigurasi koneksi MySQL tidak lengkap.');
            return self::FAILURE;
        }

        $directory = config('backup.directory');
        File::ensureDirectoryExists($directory);
        $path = $directory . DIRECTORY_SEPARATOR . 'uyuy-' . now()->format('Ymd-His') . '.sql.gz';
        $archive = gzopen($path, 'wb6');
        if ($archive === false) {
            $this->error('File backup tidak dapat dibuat. Periksa hak akses direktori backup.');
            return self::FAILURE;
        }

        $arguments = [
            config('backup.mysqldump'),
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? '3306'),
            '--user=' . ($connection['username'] ?? ''),
            '--single-transaction', '--routines', '--triggers', '--events',
            '--default-character-set=utf8mb4', '--databases', $connection['database'],
        ];
        $process = new Process($arguments, base_path(), [
            'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
        ]);
        $process->setTimeout(3600);
        $stderr = '';

        try {
            $process->run(function (string $type, string $buffer) use ($archive, &$stderr): void {
                if ($type === Process::OUT) {
                    gzwrite($archive, $buffer);
                } else {
                    $stderr .= $buffer;
                }
            });
        } catch (Throwable $exception) {
            gzclose($archive);
            File::delete($path);
            $this->error('Backup gagal dijalankan: ' . $exception->getMessage());
            return self::FAILURE;
        }

        gzclose($archive);
        if (!$process->isSuccessful() || !File::exists($path) || File::size($path) === 0) {
            File::delete($path);
            $this->error('Backup database gagal: ' . trim(substr($stderr ?: $process->getErrorOutput(), 0, 500)));
            return self::FAILURE;
        }

        $retentionDays = max(1, (int) config('backup.retention_days', 14));
        foreach (File::files($directory) as $oldBackup) {
            if ($oldBackup->getExtension() === 'gz'
                && $oldBackup->getMTime() < now()->subDays($retentionDays)->getTimestamp()) {
                File::delete($oldBackup->getPathname());
            }
        }

        $this->info('Backup berhasil dibuat: ' . $path . ' (' . number_format(File::size($path)) . ' bytes).');
        return self::SUCCESS;
    }
}
