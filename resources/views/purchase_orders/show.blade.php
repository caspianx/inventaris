@extends('layouts.app')
@section('title', 'Detail PO: ' . $purchaseOrder->po_number)

@section('content')
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4"><strong>No. PO:</strong> {{ $purchaseOrder->po_number }}</div>
            <div class="col-md-4"><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}</div>
            <div class="col-md-4"><strong>Dibuat oleh:</strong> {{ $purchaseOrder->user->name }}</div>
            <div class="col-md-4"><strong>Tanggal Order:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d/m/Y') }}</div>
            <div class="col-md-4"><strong>Estimasi Tiba:</strong> {{ $purchaseOrder->expected_date ? \Carbon\Carbon::parse($purchaseOrder->expected_date)->format('d/m/Y') : '-' }}</div>
            <div class="col-md-4"><strong>Status:</strong>
                @php $badges = ['draft' => 'secondary', 'ordered' => 'warning', 'received' => 'success', 'cancelled' => 'danger']; @endphp
                <span class="badge bg-{{ $badges[$purchaseOrder->status] }}">{{ ucfirst($purchaseOrder->status) }}</span>
            </div>
        </div>

        <table class="table">
            <thead><tr><th>Barang</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
                @foreach($purchaseOrder->items as $line)
                    <tr>
                        <td>{{ $line->item->name }}</td>
                        <td class="text-end">{{ $line->quantity }}</td>
                        <td class="text-end">Rp {{ number_format($line->price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><th colspan="3" class="text-end">Total</th><th class="text-end">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</th></tr>
            </tfoot>
        </table>

        @if($purchaseOrder->notes)
            <p><strong>Catatan:</strong> {{ $purchaseOrder->notes }}</p>
        @endif

        @if($purchaseOrder->status !== 'received' && $purchaseOrder->status !== 'cancelled')
            <div class="d-flex gap-2 mt-3">
                @if($purchaseOrder->status === 'draft')
                    <form action="{{ route('purchase-orders.status', $purchaseOrder) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="ordered">
                        <button class="btn btn-warning">Tandai Sudah Dipesan</button>
                    </form>
                @endif
                <form action="{{ route('purchase-orders.status', $purchaseOrder) }}" method="POST" data-confirm="Konfirmasi barang telah diterima? Stok akan otomatis bertambah.">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="received">
                    <button class="btn btn-success">Terima Barang</button>
                </form>
                <form action="{{ route('purchase-orders.status', $purchaseOrder) }}" method="POST" data-confirm="Batalkan PO ini?">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button class="btn btn-outline-danger">Batalkan</button>
                </form>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-3">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-link">&larr; Kembali</a>
            @if(auth()->user()->role === 'manager' && $purchaseOrder->status !== 'received')
                <form action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" method="POST" data-confirm="Hapus PO {{ $purchaseOrder->po_number }}? Tindakan ini tidak bisa dibatalkan.">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Hapus PO</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
