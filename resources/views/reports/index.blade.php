@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Laporan Import & Export</h5>
        <p class="text-muted small mb-0">Import/Export data Master Barang, Kategori, dan Supplier dalam format CSV.</p>
    </div>
    <div class="card-body">
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
                            <a href="{{ route('reports.export', 'items') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export CSV</a>
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="collapse" data-bs-target="#import-items-card"><i class="bi bi-upload"></i> Import CSV</button>
                        </div>
                        <div class="collapse mt-3" id="import-items-card">
                            <form action="{{ route('reports.import', 'items') }}" method="POST" enctype="multipart/form-data">
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
                            <div class="badge bg-warning text-dark rounded-circle me-3" style="width:42px;height:42px;display:grid;place-items:center;">
                                <i class="bi bi-tags fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Kategori</h6>
                                <small class="text-muted">Export/Import daftar kategori</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('reports.export', 'categories') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export CSV</a>
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
                            <a href="{{ route('reports.export', 'suppliers') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export CSV</a>
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
                            <a href="{{ route('reports.export', 'purchase_orders') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download"></i> Export CSV</a>
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
