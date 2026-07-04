<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'user'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        return view('purchase_orders.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $total = 0;
            foreach ($validated['items'] as $line) {
                $total += $line['quantity'] * $line['price'];
            }

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . now()->format('Ymd') . '-' . str_pad((PurchaseOrder::count() + 1), 4, '0', STR_PAD_LEFT),
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $request->user()->id,
                'status' => 'draft',
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'total_amount' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $line) {
                $po->items()->create([
                    'item_id' => $line['item_id'],
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'subtotal' => $line['quantity'] * $line['price'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'PO berhasil dibuat.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'items.item']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,ordered,received,cancelled'],
        ]);

        if ($validated['status'] === 'received' && $purchaseOrder->status !== 'received') {
            DB::transaction(function () use ($purchaseOrder, $request) {
                foreach ($purchaseOrder->items as $line) {
                    $line->item->increment('current_stock', $line->quantity);

                    StockMovement::create([
                        'item_id' => $line->item_id,
                        'type' => 'in',
                        'quantity' => $line->quantity,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'notes' => 'Penerimaan ' . $purchaseOrder->po_number,
                        'user_id' => $request->user()->id,
                    ]);
                }

                $purchaseOrder->update(['status' => 'received']);
            });

            return back()->with('success', 'PO diterima, stok telah diperbarui.');
        }

        $purchaseOrder->update(['status' => $validated['status']]);

        return back()->with('success', 'Status PO berhasil diperbarui.');
    }

    /**
     * Hapus PO. Dibatasi role 'manager' lewat middleware di routes/web.php.
     * PO berstatus 'received' tidak boleh dihapus karena sudah menambah stok
     * dan tercatat di histori mutasi stok (StockMovement) — hapus di sini
     * tidak akan mengembalikan/mengoreksi stok tersebut secara otomatis.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'PO yang sudah diterima tidak bisa dihapus karena sudah memengaruhi stok. Gunakan menu Stok Masuk/Keluar untuk koreksi jika diperlukan.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'PO berhasil dihapus.');
    }
}
