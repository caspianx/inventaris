<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Services\Code128BarcodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    private function generateSku(): string
    {
        $lastNumber = Item::where('sku', 'like', 'BRG-%')
            ->pluck('sku')
            ->map(function (string $sku): int {
                preg_match('/^BRG-(\d+)$/', $sku, $matches);

                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->max() ?? 0;

        $nextNumber = $lastNumber + 1;

        do {
            $sku = 'BRG-'.str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (Item::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Generate barcode SVG untuk item dan simpan path-nya.
     * Mengembalikan pesan error (string) jika gagal, atau null jika berhasil.
     * Dibuat "gagal aman": kalau proses ini error, data barang yang sudah
     * tersimpan TIDAK ikut hilang, cukup barcode-nya saja yang kosong.
     */
    private function generateBarcode(Item $item): ?string
    {
        try {
            $directory = public_path('barcodes');

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Prevent path traversal: gunakan basename untuk sanitize filename
            $filename = basename($item->sku).'.svg';
            $path = 'barcodes/'.$filename;
            $fullPath = public_path($path);

            // Validate path is within barcodes directory
            $realPath = realpath(dirname($fullPath)) ?: dirname($fullPath);
            $barcodeDir = realpath($directory) ?: $directory;
            if (strpos($realPath, $barcodeDir) !== 0) {
                throw new \InvalidArgumentException('Invalid barcode path');
            }

            $svg = app(Code128BarcodeGenerator::class)->svg($item->sku);

            File::put($fullPath, $svg);

            $item->forceFill(['barcode_path' => $path])->save();

            return null;
        } catch (\Throwable $e) {
            Log::error('Gagal membuat barcode untuk item #'.$item->id.' (SKU: '.$item->sku.'): '.$e->getMessage());

            return 'Barang berhasil disimpan, tetapi barcode gagal dibuat. Silakan hubungi admin (cek storage/logs/laravel.log untuk detail teknis).';
        }
    }

    /**
     * Halaman cetak barcode untuk barang yang dipilih dari Master Barang.
     * Menerima ?ids[]=1&ids[]=2&qty[1]=3&qty[2]=1 (qty = jumlah label per barang, default 1, maks 100).
     */
    public function printBarcode(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->query('ids', [])));

        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu barang untuk dicetak barcode-nya.');
        }

        $qtyInput = (array) $request->query('qty', []);

        $items = Item::whereIn('id', $ids)->orderBy('name')->get();

        // Pastikan setiap barang punya file barcode sebelum dicetak (generate kalau belum ada/hilang)
        foreach ($items as $item) {
            if (! $item->barcode_path || ! File::exists(public_path($item->barcode_path))) {
                $this->generateBarcode($item);
            }
        }

        $labels = collect();

        foreach ($items as $item) {
            $qty = max(1, min(100, (int) ($qtyInput[$item->id] ?? 1)));

            for ($i = 0; $i < $qty; $i++) {
                $labels->push($item);
            }
        }

        return view('items.print-barcode', [
            'labels' => $labels,
            'totalItems' => $items->count(),
        ]);
    }

    public function index(Request $request)
    {
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $items = Item::with('category')
            ->when($request->search, fn ($q) => $q->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%");
            }))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->low_stock, fn ($q) => $q->lowStock())
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $autoSku = $this->generateSku();

        return view('items.create', compact('categories', 'autoSku'));
    }

    /**
     * Endpoint AJAX untuk cek duplikat nama barang secara real-time saat mengetik di form.
     * Dipakai oleh resources/views/items/create.blade.php & edit.blade.php.
     */
    public function checkDuplicate(Request $request)
    {
        $name = trim((string) $request->query('name'));
        $excludeId = $request->query('exclude_id');

        if ($name === '') {
            return response()->json(['exists' => false]);
        }

        $match = Item::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->first();

        return response()->json([
            'exists' => (bool) $match,
            'item' => $match ? [
                'id' => $match->id,
                'sku' => $match->sku,
                'name' => $match->name,
                'current_stock' => $match->current_stock,
                'edit_url' => $request->user()?->canAccess('items.edit') ? route('items.edit', $match) : null,
                'stock_url' => $request->user()?->canAccess('stock_movements.create')
                    ? route('stock-movements.create', ['item_id' => $match->id, 'type' => 'in'])
                    : null,
            ] : null,
        ]);
    }

    /**
     * Pencarian barang untuk layar kasir (Transaksi Penjualan).
     * Mendukung scan barcode (cocok SKU persis) maupun ketik nama/SKU sebagian.
     */
    public function posSearch(Request $request)
    {
        $search = trim((string) $request->query('search'));

        if ($search === '') {
            return response()->json([]);
        }

        $items = Item::where('sku', $search)
            ->orWhere(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orderByRaw('sku = ? DESC', [$search]) // hasil scan (cocok persis) tampil paling atas
            ->limit(10)
            ->get(['id', 'sku', 'name', 'selling_price', 'current_stock']);

        return response()->json($items);
    }

    public function autocomplete(Request $request)
    {
        $search = trim((string) $request->query('search'));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $items = Item::where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        })
            ->latest()
            ->limit(10)
            ->get(['id', 'sku', 'name', 'current_stock']);

        return response()->json($items->map(fn ($item) => [
            'label' => "{$item->sku} - {$item->name}",
            'value' => $item->name,
            'sku' => $item->sku,
            'name' => $item->name,
            'current_stock' => $item->current_stock,
        ]));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:100'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('items', 'name'), // dicek exact match di level DB
                function ($attribute, $value, $fail) {
                    // dicek juga case-insensitive ("Mouse Wireless" vs "mouse wireless")
                    if (Item::whereRaw('LOWER(name) = ?', [strtolower($value)])->exists()) {
                        $fail('Barang dengan nama ini sudah terdaftar. Silakan cek Master Barang atau gunakan menu Stok Masuk untuk menambah stoknya.');
                    }
                },
            ],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        if (empty($validated['sku']) || Item::where('sku', $validated['sku'])->exists()) {
            $validated['sku'] = $this->generateSku();
        }

        $item = Item::create($validated);
        $barcodeError = $this->generateBarcode($item);

        if ($barcodeError) {
            return redirect()->route('items.index')->with('warning', $barcodeError);
        }

        return redirect()->route('items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $categories = Category::orderBy('name')->get();

        return view('items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', Rule::unique('items')->ignore($item)],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('items', 'name')->ignore($item->id),
                function ($attribute, $value, $fail) use ($item) {
                    if (Item::whereRaw('LOWER(name) = ?', [strtolower($value)])->where('id', '!=', $item->id)->exists()) {
                        $fail('Barang dengan nama ini sudah terdaftar pada data lain.');
                    }
                },
            ],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $oldSku = $item->sku;

        $item->update($validated);

        $barcodeError = null;

        if ($item->sku !== $oldSku || ! $item->barcode_path || ! File::exists(public_path($item->barcode_path))) {
            $barcodeError = $this->generateBarcode($item);
        }

        if ($barcodeError) {
            return redirect()->route('items.index')->with('warning', $barcodeError);
        }

        return redirect()->route('items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        if ($item->stockMovements()->exists() || $item->purchaseOrderItems()->exists() || $item->saleItems()->exists()) {
            return back()->with('error', 'Barang tidak bisa dihapus karena sudah memiliki riwayat stok, PO, atau penjualan.');
        }

        $item->delete();

        return redirect()->route('items.index')->with('success', 'Barang berhasil dihapus.');
    }
}