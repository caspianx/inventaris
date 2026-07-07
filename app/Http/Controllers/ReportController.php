<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    private const SUPPORTED_TYPES = ['items', 'categories', 'suppliers', 'purchase_orders'];

    public function index()
    {
        return view('reports.index');
    }

    public function export(string $type)
    {
        if (! in_array($type, self::SUPPORTED_TYPES, true)) {
            return redirect()->route('reports.index')->with('error', 'Tipe laporan tidak dikenal.');
        }

        $filename = sprintf('laporan_%s_%s.csv', $type, now()->format('Ymd_His'));
        $rows = [];

        if ($type === 'items') {
            $rows[] = ['SKU', 'Nama', 'Kategori', 'Satuan', 'Harga Beli', 'Harga Jual', 'Stok Min', 'Deskripsi'];
            foreach (Item::with('category')->orderBy('name')->cursor() as $item) {
                $rows[] = [
                    trim($item->sku),
                    trim($item->name),
                    trim($item->category?->name ?? ''),
                    trim($item->unit),
                    number_format($item->purchase_price, 2, '.', ''),
                    number_format($item->selling_price, 2, '.', ''),
                    (string) $item->min_stock,
                    trim($item->description),
                ];
            }
        }

        if ($type === 'categories') {
            $rows[] = ['Nama', 'Deskripsi'];
            foreach (Category::orderBy('name')->cursor() as $category) {
                $rows[] = [$category->name, $category->description];
            }
        }

        if ($type === 'suppliers') {
            $rows[] = ['Nama', 'Kontak', 'Telepon', 'Email', 'Alamat'];
            foreach (Supplier::orderBy('name')->cursor() as $supplier) {
                $rows[] = [
                    $supplier->name,
                    $supplier->contact_person,
                    $supplier->phone,
                    $supplier->email,
                    $supplier->address,
                ];
            }
        }

        if ($type === 'purchase_orders') {
            $rows[] = ['No. PO', 'Supplier', 'Status', 'Tanggal Order', 'Tanggal Diharapkan', 'Total', 'Catatan'];
            foreach (\App\Models\PurchaseOrder::with('supplier')->orderBy('order_date')->cursor() as $purchaseOrder) {
                $rows[] = [
                    trim($purchaseOrder->po_number),
                    trim($purchaseOrder->supplier?->name ?? ''),
                    trim($purchaseOrder->status),
                    optional($purchaseOrder->order_date)->format('Y-m-d') ?? '',
                    optional($purchaseOrder->expected_date)->format('Y-m-d') ?? '',
                    number_format($purchaseOrder->total_amount, 2, '.', ''),
                    trim($purchaseOrder->notes),
                ];
            }
        }

        $content = $this->arrayToCsv($rows);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function import(Request $request, string $type)
    {
        if (! in_array($type, self::SUPPORTED_TYPES, true)) {
            return redirect()->route('reports.index')->with('error', 'Tipe import tidak dikenal.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        try {
            $path = $request->file('file')->store('imports');
            $content = Storage::get($path);
            $rows = $this->csvToArray($content);

            if ($type === 'items') {
                $this->importItems($rows);
            }

            if ($type === 'categories') {
                $this->importCategories($rows);
            }

            if ($type === 'suppliers') {
                $this->importSuppliers($rows);
            }

            return redirect()->route('reports.index')->with('success', 'Import '.ucfirst($type).' berhasil disimpan.');
        } catch (\Throwable $e) {
            Log::error('Import '.$type.' gagal: '.$e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Import gagal: '.$e->getMessage());
        }
    }

    private function importItems(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if (count($row) < 9) {
                continue;
            }

            $categoryName = trim($row[2] ?? '');
            $categoryId = null;

            if ($categoryName !== '') {
                $category = Category::firstOrCreate(['name' => $categoryName]);
                $categoryId = $category->id;
            }

            Item::updateOrCreate(
                ['sku' => trim($row[0]) ?: Str::uuid()->toString()],
                [
                    'name' => trim($row[1]) ?: 'Tanpa nama',
                    'category_id' => $categoryId,
                    'unit' => trim($row[3]) ?: '-',
                    'purchase_price' => $this->toDecimal($row[4]),
                    'selling_price' => $this->toDecimal($row[5]),
                    'current_stock' => (int) ($row[6] ?? 0),
                    'min_stock' => (int) ($row[7] ?? 0),
                    'description' => trim($row[8] ?? ''),
                ]
            );
        }
    }

    private function importCategories(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if (empty($row[0])) {
                continue;
            }

            Category::updateOrCreate(
                ['name' => trim($row[0])],
                ['description' => trim($row[1] ?? '')]
            );
        }
    }

    private function importSuppliers(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if (empty($row[0])) {
                continue;
            }

            Supplier::updateOrCreate(
                ['name' => trim($row[0])],
                [
                    'contact_person' => trim($row[1] ?? ''),
                    'phone' => trim($row[2] ?? ''),
                    'email' => trim($row[3] ?? ''),
                    'address' => trim($row[4] ?? ''),
                ]
            );
        }
    }

    private function arrayToCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM untuk kompatibilitas Excel

        foreach ($rows as $row) {
            $normalized = array_map(fn ($value) => $this->normalizeCsvValue($value), $row);
            fputcsv($handle, $normalized);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    private function normalizeCsvValue($value): string
    {
        $value = (string) $value;
        $value = trim($value);
        $value = str_replace(["\r", "\n"], [' ', ' '], $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    private function csvToArray(string $content): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($content));
        $rows = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, $line);
            rewind($handle);
            $rows[] = fgetcsv($handle);
            fclose($handle);
        }

        return array_filter($rows);
    }

    private function toDecimal($value): float
    {
        $clean = preg_replace('/[^0-9\,\.]/', '', (string) $value);
        $clean = str_replace(',', '.', $clean);

        return (float) $clean;
    }
}
