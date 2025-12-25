<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds granular permissions for dynamic role management
     */
    public function up(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define granular permissions
        $permissions = [
            // User Management - Granular
            ['name' => 'users.view.all', 'guard_name' => 'web', 'description' => 'View all users in system'],
            ['name' => 'users.view.institution', 'guard_name' => 'web', 'description' => 'View users in same institution'],
            ['name' => 'users.view.own', 'guard_name' => 'web', 'description' => 'View own profile only'],
            ['name' => 'users.create', 'guard_name' => 'web', 'description' => 'Create new users'],
            ['name' => 'users.update.all', 'guard_name' => 'web', 'description' => 'Update any user'],
            ['name' => 'users.update.institution', 'guard_name' => 'web', 'description' => 'Update users in same institution'],
            ['name' => 'users.update.own', 'guard_name' => 'web', 'description' => 'Update own profile'],
            ['name' => 'users.delete', 'guard_name' => 'web', 'description' => 'Delete users'],
            ['name' => 'users.assign-role', 'guard_name' => 'web', 'description' => 'Assign roles to users'],
            ['name' => 'users.export', 'guard_name' => 'web', 'description' => 'Export user data'],
            
            // Institution Management
            ['name' => 'institutions.view.all', 'guard_name' => 'web', 'description' => 'View all institutions'],
            ['name' => 'institutions.view.own', 'guard_name' => 'web', 'description' => 'View own institution'],
            ['name' => 'institutions.create', 'guard_name' => 'web', 'description' => 'Create institutions'],
            ['name' => 'institutions.update.all', 'guard_name' => 'web', 'description' => 'Update any institution'],
            ['name' => 'institutions.update.own', 'guard_name' => 'web', 'description' => 'Update own institution'],
            ['name' => 'institutions.delete', 'guard_name' => 'web', 'description' => 'Delete institutions'],
            ['name' => 'institutions.members.view', 'guard_name' => 'web', 'description' => 'View institution members'],
            ['name' => 'institutions.members.manage', 'guard_name' => 'web', 'description' => 'Add/remove institution members'],
            
            // Logbook Templates
            ['name' => 'logbooks.view.all', 'guard_name' => 'web', 'description' => 'View all logbooks'],
            ['name' => 'logbooks.view.institution', 'guard_name' => 'web', 'description' => 'View logbooks in institution'],
            ['name' => 'logbooks.view.own', 'guard_name' => 'web', 'description' => 'View own logbooks'],
            ['name' => 'logbooks.create', 'guard_name' => 'web', 'description' => 'Create logbooks'],
            ['name' => 'logbooks.update.all', 'guard_name' => 'web', 'description' => 'Update any logbook'],
            ['name' => 'logbooks.update.own', 'guard_name' => 'web', 'description' => 'Update own logbooks'],
            ['name' => 'logbooks.delete.all', 'guard_name' => 'web', 'description' => 'Delete any logbook'],
            ['name' => 'logbooks.delete.own', 'guard_name' => 'web', 'description' => 'Delete own logbooks'],
            ['name' => 'logbooks.verify', 'guard_name' => 'web', 'description' => 'Verify logbook entries'],
            ['name' => 'logbooks.export', 'guard_name' => 'web', 'description' => 'Export logbook data'],
            ['name' => 'logbooks.export.manage', 'guard_name' => 'web', 'description' => 'Manage logbook exports (admin stats & cleanup)'],
            
            // Templates Management
            ['name' => 'templates.view', 'guard_name' => 'web', 'description' => 'View templates'],
            ['name' => 'templates.create', 'guard_name' => 'web', 'description' => 'Create templates'],
            ['name' => 'templates.update', 'guard_name' => 'web', 'description' => 'Update templates'],
            ['name' => 'templates.update.own', 'guard_name' => 'web', 'description' => 'Update own templates'],
            ['name' => 'templates.delete', 'guard_name' => 'web', 'description' => 'Delete templates'],
            ['name' => 'templates.delete.own', 'guard_name' => 'web', 'description' => 'Delete own templates'],
            ['name' => 'templates.publish', 'guard_name' => 'web', 'description' => 'Publish templates'],
            
            // Data Types Management
            ['name' => 'datatypes.view', 'guard_name' => 'web', 'description' => 'View data types'],
            ['name' => 'datatypes.create', 'guard_name' => 'web', 'description' => 'Create data types'],
            ['name' => 'datatypes.update', 'guard_name' => 'web', 'description' => 'Update data types'],
            ['name' => 'datatypes.delete', 'guard_name' => 'web', 'description' => 'Delete data types'],
            
            // Reports & Analytics
            ['name' => 'reports.view.basic', 'guard_name' => 'web', 'description' => 'View basic reports'],
            ['name' => 'reports.view.advanced', 'guard_name' => 'web', 'description' => 'View advanced reports'],
            ['name' => 'reports.view.financial', 'guard_name' => 'web', 'description' => 'View financial reports'],
            ['name' => 'reports.export', 'guard_name' => 'web', 'description' => 'Export reports'],
            
            // Audit Logs
            ['name' => 'audit.view.own', 'guard_name' => 'web', 'description' => 'View own audit logs'],
            ['name' => 'audit.view.all', 'guard_name' => 'web', 'description' => 'View all audit logs'],
            ['name' => 'audit.export', 'guard_name' => 'web', 'description' => 'Export audit logs'],
            
            // Roles & Permissions Management
            ['name' => 'roles.view', 'guard_name' => 'web', 'description' => 'View roles'],
            ['name' => 'roles.create', 'guard_name' => 'web', 'description' => 'Create custom roles'],
            ['name' => 'roles.update', 'guard_name' => 'web', 'description' => 'Update roles'],
            ['name' => 'roles.delete', 'guard_name' => 'web', 'description' => 'Delete custom roles'],
            ['name' => 'permissions.view', 'guard_name' => 'web', 'description' => 'View permissions'],
            ['name' => 'permissions.manage', 'guard_name' => 'web', 'description' => 'Manage permissions'],
            
            // Notifications
            ['name' => 'notifications.view', 'guard_name' => 'web', 'description' => 'View notifications'],
            ['name' => 'notifications.create', 'guard_name' => 'web', 'description' => 'Create notifications'],
            ['name' => 'notifications.send', 'guard_name' => 'web', 'description' => 'Send notifications to users'],
            ['name' => 'notifications.send.bulk', 'guard_name' => 'web', 'description' => 'Send bulk notifications'],
            ['name' => 'notifications.send.all', 'guard_name' => 'web', 'description' => 'Send to all users'],
            ['name' => 'notifications.send.to-role', 'guard_name' => 'web', 'description' => 'Send notifications to specific role groups'],
            
            // Participants Management
            ['name' => 'participants.view', 'guard_name' => 'web', 'description' => 'View participants'],
            ['name' => 'participants.create', 'guard_name' => 'web', 'description' => 'Create participants'],
            ['name' => 'participants.update', 'guard_name' => 'web', 'description' => 'Update participants'],
            ['name' => 'participants.delete', 'guard_name' => 'web', 'description' => 'Delete participants'],
            ['name' => 'participants.manage', 'guard_name' => 'web', 'description' => 'Full participant management access'],
            
            // Required Data Participants Management
            ['name' => 'required-data-participants.view', 'guard_name' => 'web', 'description' => 'View required data participants'],
            ['name' => 'required-data-participants.create', 'guard_name' => 'web', 'description' => 'Create required data participants'],
            ['name' => 'required-data-participants.update', 'guard_name' => 'web', 'description' => 'Update required data participants'],
            ['name' => 'required-data-participants.delete', 'guard_name' => 'web', 'description' => 'Delete required data participants'],
            ['name' => 'required-data-participants.manage', 'guard_name' => 'web', 'description' => 'Full required data participants management access'],
            
            // Additional Route-Specific Permissions
            ['name' => 'institutions.manage', 'guard_name' => 'web', 'description' => 'Full institution management access'],
            ['name' => 'institution.manage-own', 'guard_name' => 'web', 'description' => 'Manage own institution'],
            ['name' => 'institution.manage-members', 'guard_name' => 'web', 'description' => 'Manage institution members'],
            ['name' => 'institution.view-members', 'guard_name' => 'web', 'description' => 'View institution members'],
            ['name' => 'institution.view-own', 'guard_name' => 'web', 'description' => 'View own institution'],
            ['name' => 'institution.update.own', 'guard_name' => 'web', 'description' => 'Update own institution'],
            ['name' => 'data-types.manage', 'guard_name' => 'web', 'description' => 'Manage data types'],
            ['name' => 'templates.manage', 'guard_name' => 'web', 'description' => 'Manage templates'],
            ['name' => 'templates.view.all', 'guard_name' => 'web', 'description' => 'View all templates'],
            ['name' => 'templates.view.institution', 'guard_name' => 'web', 'description' => 'View institution templates'],
            ['name' => 'templates.create.any', 'guard_name' => 'web', 'description' => 'Create any template'],
            ['name' => 'templates.create.institution', 'guard_name' => 'web', 'description' => 'Create institution templates'],
            ['name' => 'templates.update.any', 'guard_name' => 'web', 'description' => 'Update any template'],
            ['name' => 'templates.update.institution', 'guard_name' => 'web', 'description' => 'Update institution templates'],
            ['name' => 'templates.delete.any', 'guard_name' => 'web', 'description' => 'Delete any template'],
            ['name' => 'templates.delete.institution', 'guard_name' => 'web', 'description' => 'Delete institution templates'],
            ['name' => 'templates.toggle.any', 'guard_name' => 'web', 'description' => 'Toggle any template'],
            ['name' => 'templates.toggle.institution', 'guard_name' => 'web', 'description' => 'Toggle institution templates'],
            ['name' => 'logbooks.export.delete.any', 'guard_name' => 'web', 'description' => 'Delete any logbook export'],
            ['name' => 'logbooks.export.delete.institution', 'guard_name' => 'web', 'description' => 'Delete institution logbook exports'],
            ['name' => 'users.manage', 'guard_name' => 'web', 'description' => 'Full user management access'],
            ['name' => 'users.search', 'guard_name' => 'web', 'description' => 'Search users'],
            ['name' => 'users.assign-role.any', 'guard_name' => 'web', 'description' => 'Assign any role including Admin'],
            ['name' => 'users.assign-role.basic', 'guard_name' => 'web', 'description' => 'Assign basic roles only'],
            ['name' => 'users.delete.any', 'guard_name' => 'web', 'description' => 'Delete any user including admins'],
            ['name' => 'permissions.create', 'guard_name' => 'web', 'description' => 'Create new permissions'],
            ['name' => 'roles.manage', 'guard_name' => 'web', 'description' => 'Manage roles'],
            ['name' => 'roles.assign-permissions', 'guard_name' => 'web', 'description' => 'Assign permissions to roles'],
            ['name' => 'system.admin', 'guard_name' => 'web', 'description' => 'Super admin system access'],
        ];

        // Insert permissions with proper handling for duplicates
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign granular permissions to existing roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            // Get all permissions including the new ones we just added
            $superAdmin->syncPermissions(Permission::all());
        }

        // Admin - Most permissions except super admin specific
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $adminPermissions = Permission::whereIn('name', [
                'users.view.all', 'users.create', 'users.update.all', 'users.delete', 'users.export',
                'users.manage', 'users.search', 'users.assign-role.basic',
                'institutions.view.all', 'institutions.create', 'institutions.update.all', 'institutions.delete',
                'institutions.members.view', 'institutions.members.manage', 'institutions.manage',
                'institution.view-members',
                'logbooks.view.all', 'logbooks.create', 'logbooks.update.all', 'logbooks.delete.all',
                'logbooks.verify', 'logbooks.export', 'logbooks.export.manage', 'logbooks.export.delete.any',
                'templates.view', 'templates.create', 'templates.update', 'templates.delete', 'templates.publish',
                'templates.manage', 'templates.view.all', 'templates.create.any', 'templates.update.any',
                'templates.delete.any', 'templates.toggle.any',
                'datatypes.view', 'datatypes.create', 'datatypes.update', 'datatypes.delete',
                'data-types.manage',
                'reports.view.basic', 'reports.view.advanced', 'reports.export',
                'audit.view.all', 'audit.export',
                'roles.view', 'permissions.view', 'roles.manage', 'roles.assign-permissions',
                'notifications.view', 'notifications.create', 'notifications.send', 'notifications.send.bulk',
                'notifications.send.to-role',
                'participants.view', 'participants.create', 'participants.update', 'participants.delete', 'participants.manage',
                'required-data-participants.view', 'required-data-participants.create', 'required-data-participants.update',
                'required-data-participants.delete', 'required-data-participants.manage',
            ])->pluck('name');
            $admin->syncPermissions($adminPermissions);
        }

        // Manager - Limited management permissions
        $manager = Role::where('name', 'Manager')->first();
        if ($manager) {
            $managerPermissions = Permission::whereIn('name', [
                'users.view.all', 'users.view.institution', 'users.search',
                'institutions.view.all', 'institutions.manage', 'institution.view-members',
                'logbooks.view.all', 'logbooks.create', 'logbooks.update.own', 'logbooks.verify', 'logbooks.export',
                'logbooks.export.manage', 'logbooks.export.delete.any',
                'templates.view', 'templates.create', 'templates.update', 'templates.manage',
                'templates.view.all', 'templates.create.any', 'templates.update.any', 'templates.delete.any',
                'templates.toggle.any',
                'datatypes.view',
                'reports.view.basic', 'reports.view.advanced', 'reports.export',
                'audit.view.own',
                'notifications.view', 'notifications.send',
            ])->pluck('name');
            $manager->syncPermissions($managerPermissions);
        }

        // Institution Admin - Institution-scoped permissions
        $institutionAdmin = Role::where('name', 'Institution Admin')->first();
        if ($institutionAdmin) {
            $institutionAdminPermissions = Permission::whereIn('name', [
                'users.view.institution', 'users.create', 'users.search',
                'institutions.view.own', 'institutions.update.own',
                'institutions.members.view', 'institutions.members.manage',
                'institution.manage-own', 'institution.manage-members', 'institution.view-members',
                'institution.view-own', 'institution.update.own',
                'logbooks.view.institution', 'logbooks.create', 'logbooks.update.own', 'logbooks.delete.own',
                'logbooks.export.delete.institution',
                'templates.view', 'templates.create', 'templates.update', 'templates.delete',
                'templates.manage', 'templates.view.institution', 'templates.create.institution',
                'templates.update.institution', 'templates.delete.institution', 'templates.toggle.institution',
                'datatypes.view',
                'reports.view.basic',
                'audit.view.own',
                'notifications.view',
                'participants.view', 'participants.create', 'participants.update', 'participants.delete', 'participants.manage',
                'required-data-participants.view', 'required-data-participants.create', 'required-data-participants.update',
                'required-data-participants.delete', 'required-data-participants.manage',
            ])->pluck('name');
            $institutionAdmin->syncPermissions($institutionAdminPermissions);
        }

        // User - Basic permissions
        $user = Role::where('name', 'User')->first();
        if ($user) {
            $userPermissions = Permission::whereIn('name', [
                'users.view.own', 'users.update.own',
                'logbooks.view.own', 'logbooks.create',
                'templates.view',
                'datatypes.view',
                'notifications.view',
            ])->pluck('name');
            $user->syncPermissions($userPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't delete, just clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Optionally remove the granular permissions added
        $granularPermissions = [
            'users.view.all', 'users.view.institution', 'users.view.own',
            'institutions.view.all', 'institutions.view.own',
            'logbooks.view.all', 'logbooks.view.institution', 'logbooks.view.own',
            // ... add all permission names
        ];
        
        Permission::whereIn('name', $granularPermissions)->delete();
    }
};
