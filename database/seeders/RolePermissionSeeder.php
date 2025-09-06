<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePermissions = [];
        
        // Admin has all permissions
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        $allPermissions = DB::table('permissions')->get();
        
        foreach ($allPermissions as $permission) {
            $rolePermissions[] = [
                'role_id' => $adminRole->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
            ];
        }
        
        // Manager permissions
        $managerRole = DB::table('roles')->where('name', 'Manager')->first();
        $managerPermissions = ['read_users', 'create_templates', 'read_templates', 'update_templates',
                               'create_logbook_data', 'read_logbook_data', 'update_logbook_data'];
        
        foreach ($managerPermissions as $permName) {
            $permission = DB::table('permissions')->where('name', $permName)->first();
            if ($permission) {
                $rolePermissions[] = [
                    'role_id' => $managerRole->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                ];
            }
        }
        
        // User permissions
        $userRole = DB::table('roles')->where('name', 'User')->first();
        $userPermissions = ['read_templates', 'create_logbook_data', 'read_logbook_data', 'update_logbook_data'];
        
        foreach ($userPermissions as $permName) {
            $permission = DB::table('permissions')->where('name', $permName)->first();
            if ($permission) {
                $rolePermissions[] = [
                    'role_id' => $userRole->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                ];
            }
        }
        
        DB::table('role_permissions')->insert($rolePermissions);
    }
}