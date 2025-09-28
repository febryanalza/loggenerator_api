<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ApplicationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create application-level permissions based on the routes analysis
        $permissions = [
            // Template management permissions - Enhanced for User role
            'create templates' => 'Can create new logbook templates',
            'edit templates' => 'Can edit existing logbook templates',
            'delete templates' => 'Can delete logbook templates',
            'manage templates' => 'Can manage logbook template fields and structure',
            'assign template access' => 'Can assign users to templates with specific roles',
            
            // User management permissions
            'view users' => 'Can view user information and profiles',
            'manage users' => 'Can create, update, and manage user accounts',
            
            // Notification permissions
            'send notifications' => 'Can send notifications to users and roles',
            'manage notifications' => 'Can create and manage system notifications',
            
            // File management permissions
            'upload files' => 'Can upload images and files to the system',
            'manage files' => 'Can manage and delete uploaded files',
            
            // System administration permissions
            'view system info' => 'Can view system information and statistics',
            'manage system' => 'Can perform system maintenance operations',
        ];

        // Create permissions (note: standard Spatie permissions table doesn't have description column)
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        $this->command->info('Application permissions seeded successfully.');
    }

    private function assignPermissionsToRoles(): void
    {
        // Super Admin gets all permissions
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(Permission::all());
        }

        // Admin gets most permissions except critical system operations
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $adminPermissions = [
                'create templates',
                'edit templates',
                'delete templates',
                'manage templates',
                'assign template access',
                'view users',
                'manage users', 
                'send notifications',
                'manage notifications',
                'upload files',
                'manage files',
                'view system info'
            ];
            $admin->givePermissionTo($adminPermissions);
        }

        // Manager gets business operation permissions
        $manager = Role::where('name', 'Manager')->first();
        if ($manager) {
            $managerPermissions = [
                'create templates',
                'edit templates',
                'delete templates',
                'manage templates',
                'assign template access',
                'view users',
                'send notifications',
                'upload files',
                'view system info'
            ];
            $manager->givePermissionTo($managerPermissions);
        }

        // User gets template management permissions - ENHANCED POWERS
        $user = Role::where('name', 'User')->first();
        if ($user) {
            $userPermissions = [
                'create templates',
                'edit templates', 
                'delete templates',
                'manage templates',
                'assign template access',
                'view users',
                'upload files'
            ];
            $user->givePermissionTo($userPermissions);
        }
    }
}