<?php

namespace App\Http\Controllers;

use App\Jobs\PrintReceipt;
use App\Models\PrintFile;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class PrintFileController extends Controller
{
    public function index(Request $request)
    {
        $this->cleanupLegacyFiles();

        $files = PrintFile::with('sale')
            ->when($request->sale_id, function ($query, $saleId) {
                $query->where('sale_id', $saleId);
            })
            ->when($request->printer_name, function ($query, $printerName) {
                $query->where('printer_name', 'like', "%{$printerName}%");
            })
            ->orderByDesc('last_printed_at')
            ->get();

        return view('print_files.index', compact('files'));
    }

    protected function cleanupLegacyFiles(): void
    {
        $directory = storage_path('prints');

        if (! File::exists($directory)) {
            return;
        }

        foreach (File::files($directory) as $file) {
            $name = $file->getFilename();

            if (preg_match('/^receipt_\d+_.*\.txt$/', $name) && ! preg_match('/^receipt_\d+\.txt$/', $name)) {
                File::delete($file->getPathname());
            }
        }
    }

    public function download(string $filename)
    {
        $safeName = basename($filename);
        $path = storage_path('prints/'.$safeName);

        if ($safeName === '' || $safeName !== $filename || ! File::exists($path)) {
            abort(404);
        }

        return Response::download($path, $safeName);
    }

    public function reprint(Sale $sale)
    {
        try {
            if (method_exists(PrintReceipt::class, 'dispatchSync')) {
                PrintReceipt::dispatchSync($sale);
            } else {
                PrintReceipt::dispatch($sale)->onConnection('sync');
            }

            return redirect()->route('print-files.index')->with('success', 'Cetak ulang struk telah diproses.');
        } catch (\Throwable $e) {
            return redirect()->route('print-files.index')->with('error', 'Gagal mencetak ulang: '.$e->getMessage());
        }
    }

    public function destroy(PrintFile $printFile)
    {
        $this->deleteReceiptFiles($printFile->filename);
        $printFile->delete();

        return redirect()->route('print-files.index')->with('success', 'File struk berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('selected_files', []);

        if (! is_array($ids) || empty($ids)) {
            return redirect()->route('print-files.index')->with('error', 'Pilih minimal satu file untuk dihapus.');
        }

        $files = PrintFile::whereIn('id', $ids)->get();
        $deletedCount = 0;

        foreach ($files as $file) {
            $this->deleteReceiptFiles($file->filename);
            $file->delete();
            $deletedCount++;
        }

        return redirect()->route('print-files.index')->with('success', $deletedCount.' file struk berhasil dihapus.');
    }

    protected function deleteReceiptFiles(string $filename): void
    {
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $directory = storage_path('prints');
        $variants = [
            $baseName.'.html',
            $baseName.'.txt',
        ];

        foreach ($variants as $variant) {
            $path = $directory.DIRECTORY_SEPARATOR.$variant;
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    protected function extractSaleId(string $filename): ?int
    {
        if (preg_match('/^receipt_(\d+)_/', $filename, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
