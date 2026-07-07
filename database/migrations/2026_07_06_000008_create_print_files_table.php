<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->unsignedInteger('print_count')->default(1);
            $table->string('printer_name')->nullable();
            $table->timestamp('last_printed_at')->nullable();
            $table->timestamps();
            $table->unique('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_files');
    }
};
