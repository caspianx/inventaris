<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('default_printer')->nullable();
            $table->boolean('auto_print_receipt')->default(false);
            $table->integer('receipt_copies')->default(1);
            $table->string('receipt_size')->default('80mm');
            $table->integer('receipt_retention_days')->default(30);
            $table->boolean('show_receipt_logo')->default(true);
            $table->string('receipt_header_title')->nullable();
            $table->string('receipt_header_subtitle')->nullable();
            $table->text('receipt_header_extra')->nullable();
            $table->boolean('receipt_show_invoice_number')->default(true);
            $table->boolean('receipt_show_date_time')->default(true);
            $table->boolean('receipt_show_cashier')->default(true);
            $table->string('receipt_cashier_label')->nullable();
            $table->boolean('receipt_show_table')->default(false);
            $table->string('receipt_table_label')->nullable();
            $table->boolean('receipt_show_tax_line')->default(false);
            $table->string('receipt_tax_label')->nullable();
            $table->decimal('receipt_tax_rate', 8, 2)->default(0);
            $table->boolean('receipt_show_payment_method')->default(true);
            $table->string('receipt_payment_label')->nullable();
            $table->string('receipt_change_label')->nullable();
            $table->boolean('receipt_show_item_sku')->default(true);
            $table->boolean('receipt_show_item_quantity')->default(true);
            $table->boolean('receipt_show_item_price')->default(true);
            $table->boolean('receipt_show_item_subtotal')->default(true);
            $table->text('receipt_thank_you_text')->nullable();
            $table->text('receipt_footer_note')->nullable();
            $table->string('cash_drawer_driver')->nullable();
            $table->string('cash_drawer_address')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $cols = [
                'default_printer', 'auto_print_receipt', 'receipt_copies', 'receipt_size', 'receipt_retention_days',
                'show_receipt_logo', 'receipt_header_title', 'receipt_header_subtitle', 'receipt_header_extra',
                'receipt_show_invoice_number', 'receipt_show_date_time', 'receipt_show_cashier', 'receipt_cashier_label',
                'receipt_show_table', 'receipt_table_label', 'receipt_show_tax_line', 'receipt_tax_label', 'receipt_tax_rate',
                'receipt_show_payment_method', 'receipt_payment_label', 'receipt_change_label', 'receipt_show_item_sku',
                'receipt_show_item_quantity', 'receipt_show_item_price', 'receipt_show_item_subtotal', 'receipt_thank_you_text',
                'receipt_footer_note', 'cash_drawer_driver', 'cash_drawer_address'
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('store_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
