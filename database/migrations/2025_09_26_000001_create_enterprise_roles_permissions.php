<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * NOTE: This migration is DEPRECATED and replaced by:
     * - 2025_12_20_000001_add_granular_permissions.php (New permission system)
     * 
     * This file is kept for migration history compatibility only.
     * It no longer creates any roles or permissions.
     */
    public function up(): void
    {
        // Do nothing - this migration is deprecated
        // All permissions are now created by:
        // database/migrations/2025_12_20_000001_add_granular_permissions.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do nothing - permissions are managed by granular permissions migration
    }
};