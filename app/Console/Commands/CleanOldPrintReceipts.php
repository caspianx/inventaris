<?php

namespace App\Console\Commands;

use App\Models\PrintFile;
use App\Models\StoreSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanOldPrintReceipts extends Command
{
    protected $signature = 'receipts:cleanup {--days=30 : Jumlah hari setelah file struk dihapus}';

    protected $description = 'Menghapus file struk dan arsip cetak yang lebih lama dari jumlah hari tertentu';

    public function handle(): int
    {
        $storeSetting = StoreSetting::current();
        $days = (int) $this->option('days');
        $days = $days > 0 ? $days : (int) ($storeSetting->receipt_retention_days ?? 30);
        $cutoff = now()->subDays($days);
        $deletedCount = 0;

        $printFiles = PrintFile::where('last_printed_at', '<=', $cutoff)->get();

        foreach ($printFiles as $printFile) {
            $this->deleteReceiptFiles($printFile->filename);
            $printFile->delete();
            $deletedCount++;
        }

        $directory = storage_path('prints');
        if (File::exists($directory)) {
            foreach (File::files($directory) as $file) {
                if ($file->getMTime() < $cutoff->timestamp) {
                    File::delete($file->getPathname());
                    $deletedCount++;
                }
            }
        }

        Log::info('Receipts cleanup completed', ['deleted_count' => $deletedCount, 'days' => $days]);
        $this->info('Berhasil menghapus '.$deletedCount.' file struk lama.');

        return Command::SUCCESS;
    }

    protected function deleteReceiptFiles(string $filename): void
    {
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $directory = storage_path('prints');
        $variants = [
            $baseName.'.html',
            $baseName.'.txt',
        ];

        foreach ($variants as $variant) {
            $path = $directory.DIRECTORY_SEPARATOR.$variant;
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }
}
