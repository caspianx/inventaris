<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $sale->invoice_number }}</title>
    <style>
        @php
            $receiptWidth = match($storeSetting->receipt_size ?? '80mm') {
                '58mm' => '220px',
                '80mm' => '280px',
                'roll' => '320px',
                default => '280px',
            };
        @endphp
        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            width: {{ $receiptWidth }};
            margin: 0 auto;
            padding: 6px;
            color: #000;
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .center { text-align: center; }
        .separator { border-top: 1px dashed #000; margin: 4px 0; padding: 0; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; 
            margin: 0;
            padding: 0;
        }
        table tr { margin: 0; padding: 0; }
        table td { 
            padding: 1px 0; 
            vertical-align: top; 
            word-wrap: break-word; 
            overflow-wrap: break-word;
            margin: 0;
        }
        .right { text-align: right; }
        .item-name { 
            font-weight: bold; 
            margin: 0 0 1px 0; 
            padding: 0;
            line-height: 1.3;
        }
        .store-logo { 
            max-width: 80px; 
            max-height: 45px; 
            object-fit: contain; 
            margin-bottom: 3px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .toolbar { text-align: center; margin-bottom: 8px; }
        .toolbar button, .toolbar a {
            display: inline-block;
            padding: 5px 10px;
            margin: 3px;
            text-decoration: none;
            border-radius: 6px;
            border: 1px solid #333;
            background: #fff;
            color: #333;
            font-family: Arial, sans-serif;
            font-size: 11px;
            cursor: pointer;
        }
        .header-text { 
            font-size: 12px; 
            color: #000; 
            margin: 1px 0;
            padding: 0;
            line-height: 1.3;
        }
        .header-text strong { font-weight: bold; }
        .small { font-size: 11px; color: #222; margin: 0; padding: 0; }
        .muted { color: #555; font-size: 10px; margin: 0; padding: 0; }
        .item-qty { width: 15%; text-align: center; }
        .item-price { width: 25%; text-align: right; }
        .item-name-col { width: 60%; }
        .label-col { width: 65%; }
        .value-col { width: 35%; text-align: right; }
        @media print {
            .no-print { display: none !important; }
            body { width: auto; padding: 4px; }
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
            <img src="{{ $logoDataUri }}" alt="Logo {{ $storeSetting->name }}" class="store-logo">
        @elseif(!empty($storeSetting->show_receipt_logo) && !empty($storeSetting->logo_path))
            <img src="{{ asset($storeSetting->logo_path) }}" alt="Logo {{ $storeSetting->name }}" class="store-logo">
        @endif
        <div class="header-text"><strong>{{ strtoupper($storeSetting->receipt_header_title ?? $storeSetting->name ?? 'Inventory App') }}</strong></div>
        @if(!empty($storeSetting->receipt_header_subtitle))
            <div class="header-text small">{{ $storeSetting->receipt_header_subtitle }}</div>
        @endif
        @if(!empty($storeSetting->address))
            <div class="header-text small">{!! nl2br(e($storeSetting->address)) !!}</div>
        @endif
        @if(!empty($storeSetting->receipt_header_extra))
            <div class="header-text small">{{ $storeSetting->receipt_header_extra }}</div>
        @endif
    </div>
    <div class="separator"></div>

    <table>
        @if($storeSetting->receipt_show_invoice_number)
            <tr>
                <td class="label-col small">No. Invoice</td>
                <td class="value-col small">{{ $sale->invoice_number }}</td>
            </tr>
        @endif
        @if($storeSetting->receipt_show_date_time)
            <tr>
                <td class="label-col small">Tanggal</td>
                <td class="value-col small">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endif
        @if($storeSetting->receipt_show_cashier)
            <tr>
                <td class="label-col small">{{ $storeSetting->receipt_cashier_label }}</td>
                <td class="value-col small">{{ $sale->user->name ?? '-' }}</td>
            </tr>
        @endif
        @if($storeSetting->receipt_show_table && $sale->notes)
            <tr>
                <td class="label-col small">{{ $storeSetting->receipt_table_label }}</td>
                <td class="value-col small">{{ $sale->notes }}</td>
            </tr>
        @endif
    </table>
    <div class="separator"></div>

    @foreach($sale->items as $item)
        @php
            $qty = $item->quantity ?? 1;
            $lineTotal = $item->subtotal ?? ($item->price * $qty);
            $itemName = $item->item_name ?? '';
            // Split long names intelligently (max ~22 chars for receipt width)
            $maxNameLen = 22;
            $displayName = $itemName;
            $extraName = '';
            
            if (strlen($itemName) > $maxNameLen) {
                // Try to break at word boundary
                $part1 = substr($itemName, 0, $maxNameLen);
                $lastSpace = strrpos($part1, ' ');
                if ($lastSpace > 10) {
                    $displayName = substr($itemName, 0, $lastSpace);
                    $extraName = substr($itemName, $lastSpace + 1);
                } else {
                    $displayName = $part1;
                    $extraName = substr($itemName, $maxNameLen);
                }
            }
        @endphp
        <table>
            <tr>
                <td class="item-name-col"><div class="item-name">{{ $displayName }}</div></td>
                <td class="item-qty small">x{{ $qty }}</td>
                <td class="item-price small">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
            </tr>
            @if(!empty($extraName))
                <tr>
                    <td colspan="3" class="muted">{{ $extraName }}</td>
                </tr>
            @endif
            @if($storeSetting->receipt_show_item_sku && $item->item_sku)
                <tr>
                    <td colspan="3" class="muted">{{ $item->item_sku }}</td>
                </tr>
            @endif
        </table>
    @endforeach

    <div class="separator"></div>
    <table>
        <tr>
            <td class="label-col small">Subtotal</td>
            <td class="value-col small">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount > 0)
            <tr>
                <td class="label-col small">Diskon</td>
                <td class="value-col small">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr>
            <td class="label-col"><strong class="small">TOTAL</strong></td>
            <td class="value-col"><strong class="small">Rp {{ number_format($sale->total, 0, ',', '.') }}</strong></td>
        </tr>
    </table>
    <div class="separator"></div>

    <table>
        <tr>
            <td class="label-col small">Bayar ({{ strtoupper($sale->payment_method) }})</td>
            <td class="value-col small">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label-col small">Kembali</td>
            <td class="value-col small">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($sale->notes)
        <div class="separator"></div>
        <div class="small"><strong>Catatan:</strong> {{ $sale->notes }}</div>
    @endif

    <div class="separator"></div>
    <div class="center">
        <div class="header-text small"><strong>{{ $storeSetting->receipt_thank_you_text ?? 'Terima kasih atas kunjungan Anda!' }}</strong></div>
        <div class="muted" style="margin-top: 2px;">Barang yang sudah dibeli tidak dapat dikembalikan.</div>
    </div>

@if(request()->boolean('print'))
    <script>
        const redirectToPos = function () {
            window.location.replace('{{ route('sales.create') }}');
        };

        window.addEventListener('load', function () {
            setTimeout(function () {
                try {
                    window.print();
                } catch (e) {
                    redirectToPos();
                }
            }, 250);
        });

        window.addEventListener('afterprint', function () {
            redirectToPos();
        });

        window.addEventListener('focus', function () {
            setTimeout(redirectToPos, 1000);
        }, { once: true });
    </script>
@endif
</body>
</html>
