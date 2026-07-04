<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Semua role login bisa lihat & catat mutasi stok
    Route::resource('stock-movements', StockMovementController::class)
        ->only(['index', 'create', 'store'])
        ->parameters(['stock-movements' => 'stock_movement']);

    Route::get('/items-check-duplicate', [ItemController::class, 'checkDuplicate'])->name('items.check-duplicate');
    Route::get('/items-autocomplete', [ItemController::class, 'autocomplete'])->name('items.autocomplete');
    Route::get('/items-print-barcode', [ItemController::class, 'printBarcode'])->name('items.print-barcode');
    Route::get('/items-pos-search', [ItemController::class, 'posSearch'])->name('items.pos-search');

    // Kasir: semua role login (termasuk staff) bisa melayani transaksi penjualan
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);

    // Semua role bisa lihat, cari, dan tambah barang
    Route::resource('items', ItemController::class)->only(['index', 'create', 'store']);

    // Kategori, supplier, PO, dan penghapusan hanya untuk admin & manager
    Route::middleware('role:admin,manager')->group(function () {
        // Ubah & hapus data barang dibatasi admin/manager (bukan staff), konsisten dgn master data lain
        Route::resource('items', ItemController::class)->only(['edit', 'update', 'destroy']);

        Route::get('/categories-autocomplete', [CategoryController::class, 'autocomplete'])->name('categories.autocomplete');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('/suppliers-autocomplete', [SupplierController::class, 'autocomplete'])->name('suppliers.autocomplete');
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::resource('purchase-orders', PurchaseOrderController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['purchase-orders' => 'purchase_order']);
        Route::patch('/purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])
            ->name('purchase-orders.status');
        // Hapus PO hanya untuk role manager (bukan admin/staff)
        Route::delete('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])
            ->name('purchase-orders.destroy')
            ->middleware('role:manager');
    });

    // Manajemen User HANYA untuk admin (manager tidak boleh, supaya tidak bisa menaikkan role sendiri jadi admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users-autocomplete', [UserController::class, 'autocomplete'])->name('users.autocomplete');
        Route::resource('users', UserController::class)->except(['show']);
    });
});
