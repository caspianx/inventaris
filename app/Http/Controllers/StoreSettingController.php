<?php

namespace App\Http\Controllers;

use App\Jobs\PrintReceipt;
use App\Models\Item;
use App\Models\PrintFile;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if ($request->has('name')) {
            $data['name'] = $validated['name'] ?? $storeSetting->name;
        }
        if ($request->has('address')) {
            $data['address'] = $validated['address'] ?? $storeSetting->address;
        }

        // Print settings (only set when present in request)
        if ($request->has('default_printer')) {
            $data['default_printer'] = $validated['default_printer'] ?? null;
        }
        if ($request->has('auto_print_receipt')) {
            $data['auto_print_receipt'] = $request->boolean('auto_print_receipt');
        }
        if ($request->has('receipt_copies')) {
            $data['receipt_copies'] = $validated['receipt_copies'] ?? $storeSetting->receipt_copies ?? 1;
        }
        if ($request->has('receipt_size')) {
            $data['receipt_size'] = $validated['receipt_size'] ?? $storeSetting->receipt_size ?? '80mm';
        }
        if ($request->has('show_receipt_logo')) {
            $data['show_receipt_logo'] = $request->boolean('show_receipt_logo');
        }
        if ($request->has('receipt_header_title')) {
            $data['receipt_header_title'] = $validated['receipt_header_title'] ?? null;
        }
        if ($request->has('receipt_header_subtitle')) {
            $data['receipt_header_subtitle'] = $validated['receipt_header_subtitle'] ?? null;
        }
        if ($request->has('receipt_header_extra')) {
            $data['receipt_header_extra'] = $validated['receipt_header_extra'] ?? null;
        }
        if ($request->has('receipt_show_invoice_number')) {
            $data['receipt_show_invoice_number'] = $request->boolean('receipt_show_invoice_number');
        }
        if ($request->has('receipt_show_date_time')) {
            $data['receipt_show_date_time'] = $request->boolean('receipt_show_date_time');
        }
        if ($request->has('receipt_show_cashier')) {
            $data['receipt_show_cashier'] = $request->boolean('receipt_show_cashier');
        }
        if ($request->has('receipt_cashier_label')) {
            $data['receipt_cashier_label'] = $validated['receipt_cashier_label'] ?? $storeSetting->receipt_cashier_label ?? 'Kasir';
        }
        if ($request->has('receipt_show_table')) {
            $data['receipt_show_table'] = $request->boolean('receipt_show_table');
        }
        if ($request->has('receipt_table_label')) {
            $data['receipt_table_label'] = $validated['receipt_table_label'] ?? $storeSetting->receipt_table_label ?? 'Tabel';
        }
        if ($request->has('receipt_show_tax_line')) {
            $data['receipt_show_tax_line'] = $request->boolean('receipt_show_tax_line');
        }
        if ($request->has('receipt_tax_label')) {
            $data['receipt_tax_label'] = $validated['receipt_tax_label'] ?? $storeSetting->receipt_tax_label ?? 'Pajak';
        }
        if ($request->has('receipt_tax_rate')) {
            $data['receipt_tax_rate'] = $validated['receipt_tax_rate'] ?? $storeSetting->receipt_tax_rate ?? 0;
        }
        if ($request->has('receipt_show_payment_method')) {
            $data['receipt_show_payment_method'] = $request->boolean('receipt_show_payment_method');
        }
        if ($request->has('receipt_payment_label')) {
            $data['receipt_payment_label'] = $validated['receipt_payment_label'] ?? $storeSetting->receipt_payment_label ?? 'Bayar';
        }
        if ($request->has('receipt_change_label')) {
            $data['receipt_change_label'] = $validated['receipt_change_label'] ?? $storeSetting->receipt_change_label ?? 'Kembalian';
        }
        if ($request->has('receipt_show_item_sku')) {
            $data['receipt_show_item_sku'] = $request->boolean('receipt_show_item_sku');
        }
        if ($request->has('receipt_show_item_quantity')) {
            $data['receipt_show_item_quantity'] = $request->boolean('receipt_show_item_quantity');
        }
        if ($request->has('receipt_show_item_price')) {
            $data['receipt_show_item_price'] = $request->boolean('receipt_show_item_price');
        }
        if ($request->has('receipt_show_item_subtotal')) {
            $data['receipt_show_item_subtotal'] = $request->boolean('receipt_show_item_subtotal');
        }
        if ($request->has('receipt_thank_you_text')) {
            $data['receipt_thank_you_text'] = $validated['receipt_thank_you_text'] ?? $storeSetting->receipt_thank_you_text ?? 'Terima kasih atas kunjungan Anda!';
        }
        if ($request->has('receipt_footer_note')) {
            $data['receipt_footer_note'] = $validated['receipt_footer_note'] ?? null;
        }

        // Cash drawer settings
        if ($request->has('cash_drawer_driver')) {
            $data['cash_drawer_driver'] = $validated['cash_drawer_driver'] ?? $storeSetting->cash_drawer_driver ?? 'network';
        }
        if ($request->has('cash_drawer_address')) {
            $data['cash_drawer_address'] = $validated['cash_drawer_address'] ?? $storeSetting->cash_drawer_address ?? null;
        }

        if ($request->boolean('remove_logo') && $storeSetting->logo_path) {
            File::delete(public_path($storeSetting->logo_path));
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($storeSetting->logo_path) {
                File::delete(public_path($storeSetting->logo_path));
            }

            $directory = public_path('store-logos');

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $file = $request->file('logo');
            $filename = 'store-logo-'.time().'.'.$file->getClientOriginalExtension();
            $file->move($directory, $filename);

            $data['logo_path'] = 'store-logos/'.$filename;
        }

        $storeSetting->update($data);

        return redirect()->route('store-settings.edit')->with('success', 'Pengaturan toko berhasil diperbarui.');
    }

    public function simulatePrint(Request $request)
    {
        $validated = $request->validate([
            'printer' => ['required', 'string', 'max:255'],
            'copies' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $printer = $validated['printer'];
        $copies = $validated['copies'];

        $storeSetting = StoreSetting::current();
        $storeSetting->update([
            'default_printer' => $printer,
            'auto_print_receipt' => true,
            'receipt_copies' => $copies,
        ]);

        $item = Item::where('current_stock', '>', 0)->first();
        if (! $item) {
            return redirect()->route('store-settings.edit')
                ->with('error', 'Tidak ada item dengan stok positif untuk disimulasikan.');
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
                'notes' => 'Simulated sale for web test',
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

        try {
            if (method_exists(PrintReceipt::class, 'dispatchSync')) {
                PrintReceipt::dispatchSync($sale, true);
            } else {
                PrintReceipt::dispatch($sale, true)->onConnection('sync');
            }
        } catch (\Throwable $e) {
            // Fallback langsung jika dispatch tidak berjalan seperti yang diharapkan.
            try {
                (new PrintReceipt($sale, true))->handle();
            } catch (\Throwable $e2) {
                return redirect()->route('store-settings.edit')
                    ->with('error', 'Simulasi gagal saat mencetak: '.$e->getMessage().' | fallback: '.$e2->getMessage());
            }
        }

        $printFile = PrintFile::where('sale_id', $sale->id)->first();
        if (! $printFile) {
            (new PrintReceipt($sale, true))->handle();
            $printFile = PrintFile::where('sale_id', $sale->id)->first();
        }

        if (! $printFile) {
            return redirect()->route('store-settings.edit')
                ->with('error', 'Simulasi selesai tetapi PrintFile tidak ditemukan.');
        }

        return redirect()->route('print-files.index', ['sale_id' => $sale->id])
            ->with('success', 'Simulasi berhasil. Sale ID='.$sale->id.'. File struk tersedia di daftar Cetak Struk.');
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
}
