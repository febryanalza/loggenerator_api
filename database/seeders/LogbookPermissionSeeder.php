<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogbookPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create logbook-specific permissions
        $logbookPermissions = [
            // Data viewing permissions  
            [
                'id' => 1,
                'name' => 'view logbook data',
                'description' => 'Can view logbook template and data entries'
            ],
            
            // Data entry permissions
            [
                'id' => 2, 
                'name' => 'create logbook entries',
                'description' => 'Can create new logbook data entries'
            ],
            [
                'id' => 3,
                'name' => 'edit logbook entries', 
                'description' => 'Can edit existing logbook data entries'
            ],
            [
                'id' => 4,
                'name' => 'delete logbook entries',
                'description' => 'Can delete logbook data entries'
            ],
            
            // User access management permissions
            [
                'id' => 5,
                'name' => 'manage template users',
                'description' => 'Can add/remove users and assign roles to template'
            ],
            [
                'id' => 6,
                'name' => 'view template users', 
                'description' => 'Can view users who have access to template'
            ],
            
            // Template structure permissions (Owner only)
            [
                'id' => 7,
                'name' => 'edit template structure',
                'description' => 'Can modify template name, description, and fields'
            ],
            [
                'id' => 8,
                'name' => 'delete template',
                'description' => 'Can delete the entire template'
            ]
        ];

        // Insert permissions
        foreach ($logbookPermissions as $permission) {
            DB::table('logbook_permissions')->updateOrInsert(
                ['id' => $permission['id']],
                array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Assign permissions to logbook roles
        $this->assignPermissionsToLogbookRoles();

        $this->command->info('Logbook permissions seeded successfully.');
    }

    private function assignPermissionsToLogbookRoles(): void
    {
        // Clear existing role-permission assignments
        DB::table('logbook_role_permissions')->delete();

        $rolePermissions = [
            // Owner (ID: 1) - Full permissions INCLUDING manage_access (ID: 9)
            1 => [1, 2, 3, 4, 5, 6, 7, 8, 9],
            
            // Supervisor (ID: 2) - Management permissions but NO delete entries and NO template structure changes
            2 => [1, 2, 3, 5, 6, 9],
            
            // Editor (ID: 3) - Data entry permissions only  
            3 => [1, 2, 3, 6],
            
            // Viewer (ID: 4) - Read-only permissions
            4 => [1, 6]
        ];

        foreach ($rolePermissions as $roleId => $permissionIds) {
            foreach ($permissionIds as $permissionId) {
                DB::table('logbook_role_permissions')->insert([
                    'logbook_role_id' => $roleId,
                    'logbook_permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}