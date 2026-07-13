<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            // Consolidation migration: adds all missing receipt and cash drawer settings
            // Uses conditional checks to handle partial migrations gracefully
            
            // Receipt retention and display settings
            if (!Schema::hasColumn('store_settings', 'receipt_retention_days')) {
                $table->integer('receipt_retention_days')->default(30);
            }
            
            // Item display columns
            if (!Schema::hasColumn('store_settings', 'receipt_show_item_sku')) {
                $table->boolean('receipt_show_item_sku')->default(true);
            }
            if (!Schema::hasColumn('store_settings', 'receipt_show_item_quantity')) {
                $table->boolean('receipt_show_item_quantity')->default(true);
            }
            if (!Schema::hasColumn('store_settings', 'receipt_show_item_price')) {
                $table->boolean('receipt_show_item_price')->default(true);
            }
            if (!Schema::hasColumn('store_settings', 'receipt_show_item_subtotal')) {
                $table->boolean('receipt_show_item_subtotal')->default(true);
            }
            
            // Footer settings
            if (!Schema::hasColumn('store_settings', 'receipt_thank_you_text')) {
                $table->text('receipt_thank_you_text')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $cols = [
                'receipt_retention_days',
                'receipt_show_item_sku',
                'receipt_show_item_quantity',
                'receipt_show_item_price',
                'receipt_show_item_subtotal',
                'receipt_thank_you_text',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('store_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
