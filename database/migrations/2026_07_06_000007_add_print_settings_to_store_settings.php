<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('default_printer')->nullable()->after('logo_path');
            $table->boolean('auto_print_receipt')->default(false)->after('default_printer');
            $table->unsignedTinyInteger('receipt_copies')->default(1)->after('auto_print_receipt');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['default_printer', 'auto_print_receipt', 'receipt_copies']);
        });
    }
};
