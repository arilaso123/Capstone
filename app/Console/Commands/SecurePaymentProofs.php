<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class SecurePaymentProofs extends Command
{
    protected $signature = 'payments:secure-proofs {--apply : Copy legacy proofs to private storage and remove public copies}';

    protected $description = 'Migrate existing public payment proofs into private local storage';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');
        $migrated = 0;
        $alreadySecured = 0;
        $pending = 0;
        $skipped = 0;
        $failed = 0;

        Payment::query()
            ->whereNotNull('proof_file_path')
            ->orderBy('id')
            ->chunkById(100, function ($payments) use ($apply, $publicDisk, $privateDisk, &$migrated, &$alreadySecured, &$pending, &$skipped, &$failed) {
                foreach ($payments as $payment) {
                    $path = $payment->proof_file_path;

                    if (!$path || str_starts_with($path, 'http')) {
                        $skipped++;
                        $this->warn("Skipped payment {$payment->id}: external proof path requires manual review.");
                        continue;
                    }

                    $privateExists = $privateDisk->exists($path);
                    $publicExists = $publicDisk->exists($path);

                    if ($privateExists) {
                        if ($publicExists && !$this->filesMatch($publicDisk, $privateDisk, $path)) {
                            $failed++;
                            $this->error("Public and private proof files differ for payment {$payment->id}; neither copy was removed.");
                            continue;
                        }

                        if (!$publicExists) {
                            $alreadySecured++;
                            continue;
                        }

                        if (!$apply) {
                            $pending++;
                            continue;
                        }

                        if (!$publicDisk->delete($path)) {
                            $failed++;
                            $this->error("Private copy exists but public copy could not be removed for payment {$payment->id}.");
                            continue;
                        }

                        $migrated++;
                        continue;
                    }

                    if (!$publicExists) {
                        $skipped++;
                        $this->warn("Skipped payment {$payment->id}: proof file was not found on either disk.");
                        continue;
                    }

                    if (!$apply) {
                        $pending++;
                        continue;
                    }

                    $stream = $publicDisk->readStream($path);
                    if (!is_resource($stream)) {
                        $failed++;
                        $this->error("Could not read public proof for payment {$payment->id}.");
                        continue;
                    }

                    try {
                        $stored = $privateDisk->put($path, $stream);
                    } finally {
                        fclose($stream);
                    }

                    if (!$stored || !$privateDisk->exists($path) || !$this->filesMatch($publicDisk, $privateDisk, $path)) {
                        $failed++;
                        $this->error("Private copy is missing or differs for payment {$payment->id}; public original was kept.");
                        continue;
                    }

                    if (!$publicDisk->delete($path)) {
                        $failed++;
                        $this->error("Private copy created, but public copy remains for payment {$payment->id}.");
                        continue;
                    }

                    $migrated++;
                    $this->line("Secured payment proof {$payment->id}.");
                }
            });

        $this->info(($apply ? 'Migration' : 'Dry run') . " complete. Migrated: {$migrated}; already secured: {$alreadySecured}; pending: {$pending}; skipped: {$skipped}; failed: {$failed}.");

        if (!$apply && $pending > 0) {
            $this->comment('Review the counts, then rerun with --apply to migrate and remove the public copies.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function filesMatch(FilesystemAdapter $sourceDisk, FilesystemAdapter $targetDisk, string $path): bool
    {
        $source = $sourceDisk->readStream($path);
        $target = $targetDisk->readStream($path);

        if (!is_resource($source) || !is_resource($target)) {
            if (is_resource($source)) {
                fclose($source);
            }
            if (is_resource($target)) {
                fclose($target);
            }

            return false;
        }

        try {
            $sourceHash = hash_init('sha256');
            $targetHash = hash_init('sha256');

            return hash_update_stream($sourceHash, $source) !== false
                && hash_update_stream($targetHash, $target) !== false
                && hash_equals(hash_final($sourceHash), hash_final($targetHash));
        } finally {
            fclose($source);
            fclose($target);
        }
    }
}
