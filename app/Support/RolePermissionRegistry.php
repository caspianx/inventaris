<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Role;
use Illuminate\Support\Facades\Schema;

class RolePermissionRegistry
{
    /**
     * Get all available roles in the application.
     *
     * @return array<string, string> Associative array of role names to labels
     */
    public static function roles(): array
    {
        // Prefer roles from database if available, otherwise fallback to static list.
        try {
            if (Schema::hasTable('roles')) {
                return Role::orderBy('name')->get()->mapWithKeys(fn ($r) => [$r->name => ($r->label ?: ucfirst($r->name))])->all();
            }
        } catch (\Throwable $e) {
            // ignore and fallback
        }

        return [
            'admin' => 'Admin',
            'manager' => 'Manager',
            'staff' => 'Staff',
        ];
    }

    public static function groups(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view' => 'Lihat dashboard',
            ],
            'Master Barang' => [
                'items.view' => 'Lihat master barang',
                'items.create' => 'Tambah barang',
                'items.edit' => 'Ubah barang',
                'items.delete' => 'Hapus barang',
                'items.print_barcode' => 'Cetak barcode barang',
            ],
            'Kasir / Penjualan' => [
                'sales.view' => 'Lihat riwayat penjualan',
                'sales.create' => 'Buat transaksi penjualan',
            ],
            'Stok Masuk/Keluar' => [
                'stock_movements.view' => 'Lihat mutasi stok',
                'stock_movements.create' => 'Catat mutasi stok',
            ],
            'Data Master' => [
                'categories.manage' => 'Kelola kategori',
                'suppliers.manage' => 'Kelola supplier',
            ],
            'Laporan' => [
                'reports.view' => 'Akses menu laporan import/export',
            ],
            'Purchase Order' => [
                'purchase_orders.view' => 'Lihat purchase order',
                'purchase_orders.create' => 'Buat purchase order',
                'purchase_orders.update_status' => 'Ubah status purchase order',
                'purchase_orders.delete' => 'Hapus purchase order',
            ],
            'Pengaturan' => [
                'store_settings.manage' => 'Kelola profil toko',
                'users.manage' => 'Kelola user',
                'role_permissions.manage' => 'Kelola akses role',
                'activity_logs.view' => 'Lihat riwayat audit',
            ],
        ];
    }

    public static function all(): array
    {
        return collect(static::groups())
            ->flatMap(fn (array $permissions) => array_keys($permissions))
            ->values()
            ->all();
    }
}
