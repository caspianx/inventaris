<?php

namespace App\Jobs;

use App\Models\PrintFile;
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

    protected object $sale;

    protected bool $simulate;

    protected bool $printToDevice;

    public function __construct(object $sale, bool $simulate = false, bool $printToDevice = false)
    {
        $this->sale = $sale;
        $this->simulate = $simulate;
        $this->printToDevice = $printToDevice;
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

        $saleId = $this->sale->id ?? 'simulated';
        $txtFilename = 'receipt_'.$saleId.'.txt';
        $htmlFilename = 'receipt_'.$saleId.'.html';

        file_put_contents($dir.DIRECTORY_SEPARATOR.$txtFilename, $textContent);
        file_put_contents($dir.DIRECTORY_SEPARATOR.$htmlFilename, $htmlContent);

        $printedToDevice = false;
        if ($this->printToDevice && ! empty($printer)) {
            $printedToDevice = self::sendToPrinter($dir.DIRECTORY_SEPARATOR.$txtFilename, $printer, $copies);
            if (! $printedToDevice) {
                throw new \RuntimeException('Printing to printer "'.$printer.'" failed.');
            }
        }

        if (! $this->simulate) {
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
        } else {
            Log::info(sprintf(
                'PrintReceipt simulation: wrote receipts to %s and %s (simulated, printer=%s, copies=%d)',
                $txtFilename,
                $htmlFilename,
                $printer ?? 'none',
                $copies
            ));
        }
    }

    protected function buildTextReceipt(StoreSetting $store, string $receiptSize): string
    {
        // Determine max characters per line based on receipt size
        $maxCharsPerLine = match($receiptSize) {
            '58MM' => 32,
            '80MM' => 40,
            'ROLL' => 50,
            default => 40,
        };

        $content = "--- Struk Belanja ({$receiptSize}) ---\n";
        if ($this->simulate) {
            $content .= "*** SIMULASI ***\n";
        }

        if ($store->show_receipt_logo && $store->logo_path) {
            $content .= '[Logo: '.basename($store->logo_path)."]\n";
        }

        $content .= $this->wrapText($store->name, $maxCharsPerLine)."\n";
        if ($store->address) {
            $content .= $this->wrapText($store->address, $maxCharsPerLine)."\n";
        }
        $content .= 'Tanggal: '.$this->sale->created_at->format('Y-m-d H:i:s')."\n";
        $content .= 'No. Nota: '.($this->sale->invoice_number ?? $this->sale->id)."\n";
        if ($this->simulate) {
            $content .= "Keterangan: Cetak simulasi\n";
        }
        $content .= str_repeat('-', min($maxCharsPerLine, 35))."\n";

        foreach ($this->sale->items as $line) {
            $name = $line->item_name ?? ($line->item?->name ?? '');
            $qty = $line->quantity;
            $price = $line->price;
            $priceStr = number_format($price, 0, ',', '.');
            
            // Format: "Name   x Qty   Price"
            $qtyStr = "x{$qty}";
            $nameMaxLen = $maxCharsPerLine - 8 - strlen($qtyStr) - strlen($priceStr);
            $nameTrimmed = substr($name, 0, max(15, $nameMaxLen));
            
            $line = str_pad($nameTrimmed, $nameMaxLen) . str_pad($qtyStr, 5, ' ', STR_PAD_LEFT) . ' ' . str_pad($priceStr, 10, ' ', STR_PAD_LEFT);
            $content .= substr($line, 0, $maxCharsPerLine)."\n";
        }

        $content .= str_repeat('-', min($maxCharsPerLine, 35))."\n";
        $totalStr = number_format($this->sale->total, 0, ',', '.');
        $totalLine = 'Total: '.$totalStr;
        $content .= $totalLine."\n";
        $content .= str_repeat('-', min($maxCharsPerLine, 35))."\n";
        $content .= $this->wrapText($store->receipt_thank_you_text ?? 'Terima kasih', $maxCharsPerLine)."\n";

        return $content;
    }

    protected function wrapText(string $text, int $maxWidth): string
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if (strlen($currentLine) + strlen($word) + 1 <= $maxWidth) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                if ($currentLine) {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        return implode("\n", $lines);
    }

    protected function buildHtmlReceipt(StoreSetting $store): string
    {
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
            'storeSetting' => $store,
            'logoDataUri' => $logoDataUri,
        ])->render();
    }

    public static function sendToPrinter(string $filename, string $printer, int $copies): bool
    {
        $printed = false;

        if (PHP_OS_FAMILY === 'Windows') {
            $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
            $printer = trim($printer);
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
