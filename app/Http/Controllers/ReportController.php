<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Services\Code128BarcodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        $filename = sprintf('laporan_%s_%s.xlsx', $type, now()->format('Ymd_His'));
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach (array_values($row) as $colIndex => $value) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1).($rowIndex + 1);
                $sheet->setCellValue($cellCoordinate, $value);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $filename = sprintf('laporan_%s_%s.xlsx', $type, now()->format('Ymd_His'));

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tempFile);
        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function import(Request $request, string $type)
    {
        if (! in_array($type, self::SUPPORTED_TYPES, true)) {
            return redirect()->route('reports.index')->with('error', 'Tipe import tidak dikenal.');
        }

        $request->validate([
            'file' => ['nullable', 'file', 'mimes:csv,txt,xlsx,xls'],
            'path' => ['nullable', 'string'],
            'confirm' => ['nullable', 'in:1'],
            'cancel' => ['nullable', 'in:1'],
        ]);

        try {
            // Determine source file: freshly uploaded or previously stored (from confirmation step)
            if ($request->input('confirm') === '1' && $request->input('path')) {
                $storedPath = $request->input('path');

                if (! Storage::disk('local')->exists($storedPath)) {
                    return redirect()->route('reports.index')->with('error', 'File import tidak ditemukan (mungkin sudah dihapus).');
                }

                $fullPath = Storage::disk('local')->path($storedPath);
                $extension = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION));
            } elseif ($request->input('cancel') === '1' && $request->input('path')) {
                // cancel and clean temporary file
                Storage::disk('local')->delete($request->input('path'));
                return redirect()->route('reports.index')->with('info', 'Import dibatalkan dan file sementara dihapus.');
            } else {
                $uploadedFile = $request->file('file');
                if (! $uploadedFile) {
                    return redirect()->route('reports.index')->with('error', 'File import tidak ditemukan.');
                }

                $storedPath = $uploadedFile->store('imports');
                $fullPath = Storage::disk('local')->path($storedPath);
                $extension = strtolower($uploadedFile->extension());
            }

            if (in_array($extension, ['xlsx', 'xls'], true)) {
                $rows = $this->spreadsheetToArray($fullPath);
            } else {
                $rows = $this->csvToArray((string) file_get_contents($fullPath));
            }

            // If importing items, detect conflicts first and require confirmation
            if ($type === 'items') {
                // Only perform conflict check when not already confirmed
                if ($request->input('confirm') !== '1') {
                    $conflicts = [];
                    $headerMap = [];

                    if (! empty($rows) && $this->isHeaderRow($rows[0])) {
                        $headerMap = $this->parseHeaderRow($rows[0]);
                    }

                    foreach ($rows as $index => $row) {
                        if ($index === 0 && $headerMap) {
                            continue;
                        }

                        if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                            continue;
                        }

                        $sku = trim($this->getRowValue($row, $headerMap, 'sku', 0));
                        $name = trim($this->getRowValue($row, $headerMap, 'name', 1));

                        if ($sku !== '' && $existing = Item::where('sku', $sku)->first()) {
                            $conflicts[] = ['row' => $index + 1, 'sku' => $sku, 'name' => $name, 'existing_id' => $existing->id, 'existing_name' => $existing->name];
                            continue;
                        }

                        if ($name !== '' && $existing = Item::whereRaw('LOWER(name) = ?', [strtolower($name)])->first()) {
                            $conflicts[] = ['row' => $index + 1, 'sku' => $sku, 'name' => $name, 'existing_id' => $existing->id, 'existing_name' => $existing->name];
                        }
                    }

                    if (! empty($conflicts)) {
                        return view('reports.import-confirm', [
                            'type' => $type,
                            'path' => $storedPath,
                            'conflicts' => $conflicts,
                            'sampleRows' => array_slice($rows, 0, 50),
                        ]);
                    }
                }

                // proceed with actual import (confirmed or no conflicts)
                $this->importItems($rows);
            }

            if ($type === 'categories') {
                $this->importCategories($rows);
            }

            if ($type === 'suppliers') {
                $this->importSuppliers($rows);
            }

            // clean temporary uploaded file
            if (isset($storedPath)) {
                Storage::delete($storedPath);
            }

            return redirect()->route('reports.index')->with('success', 'Import '.ucfirst($type).' berhasil disimpan.');
        } catch (\Throwable $e) {
            Log::error('Import '.$type.' gagal: '.$e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Import gagal: '.$e->getMessage());
        }
    }

    private function importItems(array $rows): void
    {
        $headerMap = [];

        foreach ($rows as $index => $row) {
            if ($index === 0 && $this->isHeaderRow($row)) {
                $headerMap = $this->parseHeaderRow($row);
                continue;
            }

            if (! array_filter($row, fn ($value) => trim((string) $value) !== '')) {
                continue;
            }

            $sku = trim($this->getRowValue($row, $headerMap, 'sku', 0));
            $name = trim($this->getRowValue($row, $headerMap, 'name', 1));

            if ($name === '') {
                continue;
            }

            $categoryName = trim($this->getRowValue($row, $headerMap, 'category', 2));
            $categoryId = null;

            if ($categoryName !== '') {
                $category = Category::firstOrCreate(['name' => $categoryName]);
                $categoryId = $category->id;
            }

            $unit = trim($this->getRowValue($row, $headerMap, 'unit', 3)) ?: '-';
            $purchasePrice = $this->toDecimal($this->getRowValue($row, $headerMap, 'purchase_price', 4));
            $sellingPrice = $this->toDecimal($this->getRowValue($row, $headerMap, 'selling_price', 5) ?: $this->getRowValue($row, $headerMap, 'purchase_price', 4));
            $minStock = (int) $this->getRowValue($row, $headerMap, 'min_stock', 6);
            $currentStock = (int) $this->getRowValue($row, $headerMap, 'current_stock', 7);
            $description = trim($this->getRowValue($row, $headerMap, 'description', 8));

            $item = Item::updateOrCreate(
                ['sku' => $sku ?: Str::uuid()->toString()],
                [
                    'name' => $name,
                    'category_id' => $categoryId,
                    'unit' => $unit,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'current_stock' => $currentStock,
                    'min_stock' => $minStock,
                    'description' => $description,
                ]
            );

            if (! $item->barcode_path || ! File::exists(public_path($item->barcode_path))) {
                $this->generateBarcode($item);
            }
        }
    }

    private function parseHeaderRow(array $row): array
    {
        $map = [];

        foreach ($row as $index => $value) {
            $normalized = $this->normalizeHeader((string) $value);

            match ($normalized) {
                'sku' => $map['sku'] = $index,
                'nama', 'nama barang', 'name' => $map['name'] = $index,
                'kategori', 'category' => $map['category'] = $index,
                'satuan', 'unit' => $map['unit'] = $index,
                'harga beli', 'purchase_price', 'purchase price' => $map['purchase_price'] = $index,
                'harga jual', 'selling_price', 'selling price' => $map['selling_price'] = $index,
                'stok', 'current_stock', 'stock' => $map['current_stock'] = $index,
                'stok min', 'min_stock', 'minimum stock' => $map['min_stock'] = $index,
                'deskripsi', 'description' => $map['description'] = $index,
                default => null,
            };
        }

        return $map;
    }

    private function normalizeHeader(string $value): string
    {
        $value = trim(strtolower($value));
        $value = preg_replace('/\s+/', ' ', $value);
        $value = str_replace(['_', '-'], ' ', $value);

        return $value;
    }

    private function getRowValue(array $row, array $headerMap, string $key, int $fallbackIndex)
    {
        if (isset($headerMap[$key]) && array_key_exists($headerMap[$key], $row)) {
            return $row[$headerMap[$key]];
        }

        return $row[$fallbackIndex] ?? '';
    }

    private function isHeaderRow(array $row): bool
    {
        $firstCell = strtolower(trim((string) ($row[0] ?? '')));

        return $firstCell !== '' && (
            str_contains($firstCell, 'sku') ||
            str_contains($firstCell, 'nama') ||
            str_contains($firstCell, 'kode')
        );
    }

    private function spreadsheetToArray(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = trim((string) $cell->getValue());
            }

            if (! empty(array_filter($rowData, fn ($value) => $value !== ''))) {
                $rows[] = $rowData;
            }
        }

        return $rows;
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

    private function generateBarcode(Item $item): void
    {
        try {
            $directory = public_path('barcodes');

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $filename = basename($item->sku).'.svg';
            $path = 'barcodes/'.$filename;
            $fullPath = public_path($path);

            $svg = app(Code128BarcodeGenerator::class)->svg($item->sku);
            File::put($fullPath, $svg);
            $item->forceFill(['barcode_path' => $path])->save();
        } catch (\Throwable $e) {
            Log::error('Gagal membuat barcode untuk item import SKU '.$item->sku.': '.$e->getMessage());
        }
    }

    private function toDecimal($value): float
    {
        $clean = preg_replace('/[^0-9\,\.]/', '', (string) $value);
        $clean = str_replace(',', '.', $clean);

        return (float) $clean;
    }
}
