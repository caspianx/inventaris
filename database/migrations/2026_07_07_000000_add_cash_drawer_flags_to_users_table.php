<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_cash_drawer')->default(false)->after('remember_token');
            $table->boolean('auto_open_cash_drawer')->default(false)->after('has_cash_drawer');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['has_cash_drawer', 'auto_open_cash_drawer']);
        });
    }
};
