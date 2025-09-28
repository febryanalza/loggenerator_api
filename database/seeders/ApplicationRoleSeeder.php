<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ApplicationRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create application-level roles
        $roles = [
            [
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'description' => 'Full system access with all permissions'
            ],
            [
                'name' => 'Admin', 
                'guard_name' => 'web',
                'description' => 'Administrative access to most system features'
            ],
            [
                'name' => 'Manager',
                'guard_name' => 'web', 
                'description' => 'Management level access to business operations'
            ],
            [
                'name' => 'User',
                'guard_name' => 'web',
                'description' => 'Regular user with basic access to create and manage templates'
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
        }

        $this->command->info('Application roles seeded successfully.');
    }
}