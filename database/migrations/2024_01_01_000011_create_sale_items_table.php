<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            // restrict, bukan cascade: barang yang pernah terjual tidak boleh dihapus dari master (lihat ItemController::destroy)
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            // Snapshot nama & SKU saat transaksi, supaya struk lama tetap akurat
            // walau nama/SKU barang di master berubah di kemudian hari.
            $table->string('item_name');
            $table->string('item_sku');
            $table->decimal('price', 15, 2); // harga satuan saat transaksi (snapshot)
            $table->integer('quantity');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
