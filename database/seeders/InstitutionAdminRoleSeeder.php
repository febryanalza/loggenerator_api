<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class InstitutionAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Institution Admin role if it doesn't exist
        $role = Role::firstOrCreate(
            ['name' => 'Institution Admin', 'guard_name' => 'web'],
            ['name' => 'Institution Admin', 'guard_name' => 'web']
        );

        // Define permissions for Institution Admin
        $permissions = [
            // Template management within their institution
            'view_templates',
            'create_templates',
            'edit_templates',
            'delete_templates',
            
            // User management within their institution (limited)
            'view_institution_users',
            'create_institution_users',
            'edit_institution_users',
            'delete_institution_users',
            
            // Logbook data management
            'view_logbook_data',
            'create_logbook_data',
            'edit_logbook_data',
            'delete_logbook_data',
            
            // Institution-specific permissions
            'manage_institution_templates',
            'assign_template_access',
            'view_institution_reports',
        ];

        // Create permissions if they don't exist and assign to role
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['name' => $permissionName, 'guard_name' => 'web']
            );
            
            // Assign permission to role if not already assigned
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        $this->command->info('Institution Admin role and permissions created successfully!');
        $this->command->info('Total permissions assigned: ' . count($permissions));
    }
}
