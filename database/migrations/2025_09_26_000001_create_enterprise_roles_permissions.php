<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates enterprise-level roles and permissions structure:
     * Super Admin -> Admin -> Manager -> User hierarchy
     */
    public function up(): void
    {
        // Clear existing data for fresh migration
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();

        // ===== ENTERPRISE-LEVEL PERMISSIONS =====
        $permissions = [
            // === SUPER ADMIN PERMISSIONS ===
            ['name' => 'super_admin_access', 'guard_name' => 'web', 'description' => 'Full system administration access'],
            ['name' => 'manage_system_settings', 'guard_name' => 'web', 'description' => 'Configure system-wide settings'],
            ['name' => 'manage_database', 'guard_name' => 'web', 'description' => 'Database operations and backups'],
            ['name' => 'view_system_logs', 'guard_name' => 'web', 'description' => 'View detailed system logs'],
            ['name' => 'manage_enterprise_roles', 'guard_name' => 'web', 'description' => 'Create and manage enterprise roles'],

            // === ADMIN PERMISSIONS ===
            ['name' => 'admin_dashboard_access', 'guard_name' => 'web', 'description' => 'Access admin dashboard'],
            ['name' => 'manage_all_users', 'guard_name' => 'web', 'description' => 'Full user management across organization'],
            ['name' => 'manage_all_templates', 'guard_name' => 'web', 'description' => 'Manage all logbook templates'],
            ['name' => 'manage_all_logbooks', 'guard_name' => 'web', 'description' => 'Access and manage all logbooks'],
            ['name' => 'view_all_audit_logs', 'guard_name' => 'web', 'description' => 'View all system audit logs'],
            ['name' => 'manage_permissions', 'guard_name' => 'web', 'description' => 'Assign permissions to users'],
            ['name' => 'generate_reports', 'guard_name' => 'web', 'description' => 'Generate comprehensive reports'],
            ['name' => 'export_all_data', 'guard_name' => 'web', 'description' => 'Export any system data'],

            // === MANAGER PERMISSIONS ===
            ['name' => 'manager_dashboard_access', 'guard_name' => 'web', 'description' => 'Access manager dashboard'],
            ['name' => 'manage_team_users', 'guard_name' => 'web', 'description' => 'Manage users within department/team'],
            ['name' => 'manage_department_templates', 'guard_name' => 'web', 'description' => 'Manage templates for department'],
            ['name' => 'view_department_logbooks', 'guard_name' => 'web', 'description' => 'View all logbooks in department'],
            ['name' => 'assign_logbook_access', 'guard_name' => 'web', 'description' => 'Assign logbook access to team members'],
            ['name' => 'view_team_audit_logs', 'guard_name' => 'web', 'description' => 'View audit logs for managed area'],
            ['name' => 'generate_team_reports', 'guard_name' => 'web', 'description' => 'Generate reports for managed area'],
            ['name' => 'export_team_data', 'guard_name' => 'web', 'description' => 'Export data for managed area'],
            ['name' => 'approve_template_requests', 'guard_name' => 'web', 'description' => 'Approve new template requests'],

            // === USER PERMISSIONS ===
            ['name' => 'user_dashboard_access', 'guard_name' => 'web', 'description' => 'Access user dashboard'],
            ['name' => 'view_own_profile', 'guard_name' => 'web', 'description' => 'View and edit own profile'],
            ['name' => 'view_assigned_logbooks', 'guard_name' => 'web', 'description' => 'View logbooks assigned to user'],
            ['name' => 'create_logbook_entries', 'guard_name' => 'web', 'description' => 'Create entries in assigned logbooks'],
            ['name' => 'edit_own_entries', 'guard_name' => 'web', 'description' => 'Edit own logbook entries'],
            ['name' => 'view_own_audit_logs', 'guard_name' => 'web', 'description' => 'View own activity logs'],
            ['name' => 'request_template_access', 'guard_name' => 'web', 'description' => 'Request access to templates'],
            ['name' => 'upload_files', 'guard_name' => 'web', 'description' => 'Upload files to logbook entries'],

            // === TEMPLATE & LOGBOOK PERMISSIONS ===
            ['name' => 'create_templates', 'guard_name' => 'web', 'description' => 'Create new logbook templates'],
            ['name' => 'edit_templates', 'guard_name' => 'web', 'description' => 'Edit existing templates'],
            ['name' => 'delete_templates', 'guard_name' => 'web', 'description' => 'Delete templates'],
            ['name' => 'view_templates', 'guard_name' => 'web', 'description' => 'View template list'],
            ['name' => 'duplicate_templates', 'guard_name' => 'web', 'description' => 'Duplicate existing templates'],

            // === NOTIFICATION PERMISSIONS ===
            ['name' => 'send_system_notifications', 'guard_name' => 'web', 'description' => 'Send system-wide notifications'],
            ['name' => 'send_department_notifications', 'guard_name' => 'web', 'description' => 'Send notifications to department'],
            ['name' => 'manage_notification_settings', 'guard_name' => 'web', 'description' => 'Configure notification preferences'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // ===== ENTERPRISE ROLES HIERARCHY =====
        
        // 1. SUPER ADMIN - Ultimate system administrator
        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);
        
        // Give Super Admin ALL permissions
        $superAdmin->givePermissionTo(Permission::all());

        // 2. ADMIN - Organization administrator  
        $admin = Role::create([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);
        
        $admin->givePermissionTo([
            'admin_dashboard_access',
            'manage_all_users',
            'manage_all_templates', 
            'manage_all_logbooks',
            'view_all_audit_logs',
            'manage_permissions',
            'generate_reports',
            'export_all_data',
            'create_templates',
            'edit_templates',
            'delete_templates',
            'view_templates',
            'duplicate_templates',
            'send_system_notifications',
            'manage_notification_settings',
            'assign_logbook_access',
            'approve_template_requests',
            'upload_files'
        ]);

        // 3. MANAGER - Department/Team manager
        $manager = Role::create([
            'name' => 'Manager', 
            'guard_name' => 'web'
        ]);
        
        $manager->givePermissionTo([
            'manager_dashboard_access',
            'manage_team_users',
            'manage_department_templates',
            'view_department_logbooks', 
            'assign_logbook_access',
            'view_team_audit_logs',
            'generate_team_reports',
            'export_team_data',
            'approve_template_requests',
            'create_templates',
            'edit_templates',
            'view_templates',
            'duplicate_templates',
            'send_department_notifications',
            'view_assigned_logbooks',
            'create_logbook_entries',
            'edit_own_entries',
            'view_own_profile',
            'upload_files',
            'manage_notification_settings'
        ]);

        // 4. USER - End user
        $user = Role::create([
            'name' => 'User',
            'guard_name' => 'web'
        ]);
        
        $user->givePermissionTo([
            'user_dashboard_access',
            'view_own_profile',
            'view_assigned_logbooks',
            'create_logbook_entries', 
            'edit_own_entries',
            'view_own_audit_logs',
            'request_template_access',
            'upload_files',
            'view_templates'
        ]);

        echo "✅ Enterprise roles and permissions created successfully!\n";
        echo "✅ Hierarchy: Super Admin -> Admin -> Manager -> User\n";
        echo "✅ Total permissions: " . Permission::count() . "\n";
        echo "✅ Total roles: " . Role::count() . "\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete(); 
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();
    }
};