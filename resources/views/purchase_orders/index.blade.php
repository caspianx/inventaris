@extends('layouts.app')
@section('title', 'Purchase Order')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form class="d-flex gap-2" method="GET">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </form>
        @if(auth()->user()->canAccess('purchase_orders.create'))
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat PO</a>
        @endif
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>No. PO</th><th>Supplier</th><th>Tanggal</th><th>Status</th><th class="text-end">Total</th><th></th></tr></thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                    <tr>
                        <td>{{ $po->po_number }}</td>
                        <td>{{ $po->supplier->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $badges = ['draft' => 'secondary', 'ordered' => 'warning', 'received' => 'success', 'cancelled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $badges[$po->status] }}">{{ ucfirst($po->status) }}</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Detail</a>
                            @if(auth()->user()->canAccess('purchase_orders.delete') && $po->status !== 'received')
                                <form action="{{ route('purchase-orders.destroy', $po) }}" method="POST" class="d-inline" data-confirm="Hapus PO {{ $po->po_number }}? Tindakan ini tidak bisa dibatalkan.">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada purchase order</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $purchaseOrders->links() }}</div>
</div>
@endsection
