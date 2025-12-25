<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table structure for logbook participants.
     * The 'data' column stores participant information in JSON format based on 
     * the institution's required_data_participants configuration.
     * 
     * Example data format:
     * {
     *   "Nama Lengkap": "John Doe",
     *   "NIM": "12345678", 
     *   "Email": "john@example.com",
     *   "Nomor Telepon": "08123456789"
     * }
     * 
     * The keys are taken from required_data_participants.data_name for the institution.
     * The number of fields varies depending on institution configuration.
     */
    public function up(): void
    {
        Schema::create('logbook_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->json('data')->comment('Participant details in JSON format - keys from required_data_participants.data_name');
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
