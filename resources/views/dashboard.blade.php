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

<!-- INCOME TREND SECTION -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-graph-up-arrow"></i> Tren Pendapatan
        </div>
        <small class="text-muted">Memperbarui otomatis saat ada transaksi baru</small>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Pendapatan Harian</h6>
                            <small class="text-muted">Perbandingan hari sebelumnya</small>
                        </div>
                        <span id="dailyIncomeBadge" class="badge bg-success">{{ number_format($dailyIncome['deltaPercent'] ?? 0, 1) }}%</span>
                    </div>
                    <div id="dailyIncomeChart" class="d-flex align-items-end gap-2" style="height: 170px;"></div>
                    <div class="mt-3 d-flex justify-content-between small">
                        <span id="dailyIncomeCurrent">Hari ini: Rp {{ number_format($dailyIncome['currentValue'] ?? 0, 0, ',', '.') }}</span>
                        <span id="dailyIncomePrevious">Hari lalu: Rp {{ number_format($dailyIncome['previousValue'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Pendapatan Bulanan</h6>
                            <small class="text-muted">Perbandingan bulan sebelumnya</small>
                        </div>
                        <span id="monthlyIncomeBadge" class="badge bg-success">{{ number_format($monthlyIncome['deltaPercent'] ?? 0, 1) }}%</span>
                    </div>
                    <div id="monthlyIncomeChart" class="d-flex align-items-end gap-2" style="height: 170px;"></div>
                    <div class="mt-3 d-flex justify-content-between small">
                        <span id="monthlyIncomeCurrent">Bulan ini: Rp {{ number_format($monthlyIncome['currentValue'] ?? 0, 0, ',', '.') }}</span>
                        <span id="monthlyIncomePrevious">Bulan lalu: Rp {{ number_format($monthlyIncome['previousValue'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Pendapatan Tahunan</h6>
                            <small class="text-muted">Perbandingan tahun sebelumnya</small>
                        </div>
                        <span id="yearlyIncomeBadge" class="badge bg-success">{{ number_format($yearlyIncome['deltaPercent'] ?? 0, 1) }}%</span>
                    </div>
                    <div id="yearlyIncomeChart" class="d-flex align-items-end gap-2" style="height: 170px;"></div>
                    <div class="mt-3 d-flex justify-content-between small">
                        <span id="yearlyIncomeCurrent">Tahun ini: Rp {{ number_format($yearlyIncome['currentValue'] ?? 0, 0, ',', '.') }}</span>
                        <span id="yearlyIncomePrevious">Tahun lalu: Rp {{ number_format($yearlyIncome['previousValue'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const initialData = {
        daily: {!! json_encode($dailyIncome) !!},
        monthly: {!! json_encode($monthlyIncome) !!},
        yearly: {!! json_encode($yearlyIncome) !!},
    };
    
    function formatCurrency(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    function renderIncomeChart(containerId, data, color) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        container.innerHTML = '';
        const maxValue = data.maxValue > 0 ? data.maxValue : 1;

        data.series.forEach(point => {
            const heightPercent = data.maxValue > 0 ? Math.max(5, Math.round((point.value / maxValue) * 100)) : 5;
            const bar = document.createElement('div');
            bar.style.flexGrow = '1';
            bar.style.display = 'flex';
            bar.style.flexDirection = 'column';
            bar.style.alignItems = 'center';
            bar.style.minWidth = '0';
            bar.style.height = '100%';
            bar.style.gap = '0.5rem';
            
            // Bar visualization
            const barVisual = document.createElement('div');
            barVisual.style.width = '100%';
            barVisual.style.height = heightPercent + '%';
            barVisual.style.minHeight = '8px';
            barVisual.style.background = `linear-gradient(180deg, ${color} 0%, ${color}cc 100%)`;
            barVisual.style.borderRadius = '4px 4px 0 0';
            barVisual.style.marginTop = 'auto';
            
            // Label
            const label = document.createElement('div');
            label.style.fontSize = '0.75rem';
            label.style.color = '#6b7280';
            label.style.textAlign = 'center';
            label.style.whiteSpace = 'nowrap';
            label.style.overflow = 'hidden';
            label.style.textOverflow = 'ellipsis';
            label.style.width = '100%';
            label.textContent = point.label;
            
            bar.appendChild(barVisual);
            bar.appendChild(label);
            container.appendChild(bar);
        });
    }

    function renderAllCharts(data) {
        renderIncomeChart('dailyIncomeChart', data.daily, '#3b82f6');
        document.getElementById('dailyIncomeBadge').className = 'badge ' + (data.daily.delta >= 0 ? 'bg-success' : 'bg-danger');
        document.getElementById('dailyIncomeBadge').textContent = (data.daily.delta >= 0 ? '+' : '') + Number(data.daily.deltaPercent).toFixed(1) + '%';
        document.getElementById('dailyIncomeCurrent').textContent = 'Hari ini: ' + formatCurrency(data.daily.currentValue);
        document.getElementById('dailyIncomePrevious').textContent = 'Hari lalu: ' + formatCurrency(data.daily.previousValue);

        renderIncomeChart('monthlyIncomeChart', data.monthly, '#10b981');
        document.getElementById('monthlyIncomeBadge').className = 'badge ' + (data.monthly.delta >= 0 ? 'bg-success' : 'bg-danger');
        document.getElementById('monthlyIncomeBadge').textContent = (data.monthly.delta >= 0 ? '+' : '') + Number(data.monthly.deltaPercent).toFixed(1) + '%';
        document.getElementById('monthlyIncomeCurrent').textContent = 'Bulan ini: ' + formatCurrency(data.monthly.currentValue);
        document.getElementById('monthlyIncomePrevious').textContent = 'Bulan lalu: ' + formatCurrency(data.monthly.previousValue);

        renderIncomeChart('yearlyIncomeChart', data.yearly, '#f59e0b');
        document.getElementById('yearlyIncomeBadge').className = 'badge ' + (data.yearly.delta >= 0 ? 'bg-success' : 'bg-danger');
        document.getElementById('yearlyIncomeBadge').textContent = (data.yearly.delta >= 0 ? '+' : '') + Number(data.yearly.deltaPercent).toFixed(1) + '%';
        document.getElementById('yearlyIncomeCurrent').textContent = 'Tahun ini: ' + formatCurrency(data.yearly.currentValue);
        document.getElementById('yearlyIncomePrevious').textContent = 'Tahun lalu: ' + formatCurrency(data.yearly.previousValue);
    }

    function updateIncomeTrend() {
        fetch('{{ route('dashboard.income-trend-data') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                if (!response.ok) {
                    console.error('Income trend response error:', response.status);
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                
                console.log('[Income Trend] Data updated:', data);
                renderAllCharts(data);
            })
            .catch(error => {
                console.error('[Income Trend] Error:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        console.log('[Income Trend] Dashboard loaded, rendering initial data...');
        renderAllCharts(initialData);
        
        console.log('[Income Trend] Starting auto-update (5 second interval)...');
        setInterval(updateIncomeTrend, 5000);
    });
</script>

<!-- PROFIT & LOSS SUMMARY -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-graph-up-arrow"></i> Ringkasan Laba Rugi
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <div class="text-muted small mb-2">Total Pendapatan</div>
                    <div class="fs-5 fw-bold text-primary">Rp {{ number_format($profitLossSummary['totalRevenue'], 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <div class="text-muted small mb-2">Total Biaya</div>
                    <div class="fs-5 fw-bold text-warning">Rp {{ number_format($profitLossSummary['totalCost'], 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <div class="text-muted small mb-2">Laba Bersih</div>
                    <div class="fs-5 fw-bold {{ $profitLossSummary['totalProfit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($profitLossSummary['totalProfit'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <div class="text-muted small mb-2">Margin Laba</div>
                    <div class="fs-5 fw-bold {{ $profitLossSummary['profitMargin'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($profitLossSummary['profitMargin'], 2) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOP 10 ITEMS SOLD & MARGIN BY CATEGORY -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bag-check"></i> Top 10 Barang Terjual
            </div>
            <div class="card-body p-0">
                <div style="max-height: 350px; overflow-y: auto;">
                    @forelse($topItemsSold as $item)
                        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 600; color: #1f2937;">{{ $item['name'] }}</div>
                                <div style="font-size: 0.85rem; color: #6b7280;">Rp {{ number_format($item['sales'], 0, ',', '.') }}</div>
                            </div>
                            <div style="text-align: right; background: #f3f4f6; padding: 0.5rem 0.75rem; border-radius: 6px; font-weight: 600; color: #2563eb;">
                                {{ $item['quantity'] }} pcs
                            </div>
                        </div>
                    @empty
                        <div style="padding: 3rem 1.25rem; text-align: center; color: #9ca3af;">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            <p class="mt-2 mb-0">Belum ada data penjualan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-percent"></i> Margin Keuntungan per Kategori
            </div>
            <div class="card-body p-0">
                <div style="max-height: 350px; overflow-y: auto;">
                    @forelse($marginByCategory as $cat)
                        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb;">
                            <div class="d-flex justify-content-between small mb-2">
                                <span><strong>{{ $cat['category'] }}</strong></span>
                                <span class="text-muted">{{ $cat['items_count'] }} item</span>
                            </div>
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar {{ $cat['margin'] >= 20 ? 'bg-success' : ($cat['margin'] >= 10 ? 'bg-warning' : 'bg-danger') }}" 
                                     style="width: {{ min(100, $cat['margin'] * 5) }}%;">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Penjualan: Rp {{ number_format($cat['sales'], 0, ',', '.') }}</span>
                                <span class="fw-bold {{ $cat['margin'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($cat['margin'], 1) }}%</span>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 3rem 1.25rem; text-align: center; color: #9ca3af;">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            <p class="mt-2 mb-0">Belum ada data margin</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SUMMARY CHARTS SECTION -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart"></i> Distribusi Stok per Kategori
            </div>
            <div class="card-body">
                @forelse($stockCategoryChart as $item)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $item['label'] }}</span>
                            <span>{{ $item['value'] }} item / {{ $item['stock'] }} stok</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: {{ $item['value'] > 0 ? min(100, ($item['value'] / max(1, $stockCategoryChart->max('value'))) * 100) : 0 }}%; background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada data kategori.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-arrow-left-right"></i> Mutasi Stok 6 Bulan Terakhir
            </div>
            <div class="card-body">
                <div class="d-flex align-items-end gap-2" style="height: 190px;">
                    @foreach($stockMovementChart as $point)
                        @php $maxValue = max(1, collect($stockMovementChart)->max(fn ($item) => max($item['in'], $item['out']))); @endphp
                        @php $inHeight = max(8, round(($point['in'] / $maxValue) * 100)); @endphp
                        @php $outHeight = max(8, round(($point['out'] / $maxValue) * 100)); @endphp
                        <div class="flex-fill text-center">
                            <div class="d-flex align-items-end justify-content-center gap-1" style="height: 140px;">
                                <div class="rounded-top" style="width: 40%; height: {{ $inHeight }}%; min-height: 8px; background: #10b981;"></div>
                                <div class="rounded-top" style="width: 40%; height: {{ $outHeight }}%; min-height: 8px; background: #ef4444;"></div>
                            </div>
                            <div class="small mt-2 text-muted">{{ $point['label'] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between small mt-3 text-muted">
                    <span><span class="badge bg-success">Masuk</span></span>
                    <span><span class="badge bg-danger">Keluar</span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-clipboard2-data"></i> Status Purchase Order
            </div>
            <div class="card-body">
                @forelse($poStatusChart as $item)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $item['label'] }}</span>
                            <span>{{ $item['value'] }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: {{ $item['value'] > 0 ? min(100, ($item['value'] / max(1, $poStatusChart->max('value'))) * 100) : 0 }}%; background: linear-gradient(90deg, #f59e0b, #fb923c);"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada data PO.</div>
                @endforelse
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
