<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Inventory App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1e2a3a; }
        .sidebar a { color: #c9d3de; text-decoration: none; display: block; padding: .6rem 1rem; border-radius: 6px; }
        .sidebar a:hover, .sidebar a.active { background: #33445c; color: #fff; }
        .sidebar .brand { color: #fff; font-weight: 600; padding: 1rem; }
        .card-stat { border: none; border-radius: 10px; }
    </style>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar p-2" style="width: 240px;">
        <div class="brand"><i class="bi bi-box-seam"></i> Inventory App</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="bi bi-box"></i> Master Barang</a>
        <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}"><i class="bi bi-cash-coin"></i> Kasir</a>
        <a href="{{ route('stock-movements.index') }}" class="{{ request()->routeIs('stock-movements.*') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i> Stok Masuk/Keluar</a>
        @if(auth()->user()->role !== 'staff')
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Kategori</a>
            <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="bi bi-truck"></i> Supplier</a>
            <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> Purchase Order</a>
        @endif
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Manajemen User</a>
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
            &copy; {{ date('Y') }} Inventory App. Seluruh hak cipta dilindungi.
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
