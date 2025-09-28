<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core system setup
            ApplicationRoleSeeder::class,
            ApplicationPermissionSeeder::class, 
            LogbookRoleSeeder::class,
            LogbookPermissionSeeder::class,
            
            // Sample data (optional)
            UserSeeder::class,
        ]);
    }
}
