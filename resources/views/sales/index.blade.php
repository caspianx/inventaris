@extends('layouts.app')
@section('title', 'Riwayat Transaksi')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm card-stat">
            <div class="card-body">
                <div class="text-muted small">Transaksi Hari Ini</div>
                <div class="fs-4 fw-bold">{{ $todayCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm card-stat">
            <div class="card-body">
                <div class="text-muted small">Total Penjualan Hari Ini</div>
                <div class="fs-4 fw-bold">Rp {{ number_format($todayTotal, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @if(auth()->user()->canAccess('sales.create'))
        <div class="col-md-4 d-flex align-items-center justify-content-end">
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-lg"><i class="bi bi-cash-coin"></i> Transaksi Baru</a>
        </div>
    @endif
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="date" class="form-control" value="{{ request('date') }}" onchange="this.form.submit()">
            @if(request('date'))
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th class="text-end">Total</th>
                    <th>Metode Bayar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->user->name ?? '-' }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td><span class="badge bg-secondary text-uppercase">{{ $sale->payment_method }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('sales.show', $sale) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-receipt"></i> Struk</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $sales->links() }}</div>
</div>
@endsection
