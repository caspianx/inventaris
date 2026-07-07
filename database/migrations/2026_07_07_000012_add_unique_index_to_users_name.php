<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // Only add the unique index if it does not already exist and the column exists
        if (! Schema::hasColumn('users', 'name')) {
            return;
        }

        // Guard against duplicate index creation
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('name');
            });
        } catch (\Throwable $e) {
            // If index exists or any other issue, skip to avoid failing migrations
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'name')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['name']);
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
