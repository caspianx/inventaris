@extends('layouts.app')
@section('title', 'Stok Masuk/Keluar')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form class="d-flex gap-2" method="GET">
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Masuk</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Keluar</option>
                <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
            </select>
            <select name="item_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Barang</option>
                @foreach($items as $it)
                    <option value="{{ $it->id }}" {{ request('item_id') == $it->id ? 'selected' : '' }}>{{ $it->name }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('stock-movements.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Catat Mutasi</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Tanggal</th><th>Barang</th><th>Tipe</th><th class="text-end">Qty</th><th>Referensi</th><th>Catatan</th><th>Oleh</th></tr></thead>
            <tbody>
                @forelse($movements as $mv)
                    <tr>
                        <td>{{ $mv->created_at->format('d/m/Y H:i') }}</td>
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
                        <td>{{ $mv->reference_type }}</td>
                        <td>{{ $mv->notes }}</td>
                        <td>{{ $mv->user->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada mutasi stok</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $movements->links() }}</div>
</div>
@endsection
