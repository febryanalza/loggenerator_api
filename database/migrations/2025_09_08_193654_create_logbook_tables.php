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
        // Logbook Template
        Schema::create('logbook_template', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Logbook Fields
        Schema::create('logbook_fields', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name', 100);
            $table->enum('data_type', ['teks', 'angka', 'gambar', 'tanggal', 'jam']);
            $table->uuid('template_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('template_id')->references('id')->on('logbook_template')->onDelete('cascade');
            $table->index(['id', 'template_id']);
        });

        // Logbook Data
        Schema::create('logbook_datas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->unsignedBigInteger('writer_id');
            $table->json('data');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('template_id')->references('id')->on('logbook_template')->onDelete('cascade');
            $table->foreign('writer_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['id', 'template_id']);
            $table->index('writer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_datas');
        Schema::dropIfExists('logbook_fields');
        Schema::dropIfExists('logbook_template');
    }
};
