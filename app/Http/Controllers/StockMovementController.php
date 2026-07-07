<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $movements = StockMovement::with(['item', 'user'])
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->item_id, fn ($q) => $q->where('item_id', $request->item_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $items = Item::orderBy('name')->get();

        return view('stock_movements.index', compact('movements', 'items'));
    }

    public function create(Request $request)
    {
        $items = Item::orderBy('name')->get();
        $selectedItemId = $request->query('item_id');
        $selectedType = $request->query('type', 'in');

        return view('stock_movements.create', compact('items', 'selectedItemId', 'selectedType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if ($validated['type'] === 'out' && $item->current_stock < $validated['quantity']) {
            return back()->withInput()->with('error', 'Stok tidak mencukupi. Stok tersedia: '.$item->current_stock);
        }

        DB::transaction(function () use ($validated, $item, $request) {
            StockMovement::create([
                'item_id' => $item->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'reference_type' => 'manual',
                'notes' => $validated['notes'] ?? null,
                'user_id' => $request->user()->id,
            ]);

            if ($validated['type'] === 'in') {
                $item->increment('current_stock', $validated['quantity']);
            } elseif ($validated['type'] === 'out') {
                $item->decrement('current_stock', $validated['quantity']);
            } else { // adjustment: set langsung ke jumlah yang diinput
                $item->update(['current_stock' => $validated['quantity']]);
            }
        });

        return redirect()->route('stock-movements.index')->with('success', 'Mutasi stok berhasil dicatat.');
    }
}
