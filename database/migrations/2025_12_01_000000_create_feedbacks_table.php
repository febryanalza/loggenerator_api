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
        Schema::create('feedbacks', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();
            
            // Status & Priority
            $table->enum('status', ['new', 'in_progress', 'resolved', 'closed', 'rejected'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->nullable();
            
            // Feedback Content
            $table->string('subject', 255); // Judul singkat feedback (required)
            $table->text('description')->nullable(); // Isi detail feedback
            $table->enum('category', ['bug', 'feature_request', 'complaint', 'suggestion', 'question'])->nullable();
            $table->jsonb('questionnaire_responses')->nullable()->comment('Jawaban kuesioner tambahan dalam format JSON');
            
            // Rating (1-5)
            $table->unsignedTinyInteger('rating')->nullable()->comment('Rating 1-5');
            
            // Foreign Keys
            $table->uuid('user_id')->nullable(); // User yang membuat feedback (nullable untuk guest)
            $table->uuid('assigned_to')->nullable(); // Admin/Staff yang menangani
            $table->uuid('resolved_by')->nullable(); // Admin yang menyelesaikan
            
            // Response
            $table->text('response')->nullable(); // Balasan dari admin
            $table->timestamp('response_at')->nullable(); // Waktu balasan diberikan
            
            // Timestamps
            $table->timestamps(); // created_at & updated_at
            $table->timestamp('resolved_at')->nullable(); // Waktu feedback diselesaikan
            $table->timestamp('closed_at')->nullable(); // Waktu feedback ditutup
            
            // Foreign Key Constraints
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('resolved_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Indexes for better query performance
            $table->index('status');
            $table->index('priority');
            $table->index('category');
            $table->index('user_id');
            $table->index('assigned_to');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
