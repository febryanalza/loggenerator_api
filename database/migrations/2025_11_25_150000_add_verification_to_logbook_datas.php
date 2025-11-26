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
        Schema::table('logbook_datas', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('data');
            $table->uuid('verified_by')->nullable()->after('is_verified');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verified_at');
            
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['is_verified', 'template_id']);
            $table->index(['verified_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbook_datas', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropIndex(['is_verified', 'template_id']);
            $table->dropIndex(['verified_by']);
            $table->dropColumn(['is_verified', 'verified_by', 'verified_at', 'verification_notes']);
        });
    }
};