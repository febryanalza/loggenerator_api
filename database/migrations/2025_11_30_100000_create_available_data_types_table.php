<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('available_data_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique()->comment('Nama tipe data (contoh: text, number, date, gambar, etc.)');
            $table->string('description')->nullable()->comment('Deskripsi tipe data');
            $table->boolean('is_active')->default(true)->comment('Status aktif tipe data');
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_data_types');
    }
};
