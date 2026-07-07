<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('receipt_size')->default('80mm')->after('receipt_copies');
            $table->boolean('show_receipt_logo')->default(true)->after('receipt_size');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['receipt_size', 'show_receipt_logo']);
        });
    }
};
