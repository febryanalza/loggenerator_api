<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Seeds IT roles and permissions for application-level access.
     */
    public function up(): void
    {
        // IT Application-Level Permissions
        $itPermissions = [
            // User Management
            ['name' => 'manage_users', 'guard_name' => 'web', 'description' => 'Create, edit, delete users'],
            ['name' => 'view_users', 'guard_name' => 'web', 'description' => 'View user list and details'],
            
            // Template Management
            ['name' => 'manage_templates', 'guard_name' => 'web', 'description' => 'Create, edit, delete logbook templates'],
            ['name' => 'view_templates', 'guard_name' => 'web', 'description' => 'View template list'],
            
            // System Management
            ['name' => 'view_audit_logs', 'guard_name' => 'web', 'description' => 'View system audit logs'],
            ['name' => 'manage_system_settings', 'guard_name' => 'web', 'description' => 'Manage application settings'],
            
            // File Management
            ['name' => 'manage_files', 'guard_name' => 'web', 'description' => 'Upload, delete files'],
            
            // Reports
            ['name' => 'view_reports', 'guard_name' => 'web', 'description' => 'View system reports'],
            ['name' => 'export_data', 'guard_name' => 'web', 'description' => 'Export logbook data'],
        ];

        foreach ($itPermissions as $permission) {
            Permission::create($permission);
        }

        // IT Application-Level Roles
        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $staff = Role::create(['name' => 'staff', 'guard_name' => 'web']);
        $user = Role::create(['name' => 'user', 'guard_name' => 'web']);

        // Assign permissions to IT roles
        $superAdmin->givePermissionTo(Permission::all());
        
        $admin->givePermissionTo([
            'manage_users', 'view_users',
            'manage_templates', 'view_templates', 
            'view_audit_logs',
            'manage_files',
            'view_reports', 'export_data'
        ]);
        
        $staff->givePermissionTo([
            'view_users', 'view_templates',
            'manage_files', 'view_reports'
        ]);
        
        $user->givePermissionTo([
            'view_templates'
        ]);

        // Logbook Data-Level Setup
        DB::table('logbook_roles')->insert([
            ['name' => 'owner', 'description' => 'Full control over logbook template and all entries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'supervisor', 'description' => 'Can review, approve, and manage entries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'editor', 'description' => 'Can create and edit entries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'viewer', 'description' => 'Can only view entries', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('logbook_permissions')->insert([
            ['name' => 'view_logbook', 'description' => 'View logbook template and entries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create_entry', 'description' => 'Create new logbook entries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit_entry', 'description' => 'Edit existing logbook entries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete_entry', 'description' => 'Delete logbook entries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'approve_entry', 'description' => 'Approve/reject logbook entries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manage_template', 'description' => 'Modify template structure and fields', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'assign_users', 'description' => 'Assign users to logbook roles', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Assign logbook permissions to logbook roles
        $logbookRolePermissions = [
            'owner' => ['view_logbook', 'create_entry', 'edit_entry', 'delete_entry', 'approve_entry', 'manage_template', 'assign_users'],
            'supervisor' => ['view_logbook', 'create_entry', 'edit_entry', 'approve_entry'],
            'editor' => ['view_logbook', 'create_entry', 'edit_entry'],
            'viewer' => ['view_logbook'],
        ];

        foreach ($logbookRolePermissions as $roleName => $permissions) {
            $roleId = DB::table('logbook_roles')->where('name', $roleName)->first()->id;
            
            foreach ($permissions as $permissionName) {
                $permissionId = DB::table('logbook_permissions')->where('name', $permissionName)->first()->id;
                
                DB::table('logbook_role_permissions')->insert([
                    'logbook_role_id' => $roleId,
                    'logbook_permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clean up logbook role permissions
        DB::table('logbook_role_permissions')->delete();
        DB::table('logbook_permissions')->delete();
        DB::table('logbook_roles')->delete();
        
        // Clean up Spatie roles and permissions
        Role::where('guard_name', 'web')->delete();
        Permission::where('guard_name', 'web')->delete();
    }
};