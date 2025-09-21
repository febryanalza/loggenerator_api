<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Logbook Template
        Schema::create('logbook_template', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Logbook Fields
        Schema::create('logbook_fields', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name', 100);
            $table->enum('data_type', ['teks', 'angka', 'gambar', 'tanggal', 'jam']);
            $table->uuid('template_id');
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('logbook_template')->onDelete('cascade');
            $table->index(['template_id']);
        });

        // Logbook Data
        Schema::create('logbook_datas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('template_id');
            $table->uuid('writer_id');
            $table->json('data');
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('logbook_template')->onDelete('cascade');
            $table->foreign('writer_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['template_id']);
            $table->index(['writer_id']);
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
