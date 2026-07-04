@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Jenis Barang</div>
                <div class="fs-3 fw-bold">{{ $totalItems }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Barang Stok Menipis</div>
                <div class="fs-3 fw-bold text-danger">{{ $lowStockCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Nilai Total Stok</div>
                <div class="fs-4 fw-bold">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat shadow-sm">
            <div class="card-body">
                <div class="text-muted small">PO Belum Selesai</div>
                <div class="fs-3 fw-bold">{{ $pendingPOs }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="bi bi-exclamation-triangle text-danger"></i> Barang Perlu Restock</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Barang</th><th>Kategori</th><th class="text-end">Stok</th><th class="text-end">Min</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($lowStockItems as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category->name ?? '-' }}</td>
                                <td class="text-end text-danger fw-bold">{{ $item->current_stock }}</td>
                                <td class="text-end">{{ $item->min_stock }}</td>
                                <td class="text-end">
                                    <a href="{{ route('stock-movements.create', ['item_id' => $item->id, 'type' => 'in']) }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-plus-circle"></i> Restock
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Semua stok aman</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="bi bi-clock-history"></i> Mutasi Stok Terbaru</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Barang</th><th>Tipe</th><th class="text-end">Qty</th><th>Oleh</th></tr></thead>
                    <tbody>
                        @forelse($recentMovements as $mv)
                            <tr>
                                <td>{{ $mv->item->name }}</td>
                                <td>
                                    @if($mv->type === 'in')
                                        <span class="badge bg-success">Masuk</span>
                                    @elseif($mv->type === 'out')
                                        <span class="badge bg-danger">Keluar</span>
                                    @else
                                        <span class="badge bg-secondary">Adjustment</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ $mv->quantity }}</td>
                                <td>{{ $mv->user->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada mutasi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
