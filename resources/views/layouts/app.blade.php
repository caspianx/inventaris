<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ $storeSetting->name ?? 'Inventory App' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1e2a3a; }
        .sidebar a { color: #c9d3de; text-decoration: none; display: block; padding: .6rem 1rem; border-radius: 6px; }
        .sidebar a:hover, .sidebar a.active { background: #33445c; color: #fff; }
        .sidebar .brand { color: #fff; font-weight: 600; padding: 1rem; display: flex; align-items: center; gap: .55rem; }
        .sidebar .brand img { width: 30px; height: 30px; object-fit: contain; border-radius: 4px; background: #fff; padding: 2px; }
        .card-stat { border: none; border-radius: 10px; }
    </style>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar p-2" style="width: 240px;">
        <div class="brand">
            @if(!empty($storeSetting->logo_path))
                <img src="{{ asset($storeSetting->logo_path) }}" alt="Logo {{ $storeSetting->name }}">
            @else
                <i class="bi bi-box-seam"></i>
            @endif
            <span>{{ $storeSetting->name ?? 'Inventory App' }}</span>
        </div>
        @if(auth()->user()->canAccess('dashboard.view'))
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        @endif
        @if(auth()->user()->canAccess('items.view'))
            <a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="bi bi-box"></i> Master Barang</a>
        @endif
        @if(auth()->user()->canAccess('sales.view') || auth()->user()->canAccess('sales.create'))
            <a href="{{ auth()->user()->canAccess('sales.view') ? route('sales.index') : route('sales.create') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}"><i class="bi bi-cash-coin"></i> Kasir</a>
        @endif
        @if(auth()->user()->canAccess('stock_movements.view'))
            <a href="{{ route('stock-movements.index') }}" class="{{ request()->routeIs('stock-movements.*') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i> Stok Masuk/Keluar</a>
        @endif
        @if(auth()->user()->canAccess('categories.manage'))
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Kategori</a>
        @endif
        @if(auth()->user()->canAccess('suppliers.manage'))
            <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="bi bi-truck"></i> Supplier</a>
        @endif
        @if(auth()->user()->canAccess('store_settings.manage'))
            <a href="{{ route('store-settings.edit') }}" class="{{ request()->routeIs('store-settings.*') ? 'active' : '' }}"><i class="bi bi-shop"></i> Pengaturan Toko</a>
        @endif
        @if(auth()->user()->canAccess('purchase_orders.view'))
            <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> Purchase Order</a>
        @endif
        @if(auth()->user()->canAccess('users.manage'))
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Manajemen User</a>
        @endif
        @if(auth()->user()->canAccess('role_permissions.manage'))
            <a href="{{ route('role-permissions.edit') }}" class="{{ request()->routeIs('role-permissions.*') ? 'active' : '' }}"><i class="bi bi-shield-lock"></i> Akses Role</a>
        @endif
        @if(auth()->user()->canAccess('activity_logs.view'))
            <a href="{{ route('activity-logs.index') }}" class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}"><i class="bi bi-journal-text"></i> Riwayat Audit</a>
        @endif
        <hr class="text-secondary">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-outline-light w-100"><i class="bi bi-box-arrow-right"></i> Keluar</button>
        </form>
    </nav>

    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">@yield('title', 'Dashboard')</h4>
            <div class="text-muted">
                {{ auth()->user()->name }}
                <span class="badge bg-secondary text-uppercase">{{ auth()->user()->role }}</span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

        <footer class="text-center text-muted small mt-5 pt-3 border-top">
            &copy; {{ date('Y') }} {{ $storeSetting->name ?? 'Inventory App' }}. Seluruh hak cipta dilindungi.
        </footer>
    </main>
</div>

<!-- Modal konfirmasi modern, menggantikan popup confirm() bawaan browser -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width:56px;height:56px;">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                    </span>
                </div>
                <h6 class="mb-2">Konfirmasi Tindakan</h6>
                <p class="text-muted mb-0" id="confirmModalBody"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger px-4" id="confirmModalBtn">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script>
// Ganti popup confirm() bawaan browser dengan modal modern.
// Pakai: <form data-confirm="Pesan konfirmasi di sini..."> alih-alih onsubmit="return confirm(...)"
document.addEventListener('DOMContentLoaded', function () {
    const confirmModalEl = document.getElementById('confirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);
    const confirmModalBody = document.getElementById('confirmModalBody');
    let pendingForm = null;

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            pendingForm = form;
            confirmModalBody.textContent = form.dataset.confirm;
            confirmModal.show();
        });
    });

    document.getElementById('confirmModalBtn').addEventListener('click', function () {
        confirmModal.hide();
        if (pendingForm) {
            pendingForm.submit();
        }
    });
});
</script>
</body>
</html>
