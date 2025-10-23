<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds performance indexes to audit_logs table for faster queries.
     * 
     * Query patterns optimized:
     * - ORDER BY created_at DESC (recent activity)
     * - WHERE user_id = ? ORDER BY created_at DESC (user activity timeline)
     * - WHERE action = ? ORDER BY created_at DESC (action timeline)
     */
    public function up(): void
    {
        // Create indexes only if they don't exist (PostgreSQL syntax)
        
        // Single column index for sorting (most common query pattern)
        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_created_at_index ON audit_logs (created_at DESC)');
        
        // Composite index: user activity timeline
        // Speeds up: WHERE user_id = ? ORDER BY created_at DESC
        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_user_id_created_at_index ON audit_logs (user_id, created_at DESC)');
        
        // Composite index: action timeline
        // Speeds up: WHERE action = ? ORDER BY created_at DESC
        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_action_created_at_index ON audit_logs (action, created_at DESC)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes if they exist
        DB::statement('DROP INDEX IF EXISTS audit_logs_action_created_at_index');
        DB::statement('DROP INDEX IF EXISTS audit_logs_user_id_created_at_index');
        DB::statement('DROP INDEX IF EXISTS audit_logs_created_at_index');
    }
};
