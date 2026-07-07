@extends('layouts.app')
@section('title', 'Stok Masuk/Keluar')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 1rem;">
            <div class="d-flex gap-2" style="flex-wrap: wrap;">
                <form method="GET" class="d-flex gap-2">
                    <select name="type" class="form-select" style="max-width: 150px;" onchange="this.form.submit()">
                        <option value="">📦 Semua Tipe</option>
                        <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Masuk</option>
                        <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Keluar</option>
                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                    <select name="item_id" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
                        <option value="">Semua Barang</option>
                        @foreach($items as $it)
                            <option value="{{ $it->id }}" {{ request('item_id') == $it->id ? 'selected' : '' }}>{{ $it->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            @if(auth()->user()->canAccess('stock_movements.create'))
                <a href="{{ route('stock-movements.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Catat Mutasi
                </a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Tanggal & Waktu</th>
                    <th>Barang</th>
                    <th>Tipe</th>
                    <th class="text-end">Qty</th>
                    <th>Referensi</th>
                    <th>Catatan</th>
                    <th>Operator</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $mv)
                    <tr>
                        <td>
                            <div>{{ $mv->created_at->format('d M Y') }}</div>
                            <small style="color: var(--gray-500);">{{ $mv->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <strong style="color: var(--primary);">{{ $mv->item->name }}</strong>
                        </td>
                        <td>
                            @if($mv->type === 'in')
                                <span class="badge bg-success"><i class="bi bi-arrow-down"></i> Masuk</span>
                            @elseif($mv->type === 'out')
                                <span class="badge bg-danger"><i class="bi bi-arrow-up"></i> Keluar</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-arrow-left-right"></i> Adjustment</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong>{{ $mv->quantity }}</strong>
                        </td>
                        <td>
                            <code style="background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $mv->reference_type }}</code>
                        </td>
                        <td>
                            <p class="mb-0" style="color: var(--gray-700); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $mv->notes ?: '-' }}</p>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $mv->user->name }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada mutasi stok</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $movements->links() }}
    </div>
</div>
@endsection
