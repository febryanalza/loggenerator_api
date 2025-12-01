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
        Schema::create('logbook_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->uuid('exported_by');
            $table->string('file_name', 255);
            $table->string('file_type', 50)->default('docx'); // docx, pdf, xlsx, csv
            $table->string('file_path', 500);
            $table->string('file_url', 500);
            $table->bigInteger('file_size')->default(0); // in bytes
            $table->integer('total_entries')->default(0);
            $table->integer('total_fields')->default(0);
            $table->string('status', 50)->default('completed'); // completed, failed, processing
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('expires_at')->nullable(); // for auto cleanup
            $table->timestamps();

            // Foreign keys
            $table->foreign('template_id')
                ->references('id')
                ->on('logbook_template')
                ->onDelete('cascade');

            $table->foreign('exported_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes
            $table->index('template_id');
            $table->index('exported_by');
            $table->index('status');
            $table->index('file_type');
            $table->index('created_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_exports');
    }
};
