<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('receipt_header_title')->nullable()->after('show_receipt_logo');
            $table->string('receipt_header_subtitle')->nullable()->after('receipt_header_title');
            $table->text('receipt_header_extra')->nullable()->after('receipt_header_subtitle');
            $table->boolean('receipt_show_invoice_number')->default(true)->after('receipt_header_extra');
            $table->boolean('receipt_show_date_time')->default(true)->after('receipt_show_invoice_number');
            $table->boolean('receipt_show_cashier')->default(true)->after('receipt_show_date_time');
            $table->string('receipt_cashier_label')->default('Kasir')->after('receipt_show_cashier');
            $table->boolean('receipt_show_table')->default(false)->after('receipt_cashier_label');
            $table->string('receipt_table_label')->default('Tabel')->after('receipt_show_table');
            $table->boolean('receipt_show_tax_line')->default(false)->after('receipt_table_label');
            $table->string('receipt_tax_label')->default('Pajak')->after('receipt_show_tax_line');
            $table->decimal('receipt_tax_rate', 5, 2)->default(0)->after('receipt_tax_label');
            $table->boolean('receipt_show_payment_method')->default(true)->after('receipt_tax_rate');
            $table->string('receipt_payment_label')->default('Bayar')->after('receipt_show_payment_method');
            $table->string('receipt_change_label')->default('Kembalian')->after('receipt_payment_label');
            $table->boolean('receipt_show_item_sku')->default(true)->after('receipt_change_label');
            $table->boolean('receipt_show_item_quantity')->default(true)->after('receipt_show_item_sku');
            $table->boolean('receipt_show_item_price')->default(true)->after('receipt_show_item_quantity');
            $table->boolean('receipt_show_item_subtotal')->default(true)->after('receipt_show_item_price');
            $table->string('receipt_thank_you_text')->default('Terima kasih atas kunjungan Anda!')->after('receipt_show_item_subtotal');
            $table->text('receipt_footer_note')->nullable()->after('receipt_thank_you_text');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_header_title',
                'receipt_header_subtitle',
                'receipt_header_extra',
                'receipt_show_invoice_number',
                'receipt_show_date_time',
                'receipt_show_cashier',
                'receipt_cashier_label',
                'receipt_show_table',
                'receipt_table_label',
                'receipt_show_tax_line',
                'receipt_tax_label',
                'receipt_tax_rate',
                'receipt_show_payment_method',
                'receipt_payment_label',
                'receipt_change_label',
                'receipt_thank_you_text',
                'receipt_footer_note',
            ]);
        });
    }
};
