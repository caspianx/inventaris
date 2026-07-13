<?php

namespace App\Http\Controllers;

use App\Jobs\PrintReceipt;
use App\Models\PrintFile;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class StoreSettingController extends Controller
{
    public function edit()
    {
        $storeSetting = StoreSetting::current();
        $printerOptions = $this->getAvailablePrinters();
        $hasCashDrawer = Auth::user()?->has_cash_drawer ?? false;

        // Prepare a preview sale object for rendering receipt preview
        $previewSale = new \stdClass();
        $previewSale->invoice_number = 'PREVIEW-'.now()->format('YmdHis');
        $previewSale->created_at = now();
        $previewSale->user = (object) ['name' => Auth::user()?->name ?? 'Kasir'];
        $sampleItem = (object) [
            'item_name' => 'Contoh Produk',
            'item_sku' => 'BRG-0001',
            'quantity' => 2,
            'price' => 15000,
            'subtotal' => 30000,
        ];
        $previewSale->items = collect([$sampleItem]);
        $previewSale->subtotal = 30000;
        $previewSale->discount = 0;
        $previewSale->total = 30000;
        $previewSale->payment_method = 'cash';
        $previewSale->paid_amount = 50000;
        $previewSale->change_amount = 20000;
        $previewSale->notes = 'Tabel 1';

        // Prepare logo data URI if available for inline preview
        $logoDataUri = null;
        if (! empty($storeSetting->logo_path) && file_exists(public_path($storeSetting->logo_path))) {
            $path = public_path($storeSetting->logo_path);
            $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'image/png';
            $logoDataUri = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
        }

        // Render the sales.receipt view as HTML and base64 encode for iframe src
        $receiptHtml = view('sales.receipt', ['sale' => $previewSale, 'storeSetting' => $storeSetting, 'logoDataUri' => $logoDataUri])->render();
        $receiptPreviewBase64 = base64_encode($receiptHtml);

        return view('store_settings.edit', compact('storeSetting', 'printerOptions', 'hasCashDrawer', 'receiptPreviewBase64'));
    }

    protected function getAvailablePrinters(): array
    {
        $printers = [];

        if (PHP_OS_FAMILY === 'Windows') {
            // Try PowerShell first for better compatibility
            $command = 'powershell.exe -NoProfile -Command "Get-Printer | Select-Object -ExpandProperty Name"';
            $output = shell_exec($command);
            if ($output) {
                foreach (preg_split('/\r?\n/', trim($output)) as $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        $printers[] = $line;
                    }
                }
            }

            if (empty($printers)) {
                $command = 'wmic printer get name';
                $output = shell_exec($command);
                if ($output) {
                    $lines = preg_split('/\r?\n/', trim($output));
                    foreach ($lines as $index => $line) {
                        $line = trim($line);
                        if ($index === 0 || $line === '') {
                            continue;
                        }
                        $printers[] = $line;
                    }
                }
            }
        }

        return array_values(array_unique($printers));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // allow subforms to omit `name` (they include hidden inputs, but
            // tolerate missing value) and fallback to existing store name
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'default_printer' => ['nullable', 'string', 'max:255'],
            'auto_print_receipt' => ['nullable', 'boolean'],
            'receipt_copies' => ['nullable', 'integer', 'min:1', 'max:10'],
            'receipt_size' => ['nullable', 'in:58mm,80mm,roll'],
            'receipt_retention_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'show_receipt_logo' => ['nullable', 'boolean'],
            'receipt_header_title' => ['nullable', 'string', 'max:255'],
            'receipt_header_subtitle' => ['nullable', 'string', 'max:255'],
            'receipt_header_extra' => ['nullable', 'string'],
            'receipt_show_invoice_number' => ['nullable', 'boolean'],
            'receipt_show_date_time' => ['nullable', 'boolean'],
            'receipt_show_cashier' => ['nullable', 'boolean'],
            'receipt_cashier_label' => ['nullable', 'string', 'max:255'],
            'receipt_show_table' => ['nullable', 'boolean'],
            'receipt_table_label' => ['nullable', 'string', 'max:255'],
            'receipt_show_tax_line' => ['nullable', 'boolean'],
            'receipt_tax_label' => ['nullable', 'string', 'max:255'],
            'receipt_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'receipt_show_payment_method' => ['nullable', 'boolean'],
            'receipt_payment_label' => ['nullable', 'string', 'max:255'],
            'receipt_change_label' => ['nullable', 'string', 'max:255'],
            'receipt_show_item_sku' => ['nullable', 'boolean'],
            'receipt_show_item_quantity' => ['nullable', 'boolean'],
            'receipt_show_item_price' => ['nullable', 'boolean'],
            'receipt_show_item_subtotal' => ['nullable', 'boolean'],
            'receipt_thank_you_text' => ['nullable', 'string', 'max:255'],
            'receipt_footer_note' => ['nullable', 'string'],
            'cash_drawer_driver' => ['nullable', 'in:network,printer,none'],
            'cash_drawer_address' => ['nullable', 'string', 'max:255'],
        ]);

        $storeSetting = StoreSetting::current();

        // Only update fields that were present in the request. This avoids
        // unintentionally overwriting unrelated settings when submitting
        // subforms that only include a subset of fields.
        $data = [];

        // Field configuration for conditional updates
        $fieldConfig = [
            // Basic settings
            'name' => ['type' => 'string', 'fallback' => 'name'],
            'address' => ['type' => 'string', 'fallback' => 'address'],
            
            // Print settings
            'default_printer' => ['type' => 'string', 'fallback' => null],
            'auto_print_receipt' => ['type' => 'boolean'],
            'receipt_copies' => ['type' => 'int', 'fallback' => 'receipt_copies', 'default' => 1],
            'receipt_size' => ['type' => 'string', 'fallback' => 'receipt_size', 'default' => '80mm'],
            'receipt_retention_days' => ['type' => 'int', 'fallback' => 'receipt_retention_days', 'default' => 30],
            'show_receipt_logo' => ['type' => 'boolean'],
            
            // Receipt header settings
            'receipt_header_title' => ['type' => 'string', 'fallback' => null],
            'receipt_header_subtitle' => ['type' => 'string', 'fallback' => null],
            'receipt_header_extra' => ['type' => 'string', 'fallback' => null],
            
            // Receipt transaction display
            'receipt_show_invoice_number' => ['type' => 'boolean'],
            'receipt_show_date_time' => ['type' => 'boolean'],
            'receipt_show_cashier' => ['type' => 'boolean'],
            'receipt_cashier_label' => ['type' => 'string', 'fallback' => 'receipt_cashier_label', 'default' => 'Kasir'],
            'receipt_show_table' => ['type' => 'boolean'],
            'receipt_table_label' => ['type' => 'string', 'fallback' => 'receipt_table_label', 'default' => 'Tabel'],
            
            // Tax settings
            'receipt_show_tax_line' => ['type' => 'boolean'],
            'receipt_tax_label' => ['type' => 'string', 'fallback' => 'receipt_tax_label', 'default' => 'Pajak'],
            'receipt_tax_rate' => ['type' => 'numeric', 'fallback' => 'receipt_tax_rate', 'default' => 0],
            
            // Payment settings
            'receipt_show_payment_method' => ['type' => 'boolean'],
            'receipt_payment_label' => ['type' => 'string', 'fallback' => 'receipt_payment_label', 'default' => 'Bayar'],
            'receipt_change_label' => ['type' => 'string', 'fallback' => 'receipt_change_label', 'default' => 'Kembalian'],
            
            // Item display columns
            'receipt_show_item_sku' => ['type' => 'boolean'],
            'receipt_show_item_quantity' => ['type' => 'boolean'],
            'receipt_show_item_price' => ['type' => 'boolean'],
            'receipt_show_item_subtotal' => ['type' => 'boolean'],
            
            // Receipt footer
            'receipt_thank_you_text' => ['type' => 'string', 'fallback' => 'receipt_thank_you_text', 'default' => 'Terima kasih atas kunjungan Anda!'],
            'receipt_footer_note' => ['type' => 'string', 'fallback' => null],
            
            // Cash drawer settings
            'cash_drawer_driver' => ['type' => 'string', 'fallback' => 'cash_drawer_driver', 'default' => 'network'],
            'cash_drawer_address' => ['type' => 'string', 'fallback' => null],
        ];

        // Process conditional field updates
        foreach ($fieldConfig as $field => $config) {
            if ($request->has($field)) {
                $data[$field] = $this->getFieldValue($request, $validated, $storeSetting, $field, $config);
            }
        }

        if ($request->boolean('remove_logo')) {
            $this->deleteStoreLogoFiles($storeSetting->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            $this->deleteStoreLogoFiles($storeSetting->logo_path);

            $directory = public_path('store-logos');

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'store-logo.'.$extension;
            $targetPath = $directory.'/'.$filename;

            if (File::exists($targetPath)) {
                File::delete($targetPath);
            }

            $stream = fopen($file->getRealPath(), 'rb');
            if ($stream !== false) {
                $written = file_put_contents($targetPath, $stream);
                fclose($stream);

                if ($written === false) {
                    throw new \RuntimeException('Gagal menulis file logo toko.');
                }
            } else {
                throw new \RuntimeException('Gagal membaca file logo toko.');
            }

            $data['logo_path'] = 'store-logos/'.$filename;
        }

        $storeSetting->update($data);

        return redirect()->route('store-settings.edit')->with('success', 'Pengaturan toko berhasil diperbarui.');
    }

    protected function deleteStoreLogoFiles(?string $currentLogoPath = null): void
    {
        if ($currentLogoPath) {
            File::delete(public_path($currentLogoPath));
        }

        $directory = public_path('store-logos');
        if (! File::exists($directory)) {
            return;
        }

        foreach (File::glob($directory.'/store-logo*') as $logoFile) {
            File::delete($logoFile);
        }
    }

    public function simulatePrint(Request $request)
    {
        $validated = $request->validate([
            'printer' => ['nullable', 'string', 'max:255'],
            'copies' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $storeSetting = StoreSetting::current();
        $printer = $validated['printer'] ?: $storeSetting->default_printer;

        if (! $printer) {
            return redirect()->route('store-settings.edit')
                ->with('error', 'Nama printer belum dikonfigurasi. Silakan atur printer default di Pengaturan Print Struk.');
        }

        $storeSetting->default_printer = $printer;
        $storeSetting->receipt_copies = $validated['copies'];
        $storeSetting->save();

        $printFile = PrintFile::orderByDesc('last_printed_at')->first();
        $printedFromExisting = false;

        if ($printFile) {
            $baseName = pathinfo($printFile->filename, PATHINFO_FILENAME);
            $existingTxtPath = storage_path('prints/'.$baseName.'.txt');

            if (file_exists($existingTxtPath)) {
                try {
                    if (! PrintReceipt::sendToPrinter($existingTxtPath, $printer, $validated['copies'])) {
                        return redirect()->route('store-settings.edit')
                            ->with('error', 'Simulasi gagal: tidak dapat mencetak file struk yang ada.');
                    }

                    $printedFromExisting = true;
                } catch (\Throwable $e) {
                    return redirect()->route('store-settings.edit')
                        ->with('error', 'Simulasi gagal saat mencetak file struk yang ada: '.$e->getMessage());
                }
            }
        }

        if (! $printedFromExisting) {
            $previewSale = new \stdClass();
            $previewSale->id = null;
            $previewSale->invoice_number = 'SIM-'.now()->format('YmdHis');
            $previewSale->created_at = now();
            $previewSale->payment_method = 'cash';
            $previewSale->subtotal = 15000;
            $previewSale->discount = 0;
            $previewSale->total = 15000;
            $previewSale->paid_amount = 20000;
            $previewSale->change_amount = 5000;
            $previewSale->notes = 'Simulasi print struk';
            $previewSale->user = (object) ['name' => Auth::user()?->name ?? 'Kasir'];
            $previewSale->items = collect([
                (object) [
                    'item_name' => 'Contoh Produk',
                    'item_sku' => 'BRG-0001',
                    'quantity' => 1,
                    'price' => 15000,
                    'subtotal' => 15000,
                ],
            ]);

            try {
                (new PrintReceipt($previewSale, true, true))->handle();
            } catch (\Throwable $e) {
                return redirect()->route('store-settings.edit')
                    ->with('error', 'Simulasi gagal saat mencetak: '.$e->getMessage());
            }
        }

        return redirect()->route('store-settings.edit')
            ->with('success', 'Simulasi print berhasil dijalankan tanpa mengubah stok atau membuat transaksi nyata.');
    }

    public function testCashDrawer(Request $request)
    {
        $validated = $request->validate([
            'cash_drawer_address' => ['nullable', 'string', 'max:255'],
        ]);

        $store = StoreSetting::current();
        $address = $validated['cash_drawer_address'] ?? $store->cash_drawer_address;

        if (! $address) {
            return redirect()->route('store-settings.edit')->with('error', 'Alamat cash drawer belum diisi.');
        }

        // Create a small fake sale object to provide context to device
        $fakeSale = (object) [
            'id' => 0,
            'invoice_number' => 'TEST-'.now()->format('YmdHis'),
            'total' => 0,
        ];

        // Temporarily override store setting address for this test
        $origAddress = $store->cash_drawer_address;
        $store->cash_drawer_address = $address;

        $result = app(\App\Services\CashDrawerService::class)->open($fakeSale);

        // restore
        $store->cash_drawer_address = $origAddress;

        if (is_array($result)) {
            if (! empty($result['success'])) {
                $msg = 'Percobaan membuka cash drawer terkirim.';
                if (! empty($result['status'])) {
                    $msg .= ' HTTP status: '.$result['status'].'.';
                }
                if (! empty($result['body'])) {
                    $msg .= ' Response: '.mb_strimwidth($result['body'], 0, 400, '...');
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $msg], 200);
                }

                return redirect()->route('store-settings.edit')->with('success', $msg);
            }

            $err = $result['error'] ?? ($result['body'] ?? 'Unknown error');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Percobaan membuka cash drawer gagal: '.$err], 500);
            }

            return redirect()->route('store-settings.edit')->with('error', 'Percobaan membuka cash drawer gagal: '.$err);
        }

        // fallback boolean
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => (bool) $result, 'message' => 'Percobaan membuka cash drawer '.($result ? 'terkirim' : 'gagal')], $result ? 200 : 500);
        }

        if ($result) {
            return redirect()->route('store-settings.edit')->with('success', 'Percobaan membuka cash drawer terkirim.');
        }

        return redirect()->route('store-settings.edit')->with('error', 'Percobaan membuka cash drawer gagal — cek alamat dan koneksi perangkat.');
    }

    /**
     * Extract and process field value based on configuration
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $validated
     * @param  \App\Models\StoreSetting  $storeSetting
     * @param  string  $field
     * @param  array  $config
     * @return mixed
     */
    protected function getFieldValue($request, $validated, $storeSetting, $field, $config)
    {
        $type = $config['type'] ?? 'string';

        // Handle boolean fields
        if ($type === 'boolean') {
            return $request->boolean($field);
        }

        // Get value from validated data
        $value = $validated[$field] ?? null;
        
        // If value is null and fallback is specified, use stored value or default
        if ($value === null && isset($config['fallback'])) {
            if ($config['fallback'] !== null) {
                // Fallback to stored value
                $value = $storeSetting->{$config['fallback']} ?? ($config['default'] ?? null);
            } else {
                // Use default directly (no stored fallback)
                $value = $config['default'] ?? null;
            }
        }

        return $value;
    }
}
