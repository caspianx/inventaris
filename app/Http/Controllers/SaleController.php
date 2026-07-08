<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\PrintReceipt;
use App\Models\Item;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    /**
     * Riwayat transaksi penjualan.
     */
    public function index(Request $request)
    {
        $sales = Sale::with(['user', 'items'])
            ->when($request->date, fn ($q) => $q->whereDate('created_at', $request->date))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $todayTotal = Sale::whereDate('created_at', today())->sum('total');
        $todayCount = Sale::whereDate('created_at', today())->count();

        return view('sales.index', compact('sales', 'todayTotal', 'todayCount'));
    }

    /**
     * Layar kasir (POS) untuk membuat transaksi baru.
     */
    public function create()
    {
        return view('sales.create');
    }

    /**
     * Simpan transaksi. Semua harga & ketersediaan stok dihitung ULANG di server
     * dari data Item terbaru — nilai yang dikirim dari browser tidak pernah dipercaya
     * langsung, untuk mencegah manipulasi harga lewat request palsu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,qris,debit,credit'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $sale = DB::transaction(function () use ($validated, $request) {
                $subtotal = 0;
                $lines = [];

                foreach ($validated['items'] as $line) {
                    // Lock baris item supaya aman dari race condition kalau ada 2 kasir
                    // memproses barang yang sama secara bersamaan.
                    $item = Item::lockForUpdate()->findOrFail($line['item_id']);

                    if ($item->current_stock < $line['quantity']) {
                        throw new \RuntimeException("Stok \"{$item->name}\" tidak mencukupi. Tersedia: {$item->current_stock}, diminta: {$line['quantity']}.");
                    }

                    $lineSubtotal = $item->selling_price * $line['quantity'];
                    $subtotal += $lineSubtotal;

                    $lines[] = [
                        'item' => $item,
                        'quantity' => $line['quantity'],
                        'price' => $item->selling_price,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                $discount = min($validated['discount'] ?? 0, $subtotal); // diskon tidak boleh melebihi subtotal
                $total = $subtotal - $discount;

                if ($validated['paid_amount'] < $total) {
                    throw new \RuntimeException('Jumlah dibayar kurang dari total belanja.');
                }

                $sale = Sale::create([
                    'invoice_number' => 'INV-'.now()->format('Ymd').'-'.str_pad((string) (Sale::count() + 1), 4, '0', STR_PAD_LEFT),
                    'user_id' => $request->user()->id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_method' => $validated['payment_method'],
                    'paid_amount' => $validated['paid_amount'],
                    'change_amount' => $validated['paid_amount'] - $total,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($lines as $line) {
                    $sale->items()->create([
                        'item_id' => $line['item']->id,
                        'item_name' => $line['item']->name,
                        'item_sku' => $line['item']->sku,
                        'price' => $line['price'],
                        'quantity' => $line['quantity'],
                        'subtotal' => $line['subtotal'],
                    ]);

                    $line['item']->decrement('current_stock', $line['quantity']);

                    StockMovement::create([
                        'item_id' => $line['item']->id,
                        'type' => 'out',
                        'quantity' => $line['quantity'],
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'notes' => 'Penjualan '.$sale->invoice_number,
                        'user_id' => $request->user()->id,
                    ]);
                }

                return $sale;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $printMessage = 'Transaksi berhasil disimpan dan struk telah diproses.';
        $printError = null;

        try {
            $store = StoreSetting::current();
            if ($store->auto_print_receipt || $request->boolean('print_receipt')) {
                if (method_exists(PrintReceipt::class, 'dispatchSync')) {
                    PrintReceipt::dispatchSync($sale);
                } else {
                    PrintReceipt::dispatch($sale)->onConnection('sync');
                }
            }
        } catch (\Throwable $e) {
            $printError = $e->getMessage();
            $printMessage = 'Transaksi berhasil disimpan, tetapi cetak struk gagal: '.$printError;
            Log::error('Auto print dispatch failed: '.$e->getMessage());
        }

        // Auto-open cash drawer if the user has the device and enabled auto-open.
        try {
            $user = $request->user();
            if ($user && ($user->has_cash_drawer ?? false) && (($user->auto_open_cash_drawer ?? false) || $request->boolean('open_cash_drawer'))) {
                app(\App\Services\CashDrawerService::class)->open($sale);
            }
        } catch (\Throwable $e) {
            Log::error('Auto open cash drawer failed: '.$e->getMessage());
        }

        return redirect()->route('sales.create')
            ->with('pos_success', $printError === null)
            ->with('pos_message', $printMessage)
            ->with('pos_sale_id', $sale->id)
            ->with('pos_error', $printError);
    }

    /**
     * Halaman struk (bisa dicetak).
     */
    public function show(Sale $sale)
    {
        $sale->load(['items', 'user']);

        $store = StoreSetting::current();
        $logoDataUri = null;
        if (! empty($store->show_receipt_logo) && ! empty($store->logo_path)) {
            $logoFile = public_path($store->logo_path);
            if (file_exists($logoFile)) {
                $mimeType = mime_content_type($logoFile) ?: 'image/png';
                $data = base64_encode(file_get_contents($logoFile));
                $logoDataUri = "data:$mimeType;base64,$data";
            }
        }

        return view('sales.receipt', [
            'sale' => $sale,
            'logoDataUri' => $logoDataUri,
        ]);
    }
}
