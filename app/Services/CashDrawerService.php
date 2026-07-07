<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StoreSetting;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CashDrawerService
{
    /**
     * Open the cash drawer for a completed sale.
     * This method supports multiple drivers; default is `network` or `printer`.
     */
    public function open(object $sale): array
    {
        try {
            $store = StoreSetting::current();

            // Per-store/installation driver selection
            $driver = $store->cash_drawer_driver ?? 'network';
            $address = $store->cash_drawer_address ?? null; // e.g. http://192.168.1.100/open or printer path

            if (! $address) {
                Log::warning('Cash drawer open skipped: no address configured.');
                return ['success' => false, 'error' => 'No address configured'];
            }

            if ($driver === 'network') {
                $client = new Client(['timeout' => 3]);
                // Send a simple POST payload with sale info; device should accept it.
                $response = $client->post($address, [
                    'json' => [
                        'sale_id' => $sale->id ?? null,
                        'invoice' => $sale->invoice_number ?? null,
                        'total' => $sale->total ?? null,
                    ],
                ]);

                $status = $response->getStatusCode();
                $body = (string) $response->getBody();
                $success = $status >= 200 && $status < 300;

                $this->writeLogFile($sale, $success, $driver, $address, $status, $body);

                return ['success' => $success, 'status' => $status, 'body' => $body];
            }

            if ($driver === 'printer') {
                // Some cash drawers open by sending pulse (ESC/POS) to the receipt printer.
                // $address expected to be a path to the printer device or share.
                $pulse = "\x1B\x70\x00\x19\xFA"; // common ESC/POS pulse sequence

                try {
                    // @phpstan-ignore-next-line file_put_contents may not be allowed in some environments
                    file_put_contents($address, $pulse);
                    $this->writeLogFile($sale, true, $driver, $address, null, 'Wrote ESC/POS pulse to printer path');
                    return ['success' => true, 'status' => null, 'body' => 'Wrote ESC/POS pulse to printer path'];
                } catch (\Throwable $e) {
                    Log::error('Failed to write to printer path for cash drawer: '.$e->getMessage());
                    $this->writeLogFile($sale, false, $driver, $address, null, $e->getMessage());
                    return ['success' => false, 'error' => $e->getMessage()];
                }
            }

            Log::warning('Unknown cash drawer driver: '.$driver);
            return ['success' => false, 'error' => 'Unknown driver: '.$driver];
        } catch (\Throwable $e) {
            Log::error('CashDrawerService::open failed: '.$e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function writeLogFile(object $sale, bool $success, string $driver, string $address, ?int $status, string $body): void
    {
        $dir = storage_path('prints');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'cashdrawer_'.($sale->id ?? 'sim').'_'.Str::slug($sale->invoice_number ?? 'test').'.txt';
        $content = "--- Cash Drawer Simulation ---\n";
        $content .= 'Timestamp: '.now()->format('Y-m-d H:i:s')."\n";
        $content .= 'Status: '.($success ? 'success' : 'failed')."\n";
        $content .= 'Driver: '.$driver."\n";
        $content .= 'Address: '.$address."\n";
        $content .= 'Sale ID: '.($sale->id ?? 'n/a')."\n";
        $content .= 'Invoice: '.($sale->invoice_number ?? 'n/a')."\n";
        $content .= 'Total: '.($sale->total ?? 'n/a')."\n";
        if ($status !== null) {
            $content .= 'HTTP Status: '.$status."\n";
        }
        $content .= 'Response: '.$body."\n";

        file_put_contents($dir.DIRECTORY_SEPARATOR.$filename, $content);

        Log::info('CashDrawerService: wrote log file '.$filename);
    }
}
