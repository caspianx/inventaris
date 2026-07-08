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
            line-height: 1.25;
        }
        .center { text-align: center; }
        .separator { border-top: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .item-name { font-weight: bold; margin-bottom: 2px; }
        .store-logo { max-width: 90px; max-height: 70px; object-fit: contain; margin-bottom: 6px; }
        .toolbar { text-align: center; margin-bottom: 12px; }
        .toolbar button, .toolbar a {
            display: inline-block;
            padding: 6px 12px;
            margin: 4px;
            text-decoration: none;
            border-radius: 6px;
            border: 1px solid #333;
            background: #fff;
            color: #333;
            font-family: Arial, sans-serif;
            font-size: 12px;
            cursor: pointer;
        }
        .small { font-size: 11px; color: #222; }
        .muted { color: #666; font-size: 11px; }
        .totals td { padding: 4px 0; }
        .item-table td { padding: 0; }
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
        <div class="small"><strong>{{ strtoupper($storeSetting->receipt_header_title ?? $storeSetting->name ?? 'Inventory App') }}</strong></div>
        @if(!empty($storeSetting->receipt_header_subtitle))
            <div class="small">{{ $storeSetting->receipt_header_subtitle }}</div>
        @endif
        @if(!empty($storeSetting->address))
            <div class="small">{!! nl2br(e($storeSetting->address)) !!}</div>
        @endif
        @if(!empty($storeSetting->receipt_header_extra))
            <div class="small">{{ $storeSetting->receipt_header_extra }}</div>
        @endif
    </div>
    <div class="separator"></div>

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
    <div class="separator"></div>

    @foreach($sale->items as $item)
        @php
            $qty = $item->quantity ?? 1;
            $lineTotal = $item->subtotal ?? ($item->price * $qty);
        @endphp
        <table class="item-table">
            <tr>
                <td style="width:60%"><div class="item-name">{{ $item->item_name }}</div></td>
                <td style="width:15%" class="center small">x{{ $qty }}</td>
                <td style="width:25%" class="right">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
            </tr>
            @if($storeSetting->receipt_show_item_sku && $item->item_sku)
                <tr>
                    <td colspan="3" class="muted small">{{ $item->item_sku }}</td>
                </tr>
            @endif
        </table>
    @endforeach

    <div class="separator"></div>
    <table class="totals">
        <tr><td class="small">Subtotal</td><td class="right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td></tr>
        @if($sale->discount > 0)
            <tr><td class="small">Diskon</td><td class="right">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</td></tr>
        @endif
        <tr><td><strong>TOTAL</strong></td><td class="right"><strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong></td></tr>
    </table>
    <div class="separator"></div>

    <table>
        <tr><td>Bayar ({{ strtoupper($sale->payment_method) }})</td><td class="right">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td></tr>
        <tr><td>Kembali</td><td class="right">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td></tr>
    </table>

    @if($sale->notes)
        <div class="line"></div>
        <div>Catatan: {{ $sale->notes }}</div>
    @endif

    <div class="separator"></div>
    <div class="center small">
        {{ $storeSetting->receipt_thank_you_text ?? 'Terima kasih atas kunjungan Anda!' }}<br>
        <div class="muted">Barang yang sudah dibeli tidak dapat dikembalikan.</div>
    </div>

</body>
</html>
