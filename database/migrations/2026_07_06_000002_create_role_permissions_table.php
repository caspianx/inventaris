<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role', 50);
            $table->string('permission', 100);
            $table->timestamps();

            $table->unique(['role', 'permission']);
        });

        $now = now();
        $permissions = [
            'admin' => [
                'dashboard.view',
                'items.view',
                'items.create',
                'items.edit',
                'items.delete',
                'items.print_barcode',
                'sales.view',
                'sales.create',
                'stock_movements.view',
                'stock_movements.create',
                'categories.manage',
                'suppliers.manage',
                'purchase_orders.view',
                'purchase_orders.create',
                'purchase_orders.update_status',
                'store_settings.manage',
                'users.manage',
                'role_permissions.manage',
            ],
            'manager' => [
                'dashboard.view',
                'items.view',
                'items.create',
                'items.edit',
                'items.delete',
                'items.print_barcode',
                'sales.view',
                'sales.create',
                'stock_movements.view',
                'stock_movements.create',
                'categories.manage',
                'suppliers.manage',
                'purchase_orders.view',
                'purchase_orders.create',
                'purchase_orders.update_status',
                'purchase_orders.delete',
                'store_settings.manage',
            ],
            'staff' => [
                'dashboard.view',
                'items.view',
                'items.create',
                'items.print_barcode',
                'sales.view',
                'sales.create',
                'stock_movements.view',
                'stock_movements.create',
            ],
        ];

        foreach ($permissions as $role => $rolePermissions) {
            foreach ($rolePermissions as $permission) {
                DB::table('role_permissions')->insert([
                    'role' => $role,
                    'permission' => $permission,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
