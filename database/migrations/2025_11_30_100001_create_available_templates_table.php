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
        Schema::create('available_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->comment('Nama template');
            $table->text('description')->nullable()->comment('Deskripsi template');
            $table->uuid('institution_id')->nullable()->comment('Institusi yang membuat template ini');
            $table->uuid('created_by')->nullable()->comment('User yang membuat template');
            $table->jsonb('required_columns')->comment('Array of columns dengan format: [{"name": "Nama Kegiatan", "data_type": "text"}, ...]');
            $table->boolean('is_active')->default(true)->comment('Status aktif template');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('institution_id')
                ->references('id')
                ->on('institutions')
                ->onDelete('cascade');
            
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            // Indexes
            $table->index('is_active');
            $table->index('institution_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_templates');
    }
};
