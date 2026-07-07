<?php

namespace App\Console\Commands;

use App\Jobs\PrintReceipt;
use App\Models\Item;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimulateSalePrint extends Command
{
    protected $signature = 'simulate:sale-print {--force : Force dispatch print even if auto_print_receipt is false}';

    protected $description = 'Simulate a sale using first available item and dispatch PrintReceipt job for testing.';

    public function handle(): int
    {
        $item = Item::where('current_stock', '>', 0)->first();
        if (! $item) {
            $this->error('No items with positive stock found. Please replenish stock or create an item.');

            return 1;
        }

        try {
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
                    'notes' => 'Simulated sale for printing test',
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
        } catch (\Throwable $e) {
            $this->error('Failed to create simulated sale: '.$e->getMessage());

            return 1;
        }

        $this->info('Simulated sale created: ID '.$sale->id.' Invoice '.$sale->invoice_number);

        $force = $this->option('force');

        if ($force) {
            try {
                if (method_exists(PrintReceipt::class, 'dispatchSync')) {
                    PrintReceipt::dispatchSync($sale, true);
                } else {
                    PrintReceipt::dispatch($sale, true)->onConnection('sync');
                }
                $this->info('PrintReceipt job dispatched and processed (forced, sync).');
            } catch (\Throwable $e) {
                $this->error('Failed to run PrintReceipt synchronously: '.$e->getMessage());
            }

            return 0;
        }

        // Respect store setting auto_print_receipt if available
        try {
            $store = StoreSetting::current();
            if ($store->auto_print_receipt) {
                try {
                    if (method_exists(PrintReceipt::class, 'dispatchSync')) {
                        PrintReceipt::dispatchSync($sale);
                    } else {
                        PrintReceipt::dispatch($sale)->onConnection('sync');
                    }
                    $this->info('PrintReceipt job dispatched and processed (auto_print_receipt enabled, sync).');
                } catch (\Throwable $e) {
                    $this->error('Failed to run PrintReceipt synchronously: '.$e->getMessage());
                }
            } else {
                $this->info('auto_print_receipt is disabled; use --force to dispatch the print job.');
            }
        } catch (\Throwable $e) {
            $this->error('Error checking store setting: '.$e->getMessage());
        }

        return 0;
    }
}
