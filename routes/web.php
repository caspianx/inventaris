<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PrintFileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StoreSettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
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

    Route::view('/no-access', 'no_access')->name('no-access');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')
        ->middleware('permission:dashboard.view');

    Route::resource('stock-movements', StockMovementController::class)
        ->only(['index', 'create', 'store'])
        ->parameters(['stock-movements' => 'stock_movement'])
        ->middlewareFor('index', 'permission:stock_movements.view')
        ->middlewareFor(['create', 'store'], 'permission:stock_movements.create');

    Route::get('/items-check-duplicate', [ItemController::class, 'checkDuplicate'])
        ->name('items.check-duplicate')
        ->middleware('permission:items.create,items.edit');
    Route::get('/items-autocomplete', [ItemController::class, 'autocomplete'])
        ->name('items.autocomplete')
        ->middleware('permission:items.view');
    Route::get('/items-print-barcode', [ItemController::class, 'printBarcode'])
        ->name('items.print-barcode')
        ->middleware('permission:items.print_barcode');
    Route::get('/items-pos-search', [ItemController::class, 'posSearch'])
        ->name('items.pos-search')
        ->middleware('permission:sales.create');

    Route::resource('sales', SaleController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middlewareFor('index', 'permission:sales.view')
        ->middlewareFor(['create', 'store'], 'permission:sales.create')
        ->middlewareFor('show', 'permission:sales.view,sales.create');

    Route::resource('items', ItemController::class)
        ->only(['index', 'create', 'store'])
        ->middlewareFor('index', 'permission:items.view')
        ->middlewareFor(['create', 'store'], 'permission:items.create');

    Route::resource('items', ItemController::class)
        ->only(['edit', 'update', 'destroy'])
        ->middlewareFor(['edit', 'update'], 'permission:items.edit')
        ->middlewareFor('destroy', 'permission:items.delete');

    Route::get('/categories-autocomplete', [CategoryController::class, 'autocomplete'])
        ->name('categories.autocomplete')
        ->middleware('permission:categories.manage');
    Route::resource('categories', CategoryController::class)
        ->except(['show'])
        ->middleware('permission:categories.manage');
    Route::get('/suppliers-autocomplete', [SupplierController::class, 'autocomplete'])
        ->name('suppliers.autocomplete')
        ->middleware('permission:suppliers.manage');
    Route::resource('suppliers', SupplierController::class)
        ->except(['show'])
        ->middleware('permission:suppliers.manage');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index')
        ->middleware('permission:reports.view');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])
        ->name('reports.export')
        ->middleware('permission:reports.view');
    Route::post('/reports/import/{type}', [ReportController::class, 'import'])
        ->name('reports.import')
        ->middleware('permission:reports.view');

    Route::get('/store-settings', [StoreSettingController::class, 'edit'])
        ->name('store-settings.edit')
        ->middleware('permission:store_settings.manage');
    Route::put('/store-settings', [StoreSettingController::class, 'update'])
        ->name('store-settings.update')
        ->middleware('permission:store_settings.manage');
    Route::post('/store-settings/simulate-print', [StoreSettingController::class, 'simulatePrint'])
        ->name('store-settings.simulate-print')
        ->middleware('permission:store_settings.manage');
    Route::get('/print-files', [PrintFileController::class, 'index'])
        ->name('print-files.index')
        ->middleware('permission:store_settings.manage');
    Route::get('/print-files/download/{filename}', [PrintFileController::class, 'download'])
        ->where('filename', '.*')
        ->name('print-files.download')
        ->middleware('permission:store_settings.manage');
    Route::get('/print-files/reprint/{sale}', [PrintFileController::class, 'reprint'])
        ->name('print-files.reprint')
        ->middleware('permission:store_settings.manage');
    Route::delete('/print-files/{print_file}', [PrintFileController::class, 'destroy'])
        ->name('print-files.destroy')
        ->middleware('permission:store_settings.manage');
    Route::post('/print-files/bulk-delete', [PrintFileController::class, 'bulkDestroy'])
        ->name('print-files.bulk-delete')
        ->middleware('permission:store_settings.manage');
    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['purchase-orders' => 'purchase_order'])
        ->middlewareFor(['index', 'show'], 'permission:purchase_orders.view')
        ->middlewareFor(['create', 'store'], 'permission:purchase_orders.create');
    Route::patch('/purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])
        ->name('purchase-orders.status')
        ->middleware('permission:purchase_orders.update_status');
    Route::delete('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])
        ->name('purchase-orders.destroy')
        ->middleware('permission:purchase_orders.delete');

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users-autocomplete', [UserController::class, 'autocomplete'])->name('users.autocomplete');
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/role-permissions', [RolePermissionController::class, 'edit'])->name('role-permissions.edit');
        Route::put('/role-permissions', [RolePermissionController::class, 'update'])->name('role-permissions.update');
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index')
        ->middleware('permission:activity_logs.view');
});
