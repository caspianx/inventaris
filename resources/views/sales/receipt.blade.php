<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $sale->invoice_number }}</title>
    <style>
        @php
            $receiptWidth = match($storeSetting->receipt_size ?? '80mm') {
                '58mm' => '220px',
                '80mm' => '300px',
                'roll' => '340px',
                default => '300px',
            };
        @endphp
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: {{ $receiptWidth }};
            margin: 0 auto;
            padding: 12px;
            color: #000;
        }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .item-name { font-weight: bold; }
        .store-logo { max-width: 90px; max-height: 70px; object-fit: contain; margin-bottom: 4px; }
        .toolbar { text-align: center; margin-bottom: 16px; }
        .toolbar button, .toolbar a {
            display: inline-block;
            padding: 8px 16px;
            margin: 4px;
            text-decoration: none;
            border-radius: 6px;
            border: 1px solid #333;
            background: #fff;
            color: #333;
            font-family: Arial, sans-serif;
            font-size: 13px;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none !important; }
            body { width: auto; }
        }
    </style>
</head>
<body>

    <div class="toolbar no-print">
        <button onclick="window.print()">Cetak Struk</button>
        @if(optional(auth()->user())->canAccess('sales.create'))
            <a href="{{ route('sales.create') }}">Transaksi Baru</a>
        @endif
        @if(optional(auth()->user())->canAccess('sales.view'))
            <a href="{{ route('sales.index') }}">Riwayat Transaksi</a>
        @endif
    </div>

    <div class="center">
        @if(!empty($storeSetting->show_receipt_logo) && isset($logoDataUri) && !empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="Logo {{ $storeSetting->name }}" class="store-logo"><br>
        @elseif(!empty($storeSetting->show_receipt_logo) && !empty($storeSetting->logo_path))
            <img src="{{ asset($storeSetting->logo_path) }}" alt="Logo {{ $storeSetting->name }}" class="store-logo"><br>
        @endif
        <strong>{{ strtoupper($storeSetting->receipt_header_title ?? $storeSetting->name ?? 'Inventory App') }}</strong><br>
        @if(!empty($storeSetting->receipt_header_subtitle))
            <span>{{ $storeSetting->receipt_header_subtitle }}</span><br>
        @endif
        @if(!empty($storeSetting->address))
            {!! nl2br(e($storeSetting->address)) !!}<br>
        @endif
        @if(!empty($storeSetting->receipt_header_extra))
            <span>{{ $storeSetting->receipt_header_extra }}</span><br>
        @endif
        ------------------------
    </div>
    <div class="line"></div>

    <table>
        @if($storeSetting->receipt_show_invoice_number)
            <tr><td>No. Invoice</td><td class="right">{{ $sale->invoice_number }}</td></tr>
        @endif
        @if($storeSetting->receipt_show_date_time)
            <tr><td>Tanggal</td><td class="right">{{ $sale->created_at->format('d/m/Y H:i') }}</td></tr>
        @endif
        @if($storeSetting->receipt_show_cashier)
            <tr><td>{{ $storeSetting->receipt_cashier_label }}</td><td class="right">{{ $sale->user->name ?? '-' }}</td></tr>
        @endif
        @if($storeSetting->receipt_show_table && $sale->notes)
            <tr><td>{{ $storeSetting->receipt_table_label }}</td><td class="right">{{ $sale->notes }}</td></tr>
        @endif
    </table>
    <div class="line"></div>

    @foreach($sale->items as $item)
        @php
            $itemLine = $item->item_name;
            $details = [];
            if ($storeSetting->receipt_show_item_quantity) {
                $details[] = $item->quantity . 'x';
            }
            if ($storeSetting->receipt_show_item_price) {
                $details[] = 'Rp ' . number_format($item->price, 0, ',', '.');
            }
            if ($storeSetting->receipt_show_item_subtotal) {
                $details[] = 'Rp ' . number_format($item->subtotal, 0, ',', '.');
            }
        @endphp
        <div class="item-name">{{ $itemLine }}</div>
        <table>
            <tr>
                <td>{{ implode(' ', $details) }}</td>
            </tr>
            @if($storeSetting->receipt_show_item_sku && $item->item_sku)
                <tr>
                    <td class="text-muted small">{{ $item->item_sku }}</td>
                </tr>
            @endif
        </table>
    @endforeach

    <div class="line"></div>
    <table>
        <tr><td>Subtotal</td><td class="right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td></tr>
        @if($sale->discount > 0)
            <tr><td>Diskon</td><td class="right">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</td></tr>
        @endif
        <tr><td><strong>TOTAL</strong></td><td class="right"><strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong></td></tr>
    </table>
    <div class="line"></div>

    <table>
        <tr><td>Bayar ({{ strtoupper($sale->payment_method) }})</td><td class="right">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td></tr>
        <tr><td>Kembali</td><td class="right">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td></tr>
    </table>

    @if($sale->notes)
        <div class="line"></div>
        <div>Catatan: {{ $sale->notes }}</div>
    @endif

    <div class="line"></div>
    <div class="center">
        Terima kasih atas kunjungan Anda!<br>
        Barang yang sudah dibeli tidak dapat dikembalikan.
    </div>

</body>
</html>
