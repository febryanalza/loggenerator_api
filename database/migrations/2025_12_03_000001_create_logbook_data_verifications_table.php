<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates a new table for storing logbook data verifications.
     * This allows multiple verifiers per logbook data entry.
     */
    public function up(): void
    {
        Schema::create('logbook_data_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('data_id');
            $table->uuid('verifier_id');
            $table->boolean('is_verified')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('data_id')
                ->references('id')
                ->on('logbook_datas')
                ->onDelete('cascade');

            $table->foreign('verifier_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes for performance
            $table->index(['data_id']);
            $table->index(['verifier_id']);
            $table->index(['data_id', 'verifier_id']);
            $table->index(['verified_at']);
            
            // Unique constraint - one verifier can only verify a data entry once
            $table->unique(['data_id', 'verifier_id'], 'unique_data_verifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_data_verifications');
    }
};
