<?php

namespace App\Console\Commands;

use App\Jobs\PrintReceipt;
use App\Models\Item;
use App\Models\PrintFile;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SimulateAllFeatures extends Command
{
    protected $signature = 'simulate:all-features {--printer=POS-1} {--copies=2} {--auto : Enable auto receipt printing}';

    protected $description = 'Simulate store settings, sale creation, receipt printing, reprint, admin filtering, and legacy cleanup.';

    public function handle(): int
    {
        $this->info('Starting full feature simulation...');

        $storeSetting = StoreSetting::current();
        $storeSetting->update([
            'default_printer' => $this->option('printer'),
            'auto_print_receipt' => $this->option('auto'),
            'receipt_copies' => max(1, (int) $this->option('copies')),
        ]);

        $this->line('Store settings:');
        $this->line('  default_printer = '.($storeSetting->default_printer ?? '-'));
        $this->line('  auto_print_receipt = '.($storeSetting->auto_print_receipt ? 'true' : 'false'));
        $this->line('  receipt_copies = '.$storeSetting->receipt_copies);

        $this->createLegacyReceiptFile();
        $this->cleanupLegacyReceiptFiles();

        $item = Item::where('current_stock', '>', 0)->first();
        if (! $item) {
            $this->error('No items with positive stock found. Please create an item or restock an existing item.');

            return 1;
        }

        $sale = DB::transaction(function () use ($item) {
            $sale = Sale::create([
                'invoice_number' => 'SIM-'.now()->format('YmdHis'),
                'user_id' => Auth::id() ?? 1,
                'subtotal' => $item->selling_price,
                'discount' => 0,
                'total' => $item->selling_price,
                'payment_method' => 'cash',
                'paid_amount' => $item->selling_price,
                'change_amount' => 0,
                'notes' => 'Simulated sale for feature test',
            ]);

            $sale->items()->create([
                'item_id' => $item->id,
                'item_name' => $item->name,
                'item_sku' => $item->sku,
                'price' => $item->selling_price,
                'quantity' => 1,
                'subtotal' => $item->selling_price,
            ]);

            $item->decrement('current_stock', 1);

            StockMovement::create([
                'item_id' => $item->id,
                'type' => 'out',
                'quantity' => 1,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'notes' => 'Simulated sale '.$sale->invoice_number,
                'user_id' => Auth::id() ?? 1,
            ]);

            return $sale;
        });

        $this->info('Created simulated sale: ID '.$sale->id.' Invoice '.$sale->invoice_number);

        if ($storeSetting->auto_print_receipt) {
            $this->dispatchPrintReceipt($sale, 'auto_print_receipt');
        } else {
            $this->info('Auto print is disabled. Use --auto to enable receipt dispatch during simulation.');
        }

        $this->displayPrintFileSummary($sale);
        $this->displayAdminFilters($sale, $storeSetting);

        $this->info('Simulating reprint for sale ID '.$sale->id);
        $this->dispatchPrintReceipt($sale, 'reprint');

        $this->displayPrintFileSummary($sale);

        $this->info('Full feature simulation completed.');

        return 0;
    }

    protected function createLegacyReceiptFile(): void
    {
        $directory = storage_path('prints');
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $legacyPath = $directory.DIRECTORY_SEPARATOR.'receipt_999_legacy.txt';
        File::put($legacyPath, "Legacy receipt file for cleanup simulation.\n");
        $this->line('Created legacy file: '.$legacyPath);
    }

    protected function cleanupLegacyReceiptFiles(): void
    {
        $directory = storage_path('prints');
        if (! File::exists($directory)) {
            return;
        }

        $deleted = 0;
        foreach (File::files($directory) as $file) {
            $name = $file->getFilename();
            if (preg_match('/^receipt_\d+_.*\.txt$/', $name) && ! preg_match('/^receipt_\d+\.txt$/', $name)) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        $this->line('Legacy cleanup: removed '.$deleted.' legacy receipt file(s).');
    }

    protected function dispatchPrintReceipt(Sale $sale, string $context): void
    {
        try {
            if (method_exists(PrintReceipt::class, 'dispatchSync')) {
                PrintReceipt::dispatchSync($sale, true);
            } else {
                PrintReceipt::dispatch($sale, true)->onConnection('sync');
            }
            $this->info('PrintReceipt dispatched and processed ('.$context.').');
        } catch (\Throwable $e) {
            $this->error('Failed to run PrintReceipt synchronously: '.$e->getMessage());
        }
    }

    protected function displayPrintFileSummary(Sale $sale): void
    {
        $printFile = PrintFile::where('sale_id', $sale->id)->first();
        if (! $printFile) {
            $this->warn('No PrintFile record found for sale ID '.$sale->id);

            return;
        }

        $this->line('PrintFile summary:');
        $this->line('  filename = '.$printFile->filename);
        $this->line('  printer_name = '.($printFile->printer_name ?? '-'));
        $this->line('  print_count = '.$printFile->print_count);
        $this->line('  last_printed_at = '.$printFile->last_printed_at?->format('Y-m-d H:i:s'));
        $this->line('  path = '.storage_path('prints/'.$printFile->filename));
    }

    protected function displayAdminFilters(Sale $sale, StoreSetting $storeSetting): void
    {
        $this->line('Admin filter simulation:');

        $bySale = PrintFile::where('sale_id', $sale->id)->get();
        $this->line('  sale_id filter matches: '.$bySale->count());

        $byPrinter = PrintFile::where('printer_name', 'like', '%'.($storeSetting->default_printer ?? '').'%')->get();
        $this->line('  printer_name filter matches: '.$byPrinter->count());
    }
}
