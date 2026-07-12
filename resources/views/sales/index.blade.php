@extends('layouts.app')
@section('title', 'Riwayat Transaksi')

@section('content')
<!-- STAT CARDS -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">📅 Transaksi Hari Ini</div>
                        <div class="fs-3 fw-bold mt-2" style="color: var(--primary);">{{ $todayCount }}</div>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(99, 102, 241, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-calendar3" style="font-size: 1.5rem; color: var(--primary);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">💰 Total Hari Ini</div>
                        <div class="fs-4 fw-bold mt-2" style="color: var(--success);">Rp {{ number_format($todayTotal, 0, ',', '.') }}</div>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-cash-coin" style="font-size: 1.5rem; color: var(--success);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->canAccess('sales.create'))
        <div class="col-md-12 col-lg-6 d-flex align-items-center justify-content-end">
            <a href="{{ route('sales.create') }}" class="btn btn-primary" style="padding: 0.875rem 2rem; font-size: 1.1rem;">
                <i class="bi bi-cash-coin"></i> Buat Transaksi Baru
            </a>
        </div>
    @endif
</div>

<!-- TABEL TRANSAKSI -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <form method="GET" class="d-flex gap-2">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}" onchange="this.form.submit()">
                    @if(request('date'))
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Hapus Filter
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Tanggal & Waktu</th>
                    <th>Kasir</th>
                    <th>Metode Pembayaran</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>
                            <strong style="color: var(--primary);">{{ $sale->invoice_number }}</strong>
                        </td>
                        <td>
                            <div>{{ $sale->created_at->format('d M Y') }}</div>
                            <small style="color: var(--gray-500);">{{ $sale->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $sale->user->name ?? '-' }}</div>
                        </td>
                        <td>
                            @php
                                $methods = [
                                    'cash' => ['badge' => 'bg-success', 'label' => '💵 Tunai', 'icon' => 'cash-coin'],
                                    'qris' => ['badge' => 'bg-info', 'label' => '📱 QRIS', 'icon' => 'qr-code'],
                                    'debit' => ['badge' => 'bg-warning', 'label' => '🏦 Debit', 'icon' => 'credit-card'],
                                    'credit' => ['badge' => 'bg-secondary', 'label' => '💳 Kredit', 'icon' => 'credit-card'],
                                ];
                                $method = $methods[strtolower($sale->payment_method)] ?? ['badge' => 'bg-secondary', 'label' => $sale->payment_method, 'icon' => 'question-circle'];
                            @endphp
                            <span class="badge {{ $method['badge'] }}">{{ $method['label'] }}</span>
                        </td>
                        <td class="text-end">
                            <strong style="font-size: 1.1rem; color: var(--primary);">Rp {{ number_format($sale->total, 0, ',', '.') }}</strong>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-sale-detail" data-sale-id="{{ $sale->id }}">
                                    <i class="bi bi-info-circle"></i> Detail
                                </button>
                                <a href="{{ route('sales.show', $sale) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-receipt"></i> Lihat Struk
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; color: var(--gray-300); display: block; margin-bottom: 1rem;"></i>
                            <div style="color: var(--gray-500); font-size: 1.1rem;">Belum ada transaksi</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $sales->links() }}
    </div>
</div>

<!-- Modal detail transaksi -->
<div class="modal fade" id="saleDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="saleDetailModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-3">Memuat detail transaksi...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const detailButtons = document.querySelectorAll('.btn-sale-detail');
        const detailModalEl = document.getElementById('saleDetailModal');
        const detailModalBody = document.getElementById('saleDetailModalBody');
        const detailModal = new bootstrap.Modal(detailModalEl);

        detailButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const saleId = button.dataset.saleId;
                if (!saleId) {
                    return;
                }

                detailModalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-3">Memuat detail transaksi...</div></div>';
                detailModal.show();

                fetch(`{{ url('sales') }}/${saleId}/detail`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Gagal memuat detail transaksi.');
                        }
                        return response.text();
                    })
                    .then(function (html) {
                        detailModalBody.innerHTML = html;
                    })
                    .catch(function (error) {
                        detailModalBody.innerHTML = '<div class="alert alert-danger">' + error.message + '</div>';
                    });
            });
        });
    });
</script>
@endsection
