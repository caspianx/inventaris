@extends('layouts.app')
@section('title', 'Purchase Order')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
                        <option value="">📋 Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Konsep</option>
                        <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Dipesan</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Diterima</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </form>
            </div>
            @if(auth()->user()->canAccess('purchase_orders.create'))
                <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Buat Purchase Order
                </a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Supplier</th>
                    <th>Tanggal Pesan</th>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                    <tr>
                        <td>
                            <strong style="color: var(--primary);">{{ $po->po_number }}</strong>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $po->supplier->name }}</div>
                            <small style="color: var(--gray-500);">{{ $po->supplier->contact_person ?? '-' }}</small>
                        </td>
                        <td>
                            <div>{{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</div>
                            <small style="color: var(--gray-500);">{{ \Carbon\Carbon::parse($po->order_date)->diffForHumans() }}</small>
                        </td>
                        <td>
                            @php
                                $statusConfig = [
                                    'draft' => ['badge' => 'bg-secondary', 'label' => 'Konsep', 'icon' => 'file-earmark'],
                                    'ordered' => ['badge' => 'bg-warning', 'label' => 'Dipesan', 'icon' => 'hourglass-split'],
                                    'received' => ['badge' => 'bg-success', 'label' => 'Diterima', 'icon' => 'check-circle'],
                                    'cancelled' => ['badge' => 'bg-danger', 'label' => 'Dibatalkan', 'icon' => 'x-circle'],
                                ];
                                $config = $statusConfig[$po->status] ?? $statusConfig['draft'];
                            @endphp
                            <span class="badge {{ $config['badge'] }}">
                                <i class="bi bi-{{ $config['icon'] }}"></i> {{ $config['label'] }}
                            </span>
                        </td>
                        <td class="text-end">
                            <strong style="font-size: 1.1rem; color: var(--primary);">
                                Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                            </strong>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-outline-primary" title="Lihat Detail">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                @if(auth()->user()->canAccess('purchase_orders.delete') && $po->status !== 'received')
                                    <form action="{{ route('purchase-orders.destroy', $po) }}" method="POST" class="d-inline" data-confirm="Yakin hapus PO {{ $po->po_number }}?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada purchase order</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $purchaseOrders->links() }}
    </div>
</div>
@endsection
