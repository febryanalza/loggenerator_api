<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * DEPRECATED: This migration is no longer used.
     * Users are now seeded by database/seeders/UserSeeder.php
     * 
     * This file is kept for migration history purposes only.
     * All user creation is now handled by seeders which run after migrations.
     */
    public function up(): void
    {
        // Do nothing - this migration is deprecated
        // Users are now created by: database/seeders/UserSeeder.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do nothing - this migration is deprecated
    }
};
