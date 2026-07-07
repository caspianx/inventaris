<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('cash_drawer_driver')->default('network')->after('receipt_footer_note');
            $table->string('cash_drawer_address')->nullable()->after('cash_drawer_driver');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['cash_drawer_driver', 'cash_drawer_address']);
        });
    }
};
