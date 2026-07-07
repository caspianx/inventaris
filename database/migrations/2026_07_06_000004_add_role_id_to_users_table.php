<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email');
        });

        // Map existing enum roles to newly created roles table if present
        if (Schema::hasTable('roles')) {
            $mapping = DB::table('roles')->pluck('id', 'name')->all();

            foreach (DB::table('users')->get() as $user) {
                $roleName = $user->role ?? null;
                if ($roleName && isset($mapping[$roleName])) {
                    DB::table('users')->where('id', $user->id)->update(['role_id' => $mapping[$roleName]]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_id');
        });
    }
};
