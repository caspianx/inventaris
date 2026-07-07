@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<!-- STAT CARDS -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Total Jenis Barang</div>
                        <div class="fs-3 fw-bold mt-2">{{ $totalItems }}</div>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(99, 102, 241, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-seam" style="font-size: 1.5rem; color: #6366f1;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Barang Stok Menipis</div>
                        <div class="fs-3 fw-bold mt-2 text-danger">{{ $lowStockCount }}</div>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-exclamation-circle" style="font-size: 1.5rem; color: #ef4444;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Nilai Total Stok</div>
                        <div class="fs-4 fw-bold mt-2" style="color: #10b981;">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-cash-coin" style="font-size: 1.5rem; color: #10b981;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">PO Belum Selesai</div>
                        <div class="fs-3 fw-bold mt-2" style="color: #f59e0b;">{{ $pendingPOs }}</div>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clipboard-check" style="font-size: 1.5rem; color: #f59e0b;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLES SECTION -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle"></i> Barang Perlu Restock
            </div>
            <div class="card-body p-0">
                @forelse($lowStockItems as $item)
                    <div style="padding: 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: 600; color: #1f2937;">{{ $item->name }}</div>
                            <div style="font-size: 0.85rem; color: #6b7280;">{{ $item->category->name ?? '-' }}</div>
                        </div>
                        <div style="text-align: right; margin-right: 1rem;">
                            <div style="font-weight: 600; color: #ef4444;">{{ $item->current_stock }}</div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Min: {{ $item->min_stock }}</div>
                        </div>
                        @if(auth()->user()->canAccess('stock_movements.create'))
                            <a href="{{ route('stock-movements.create', ['item_id' => $item->id, 'type' => 'in']) }}" class="btn btn-sm btn-success">
                                <i class="bi bi-plus-lg"></i> Restock
                            </a>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </div>
                @empty
                    <div style="padding: 3rem 1.25rem; text-align: center; color: #9ca3af;">
                        <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        <p class="mt-2 mb-0">Semua stok aman</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Mutasi Stok Terbaru
            </div>
            <div class="card-body p-0">
                @forelse($recentMovements as $mv)
                    <div style="padding: 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: 600; color: #1f2937;">{{ $mv->item->name }}</div>
                            <div style="font-size: 0.85rem; color: #6b7280;">Oleh: {{ $mv->user->name }}</div>
                        </div>
                        <div style="text-align: center; margin-right: 1rem;">
                            @if($mv->type === 'in')
                                <span class="badge bg-success">Masuk</span>
                            @elseif($mv->type === 'out')
                                <span class="badge bg-danger">Keluar</span>
                            @else
                                <span class="badge bg-secondary">Adjustment</span>
                            @endif
                        </div>
                        <div style="font-weight: 600; color: #6366f1; min-width: 40px; text-align: right;">{{ $mv->quantity }}</div>
                    </div>
                @empty
                    <div style="padding: 3rem 1.25rem; text-align: center; color: #9ca3af;">
                        <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        <p class="mt-2 mb-0">Belum ada mutasi</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
