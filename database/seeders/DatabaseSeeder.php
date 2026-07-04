<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@inventory.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Staff Gudang',
            'email' => 'staff@inventory.test',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        $elektronik = Category::create(['name' => 'Elektronik', 'description' => 'Perangkat elektronik']);
        $atk = Category::create(['name' => 'ATK', 'description' => 'Alat tulis kantor']);

        $supplier = Supplier::create([
            'name' => 'PT Sumber Makmur',
            'contact_person' => 'Budi Santoso',
            'phone' => '021-5550001',
            'email' => 'sales@sumbermakmur.co.id',
            'address' => 'Jl. Industri No. 10, Jakarta',
        ]);

        Item::create([
            'sku' => 'ELK-001',
            'name' => 'Mouse Wireless',
            'category_id' => $elektronik->id,
            'unit' => 'pcs',
            'purchase_price' => 45000,
            'selling_price' => 65000,
            'min_stock' => 10,
            'current_stock' => 25,
        ]);

        Item::create([
            'sku' => 'ATK-001',
            'name' => 'Kertas A4 80gr',
            'category_id' => $atk->id,
            'unit' => 'rim',
            'purchase_price' => 42000,
            'selling_price' => 55000,
            'min_stock' => 20,
            'current_stock' => 8,
        ]);

        unset($supplier);
    }
}
