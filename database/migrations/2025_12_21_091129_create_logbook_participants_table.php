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
        Schema::create('logbook_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->json('data')->comment('Participant details in JSON format');
            $table->integer('grade')->nullable()->comment('Grade from 1 to 100');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('template_id')
                  ->references('id')
                  ->on('logbook_template')
                  ->onDelete('cascade');

            // Index for faster queries
            $table->index('template_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_participants');
    }
};
