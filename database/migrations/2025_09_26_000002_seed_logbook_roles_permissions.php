<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Seeds logbook roles and permissions for data-level access control.
     */
    public function up(): void
    {
        // Clear existing logbook roles and permissions for fresh migration
        DB::table('user_logbook_access')->delete();
        DB::table('logbook_role_permissions')->delete();
        DB::table('logbook_permissions')->delete();
        DB::table('logbook_roles')->delete();

        // ===== LOGBOOK DATA-LEVEL PERMISSIONS =====
        $logbookPermissions = [
            ['name' => 'view_logbook', 'description' => 'View logbook entries and data'],
            ['name' => 'create_entry', 'description' => 'Create new logbook entries'],
            ['name' => 'edit_entry', 'description' => 'Edit existing logbook entries'],
            ['name' => 'delete_entry', 'description' => 'Delete logbook entries'],
            ['name' => 'edit_own_entry', 'description' => 'Edit only own created entries'],
            ['name' => 'delete_own_entry', 'description' => 'Delete only own created entries'],
            ['name' => 'approve_entry', 'description' => 'Approve/reject entries for publication'],
            ['name' => 'manage_template', 'description' => 'Modify template structure and fields'],
            ['name' => 'manage_access', 'description' => 'Grant/revoke access to other users'],
            ['name' => 'view_analytics', 'description' => 'View logbook analytics and reports'],
            ['name' => 'export_data', 'description' => 'Export logbook data'],
            ['name' => 'manage_files', 'description' => 'Upload/delete files in entries'],
            ['name' => 'supervise_entries', 'description' => 'Review and supervise all entries'],
            ['name' => 'assign_tasks', 'description' => 'Assign entries or tasks to users'],
        ];

        foreach ($logbookPermissions as $permission) {
            DB::table('logbook_permissions')->insert([
                'name' => $permission['name'],
                'description' => $permission['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ===== LOGBOOK ROLES HIERARCHY =====
        
        // 1. OWNER - Full control over logbook
        $ownerId = DB::table('logbook_roles')->insertGetId([
            'name' => 'Owner',
            'description' => 'Full control over logbook template and all entries. Can manage access and modify template structure.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. SUPERVISOR - Management and oversight
        $supervisorId = DB::table('logbook_roles')->insertGetId([
            'name' => 'Supervisor', 
            'description' => 'Supervise all logbook activities, approve entries, and manage team access. Cannot modify template structure.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. EDITOR - Content creation and editing
        $editorId = DB::table('logbook_roles')->insertGetId([
            'name' => 'Editor',
            'description' => 'Create, edit, and delete any entries in the logbook. Can upload files and export data.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. VIEWER - Read-only access
        $viewerId = DB::table('logbook_roles')->insertGetId([
            'name' => 'Viewer',
            'description' => 'Read-only access to view logbook entries. Cannot create, edit, or delete content.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ===== ASSIGN PERMISSIONS TO ROLES =====
        
        // Get permission IDs for easier assignment
        $permissions = DB::table('logbook_permissions')->pluck('id', 'name');
        
        // OWNER - All permissions
        $ownerPermissions = $permissions->all();
        foreach ($ownerPermissions as $permissionId) {
            DB::table('logbook_role_permissions')->insert([
                'logbook_role_id' => $ownerId,
                'logbook_permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // SUPERVISOR - Management and oversight permissions  
        $supervisorPermissions = [
            'view_logbook', 'create_entry', 'edit_entry', 'delete_entry',
            'approve_entry', 'manage_access', 'view_analytics', 'export_data',
            'manage_files', 'supervise_entries', 'assign_tasks'
        ];
        foreach ($supervisorPermissions as $permName) {
            if (isset($permissions[$permName])) {
                DB::table('logbook_role_permissions')->insert([
                    'logbook_role_id' => $supervisorId,
                    'logbook_permission_id' => $permissions[$permName],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // EDITOR - Content creation and editing permissions
        $editorPermissions = [
            'view_logbook', 'create_entry', 'edit_entry', 'delete_entry',
            'edit_own_entry', 'delete_own_entry', 'view_analytics', 
            'export_data', 'manage_files'
        ];
        foreach ($editorPermissions as $permName) {
            if (isset($permissions[$permName])) {
                DB::table('logbook_role_permissions')->insert([
                    'logbook_role_id' => $editorId,
                    'logbook_permission_id' => $permissions[$permName],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // VIEWER - Read-only permissions
        $viewerPermissions = [
            'view_logbook', 'view_analytics'
        ];
        foreach ($viewerPermissions as $permName) {
            if (isset($permissions[$permName])) {
                DB::table('logbook_role_permissions')->insert([
                    'logbook_role_id' => $viewerId,
                    'logbook_permission_id' => $permissions[$permName],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "✅ Logbook roles and permissions created successfully!\n";
        echo "✅ Roles: Owner -> Supervisor -> Editor -> Viewer\n";
        echo "✅ Total logbook permissions: " . DB::table('logbook_permissions')->count() . "\n";
        echo "✅ Total logbook roles: " . DB::table('logbook_roles')->count() . "\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_logbook_access')->delete();
        DB::table('logbook_role_permissions')->delete();
        DB::table('logbook_permissions')->delete();
        DB::table('logbook_roles')->delete();
    }
};