<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Note: Roles and Permissions are managed by migration:
     * - 2025_12_20_000001_add_granular_permissions.php
     * 
     * This seeder only handles:
     * - Creating basic roles (if not exists)
     * - Logbook-specific roles and permissions
     * - Sample data for development/testing
     */
    public function run(): void
    {
        $this->call([
            // Core system setup - Application roles
            RoleSeeder::class,
            
            // Assign permissions to roles (must be after RoleSeeder and migrations)
            RolePermissionSeeder::class,
            
            // Logbook-specific roles and permissions (separate system)
            LogbookRoleSeeder::class,
            LogbookPermissionSeeder::class,

            // Logbook Data Type Seeder
            AvailableDataTypeSeeder::class,

            // Logbook Templates & Fields
            LogbookTemplateSeeder::class,
            LogbookFieldSeeder::class,
            
            // Sample data (optional - comment out for production)
            UserSeeder::class, // Creates sample users for each role
        ]);

        $this->command->info('');
        $this->command->info('✓ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('IMPORTANT: Run migration for permissions:');
        $this->command->info('  php artisan migrate');
        $this->command->info('');
    }
}
