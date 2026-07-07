<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // machine name
            $table->string('label', 100)->nullable(); // human-friendly
            $table->timestamps();
        });

        // seed default roles
        if (Schema::hasTable('roles')) {
            DB::table('roles')->insert([
                ['name' => 'admin', 'label' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'manager', 'label' => 'Manager', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'staff', 'label' => 'Staff', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
