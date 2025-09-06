<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User management permissions
            [
                'name' => 'create_users',
                'description' => 'Can create new users',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'read_users',
                'description' => 'Can view user details',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'update_users',
                'description' => 'Can update user details',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'delete_users',
                'description' => 'Can delete users',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Template management permissions
            [
                'name' => 'create_templates',
                'description' => 'Can create logbook templates',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'read_templates',
                'description' => 'Can view logbook templates',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'update_templates',
                'description' => 'Can update logbook templates',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'delete_templates',
                'description' => 'Can delete logbook templates',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Logbook data permissions
            [
                'name' => 'create_logbook_data',
                'description' => 'Can create logbook entries',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'read_logbook_data',
                'description' => 'Can view logbook entries',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'update_logbook_data',
                'description' => 'Can update logbook entries',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'delete_logbook_data',
                'description' => 'Can delete logbook entries',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('permissions')->insert($permissions);
    }
}