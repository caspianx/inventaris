<?php

namespace App\Jobs;

use App\Models\PrintFile;
use App\Models\Sale;
use App\Models\StoreSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PrintReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    protected Sale $sale;

    protected bool $simulate;

    public function __construct(Sale $sale, bool $simulate = false)
    {
        $this->sale = $sale;
        $this->simulate = $simulate;
    }

    public function handle(): void
    {
        $store = StoreSetting::current();

        $printer = $store->default_printer;
        $copies = $store->receipt_copies ?? 1;

        $receiptSize = strtoupper($store->receipt_size ?? '80mm');
        $textContent = $this->buildTextReceipt($store, $receiptSize);
        $htmlContent = $this->buildHtmlReceipt($store);

        $dir = storage_path('prints');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $txtFilename = 'receipt_'.$this->sale->id.'.txt';
        $htmlFilename = 'receipt_'.$this->sale->id.'.html';

        file_put_contents($dir.DIRECTORY_SEPARATOR.$txtFilename, $textContent);
        file_put_contents($dir.DIRECTORY_SEPARATOR.$htmlFilename, $htmlContent);

        $printedToDevice = false;
        if (! $this->simulate && ! empty($printer)) {
            $printedToDevice = $this->sendToPrinter($dir.DIRECTORY_SEPARATOR.$txtFilename, $printer, $copies);
        }

        $printFile = PrintFile::firstOrNew(['sale_id' => $this->sale->id]);
        $printFile->filename = $htmlFilename;
        $printFile->printer_name = $printer;
        $printFile->last_printed_at = now();
        $printFile->print_count = $printFile->exists ? $printFile->print_count + 1 : 1;
        $printFile->save();

        Log::info(sprintf(
            'PrintReceipt: wrote receipts to %s and %s (printer=%s, copies=%d, printed_to_device=%s, count=%d)',
            $txtFilename,
            $htmlFilename,
            $printer ?? 'none',
            $copies,
            $printedToDevice ? 'yes' : 'no',
            $printFile->print_count
        ));
    }

    protected function buildTextReceipt(StoreSetting $store, string $receiptSize): string
    {
        $content = "--- Struk Belanja ({$receiptSize}) ---\n";
        if ($this->simulate) {
            $content .= "*** SIMULASI ***\n";
        }

        if ($store->show_receipt_logo && $store->logo_path) {
            $content .= '[Logo: '.basename($store->logo_path)."]\n";
        }

        $content .= $store->name."\n";
        $content .= ($store->address ? $store->address."\n" : '');
        $content .= 'Tanggal: '.$this->sale->created_at->format('Y-m-d H:i:s')."\n";
        $content .= 'No. Nota: '.$this->sale->id."\n";
        if ($this->simulate) {
            $content .= "Keterangan: Cetak simulasi\n";
        }
        $content .= "---------------------\n";
        foreach ($this->sale->items as $line) {
            $name = $line->item_name ?? ($line->item?->name ?? '');
            $qty = $line->quantity;
            $price = $line->price;
            $content .= $name.' x'.$qty.'  '.number_format($price, 0, ',', '.')."\n";
        }
        $content .= "---------------------\n";
        $content .= 'Total: '.number_format($this->sale->total, 0, ',', '.')."\n";
        $content .= "---------------------\n";
        $content .= "Terima kasih\n";

        return $content;
    }

    protected function buildHtmlReceipt(StoreSetting $store): string
    {
        $this->sale->loadMissing(['items', 'user']);

        $logoDataUri = null;
        if ($store->show_receipt_logo && $store->logo_path) {
            $logoFile = public_path($store->logo_path);
            if (file_exists($logoFile)) {
                $mimeType = mime_content_type($logoFile) ?: 'image/png';
                $data = base64_encode(file_get_contents($logoFile));
                $logoDataUri = "data:$mimeType;base64,$data";
            }
        }

        return view('sales.receipt', [
            'sale' => $this->sale,
            'logoDataUri' => $logoDataUri,
        ])->render();
    }

    protected function sendToPrinter(string $filename, string $printer, int $copies): bool
    {
        $printed = false;

        if (PHP_OS_FAMILY === 'Windows') {
            $escapedFile = str_replace("'", "''", $filename);
            $escapedPrinter = str_replace("'", "''", $printer);
            $powershell = "for (\$i = 1; \$i -le $copies; \$i++) { Get-Content -Path '$escapedFile' | Out-Printer -Name '$escapedPrinter' }";
            $command = 'powershell.exe -NoProfile -Command '.escapeshellarg($powershell);

            exec($command, $output, $exitCode);
            if ($exitCode === 0) {
                $printed = true;
            } else {
                Log::error('PrintReceipt: direct print to Windows printer failed', [
                    'command' => $command,
                    'output' => $output,
                    'exitCode' => $exitCode,
                ]);
            }
        } else {
            $escapedFile = escapeshellarg($filename);
            $escapedPrinter = escapeshellarg($printer);
            $command = "lpr -P $escapedPrinter -# $copies $escapedFile";

            exec($command, $output, $exitCode);
            if ($exitCode === 0) {
                $printed = true;
            } else {
                Log::error('PrintReceipt: direct print via lpr failed', [
                    'command' => $command,
                    'output' => $output,
                    'exitCode' => $exitCode,
                ]);
            }
        }

        return $printed;
    }
}
