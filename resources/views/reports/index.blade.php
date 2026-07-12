@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Laporan</h5>
        <p class="text-muted small mb-0">Lihat laporan pendapatan, pengeluaran, dan ekspor/import data master.</p>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Jenis Laporan</label>
                    <select name="report" class="form-select" onchange="this.form.submit()">
                        <option value="income" {{ request('report', 'income') === 'income' ? 'selected' : '' }}>Pendapatan</option>
                        <option value="expense" {{ request('report', 'income') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Periode</label>
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="day" {{ request('period', 'day') === 'day' ? 'selected' : '' }}>Harian</option>
                        <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Bulanan</option>
                        <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date', now()->format('Y-m-d')) }}" onchange="this.form.submit()">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Bulan</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month', now()->format('Y-m')) }}" onchange="this.form.submit()">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="year" class="form-control" min="2000" max="2099" value="{{ request('year', now()->format('Y')) }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>

        @if($financialReports !== null)
            <div class="mb-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                    <div>
                        <h6 class="mb-1">{{ $financialTitle }}</h6>
                        <p class="text-muted small mb-0">Tampilkan data berdasarkan periode yang dipilih.</p>
                    </div>
                    <div class="text-end">
                        <div class="mb-1 text-muted small">
                            {{ $financialTableType === 'income' ? 'Pendapatan Bersih' : 'Total Pengeluaran' }}
                        </div>
                        <div class="fs-4 fw-bold">Rp {{ number_format($financialTotal, 0, ',', '.') }}</div>
                    </div>
                </div>
                @if($financialTableType === 'income')
                    <div class="row g-2 mt-3">
                        <div class="col-12 col-sm-4">
                            <div class="card bg-light border">
                                <div class="card-body py-2">
                                    <div class="text-muted small">Total Pendapatan</div>
                                    <div class="fw-bold">Rp {{ number_format($financialSummary['revenue'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="card bg-light border">
                                <div class="card-body py-2">
                                    <div class="text-muted small">Total Biaya</div>
                                    <div class="fw-bold">Rp {{ number_format($financialSummary['cost'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="card bg-light border">
                                <div class="card-body py-2">
                                    <div class="text-muted small">Margin</div>
                                    <div class="fw-bold">{{ number_format($financialSummary['margin'], 2, ',', '.') }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="mt-3">
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#reportTableCollapse" aria-expanded="false">
                        <i class="bi bi-table"></i> {{ count($financialReports) > 0 ? 'Tampilkan Tabel (' . count($financialReports) . ' data)' : 'Tampilkan Tabel' }}
                    </button>
                    <a href="{{ route('reports.export', array_merge(['type' => $reportType], request()->only(['period','date','month','year']))) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-download"></i> Export Excel
                    </a>
                </div>
            </div>

            <!-- COLLAPSIBLE TABLE SECTION -->
            <div class="collapse mb-4" id="reportTableCollapse">
                <div class="card card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered mb-0">
                            <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                                <tr>
                                    @if($financialTableType === 'income')
                                        <th>No. Invoice</th>
                                        <th>Tanggal</th>
                                        <th>Kasir</th>
                                        <th>Metode Pembayaran</th>
                                        <th class="text-end">Pendapatan Bersih</th>
                                        <th>Catatan</th>
                                    @else
                                        <th>No. PO</th>
                                        <th>Tanggal Order</th>
                                        <th>Supplier</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
                                        <th>Catatan</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($financialReports as $report)
                                    <tr>
                                        @if($financialTableType === 'income')
                                            <td>{{ $report->invoice_number }}</td>
                                            <td>{{ $report->created_at->format('d M Y H:i') }}</td>
                                            <td>{{ $report->user->name ?? '-' }}</td>
                                            <td>{{ ucfirst($report->payment_method) }}</td>
                                            <td class="text-end">Rp {{ number_format($report->items->sum(function ($item) { return ($item->price - ($item->item?->purchase_price ?? 0)) * $item->quantity; }), 0, ',', '.') }}</td>
                                            <td>{{ $report->notes }}</td>
                                        @else
                                            <td>{{ $report->po_number }}</td>
                                            <td>{{ optional($report->order_date)->format('d M Y') }}</td>
                                            <td>{{ $report->supplier->name ?? '-' }}</td>
                                            <td>{{ ucfirst($report->status) }}</td>
                                            <td class="text-end">Rp {{ number_format($report->total_amount, 0, ',', '.') }}</td>
                                            <td>{{ $report->notes }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Tidak ada data untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-secondary">Pilih laporan pendapatan atau pengeluaran dan periode untuk melihat data.</div>
        @endif

        <div class="row gy-4">
            <div class="col-md-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge bg-primary rounded-circle me-3" style="width:42px;height:42px;display:grid;place-items:center;">
                                <i class="bi bi-box-seam fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Master Barang</h6>
                                <small class="text-muted">Export/Import data barang</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('reports.export', 'items') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export Excel</a>
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="collapse" data-bs-target="#import-items-card"><i class="bi bi-upload"></i> Import Excel/CSV</button>
                        </div>
                        <div class="collapse mt-3" id="import-items-card">
                            <form action="{{ route('reports.import', 'items') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Pilih file Excel atau CSV</label>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv,text/csv" class="form-control form-control-sm" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-upload"></i> Unggah dan Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge bg-warning text-dark rounded-circle me-3" style="width:42px;height:42px;display:grid;place-items:center;">
                                <i class="bi bi-tags fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Kategori</h6>
                                <small class="text-muted">Export/Import daftar kategori</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('reports.export', 'categories') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export Excel</a>
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="collapse" data-bs-target="#import-categories-card"><i class="bi bi-upload"></i> Import CSV</button>
                        </div>
                        <div class="collapse mt-3" id="import-categories-card">
                            <form action="{{ route('reports.import', 'categories') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Pilih file CSV</label>
                                    <input type="file" name="file" accept=".csv,text/csv" class="form-control form-control-sm" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-upload"></i> Unggah dan Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge bg-success rounded-circle me-3" style="width:42px;height:42px;display:grid;place-items:center;">
                                <i class="bi bi-truck fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Supplier</h6>
                                <small class="text-muted">Export/Import data supplier</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('reports.export', 'suppliers') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export Excel</a>
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="collapse" data-bs-target="#import-suppliers-card"><i class="bi bi-upload"></i> Import CSV</button>
                        </div>
                        <div class="collapse mt-3" id="import-suppliers-card">
                            <form action="{{ route('reports.import', 'suppliers') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Pilih file CSV</label>
                                    <input type="file" name="file" accept=".csv,text/csv" class="form-control form-control-sm" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-upload"></i> Unggah dan Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge bg-info text-dark rounded-circle me-3" style="width:42px;height:42px;display:grid;place-items:center;">
                                <i class="bi bi-file-earmark-text fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Purchase Order</h6>
                                <small class="text-muted">Export laporan purchase order</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('reports.export', 'purchase_orders') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export Excel</a>
                        </div>
                        <div class="mt-3 text-muted small">
                            <p class="mb-1">Data PO akan tervalidasi ke file CSV.</p>
                            <p class="mb-0">Import PO belum tersedia di fitur ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
