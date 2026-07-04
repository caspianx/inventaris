<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 300px; /* kira-kira lebar kertas thermal 80mm */
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
        .toolbar { text-align: center; margin-bottom: 16px; }
        .toolbar button, .toolbar a {
            display: inline-block; padding: 8px 16px; margin: 4px; text-decoration: none;
            border-radius: 6px; border: 1px solid #333; background: #fff; color: #333; font-family: Arial, sans-serif; font-size: 13px; cursor: pointer;
        }
        @media print {
            .no-print { display: none !important; }
            body { width: auto; }
        }
    </style>
</head>
<body>

    <div class="toolbar no-print">
        <button onclick="window.print()">🖨️ Cetak Struk</button>
        <a href="{{ route('sales.create') }}">Transaksi Baru</a>
        <a href="{{ route('sales.index') }}">Riwayat Transaksi</a>
    </div>

    <div class="center">
        <strong>INVENTORY APP</strong><br>
        Jl. Contoh Alamat No. 123<br>
        ------------------------
    </div>
    <div class="line"></div>

    <table>
        <tr><td>No. Invoice</td><td class="right">{{ $sale->invoice_number }}</td></tr>
        <tr><td>Tanggal</td><td class="right">{{ $sale->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td class="right">{{ $sale->user->name ?? '-' }}</td></tr>
    </table>
    <div class="line"></div>

    @foreach($sale->items as $item)
        <div class="item-name">{{ $item->item_name }}</div>
        <table>
            <tr>
                <td>{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
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
