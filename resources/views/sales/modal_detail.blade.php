<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">{{ $sale->invoice_number }}</h5>
                <div class="text-muted small">{{ $sale->created_at->format('d M Y H:i') }} WIB</div>
                <div class="text-muted small">Kasir: {{ $sale->user->name ?? '-' }}</div>
            </div>
            <div class="text-end">
                <div class="badge bg-primary">Total: Rp {{ number_format($sale->total, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-3">
        <div class="card border rounded-3">
            <div class="card-body py-3 px-4">
                <div class="row gy-2">
                    <div class="col-md-4">
                        <div class="text-muted small">Subtotal</div>
                        <div class="fw-semibold">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Diskon</div>
                        <div class="fw-semibold">Rp {{ number_format($sale->discount, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Bayar</div>
                        <div class="fw-semibold">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->item_name }}</div>
                                <div class="text-muted small">SKU: {{ $item->item_sku }}</div>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 mt-3">
        <div class="row gy-2">
            <div class="col-md-4">
                <div class="text-muted small">Metode Pembayaran</div>
                <div class="fw-semibold text-capitalize">{{ $sale->payment_method }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Kembali</div>
                <div class="fw-semibold">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</div>
            </div>
            @if($sale->notes)
                <div class="col-md-4">
                    <div class="text-muted small">Catatan</div>
                    <div>{{ $sale->notes }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
