<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6f9; padding: 24px; }
        .label-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .label-box {
            width: 190px;
            border: 1px dashed #adb5bd;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            background: #fff;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .label-box img { max-width: 100%; height: auto; }
        .label-sku { font-size: 13px; font-weight: 700; margin-top: 4px; }
        .label-name { font-size: 11px; color: #444; line-height: 1.2; }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .label-box { border: none; }
        }
    </style>
</head>
<body>

    <div class="no-print d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Cetak Barcode</h5>
            <span class="text-muted small">{{ $totalItems }} barang &middot; {{ $labels->count() }} label</span>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Cetak</button>
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    <div class="label-grid">
        @forelse($labels as $item)
            <div class="label-box">
                @if($item->barcode_path)
                    <img src="{{ asset($item->barcode_path) }}" alt="Barcode {{ $item->sku }}">
                @else
                    <div class="text-muted small py-4">Barcode tidak tersedia</div>
                @endif
                <div class="label-sku">{{ $item->sku }}</div>
                <div class="label-name">{{ $item->name }}</div>
            </div>
        @empty
            <p class="text-muted">Tidak ada barang yang dipilih untuk dicetak.</p>
        @endforelse
    </div>

</body>
</html>
