<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Assigns permissions to roles based on hierarchical access control.
     */
    public function run(): void
    {
        // Get all roles
        $superAdmin = Role::findByName('Super Admin');
        $admin = Role::findByName('Admin');
        $manager = Role::findByName('Manager');
        $institutionAdmin = Role::findByName('Institution Admin');
        $user = Role::findByName('User');

        // ===== SUPER ADMIN - ALL PERMISSIONS =====
        $superAdmin->givePermissionTo(Permission::all());
        
        // ===== ADMIN - MOST PERMISSIONS EXCEPT SUPER ADMIN SPECIFIC =====
        $adminPermissions = Permission::whereNotIn('name', [
            'system.admin', // Super admin only
        ])->pluck('name')->toArray();
        $admin->givePermissionTo($adminPermissions);
        
        // ===== INSTITUTION ADMIN - INSTITUTION SCOPED PERMISSIONS =====
        $institutionAdminPermissions = [
            // User management (institution scope)
            'users.view.institution',
            'users.view.own',
            'users.create',
            'users.update.institution',
            'users.update.own',
            'users.export',
            'users.search',
            
            // Institution management
            'institutions.view.own',
            'institutions.update.own',
            'institutions.members.view',
            'institutions.members.manage',
            'institution.manage-own',
            'institution.manage-members',
            'institution.view-members',
            'institution.view-own',
            'institution.update.own',
            
            // Logbook management (institution scope)
            'logbooks.view.institution',
            'logbooks.view.own',
            'logbooks.create',
            'logbooks.update.own',
            'logbooks.delete.own',
            'logbooks.verify',
            'logbooks.export',
            'logbooks.export.manage',
            'logbooks.export.delete.institution',
            
            // Template management (institution scope)
            'templates.view',
            'templates.view.all',
            'templates.view.institution',
            'templates.create',
            'templates.create.institution',
            'templates.update',
            'templates.update.institution',
            'templates.delete',
            'templates.delete.institution',
            'templates.publish',
            'templates.toggle.institution',
            'templates.manage',
            
            // Data types
            'datatypes.view',
            'datatypes.create',
            'datatypes.update',
            'data-types.manage',
            
            // Reports
            'reports.view.basic',
            'reports.view.advanced',
            'reports.export',
            
            // Audit logs
            'audit.view.own',
            'audit.view.all',
            'audit.export',
            
            // Notifications
            'notifications.view',
            'notifications.create',
            'notifications.send',
            'notifications.send.bulk',
            'notifications.send.to-role',
            
            // Participants
            'participants.view',
            'participants.create',
            'participants.update',
            'participants.delete',
            'participants.manage',
        ];
        $institutionAdmin->givePermissionTo($institutionAdminPermissions);
        
        // ===== MANAGER - OPERATIONAL MANAGEMENT =====
        $managerPermissions = [
            // User viewing
            'users.view.all',
            'users.view.institution',
            'users.view.own',
            'users.export',
            'users.search',
            
            // Institution viewing
            'institutions.view.all',
            'institutions.view.own',
            'institutions.members.view',
            'institution.view-members',
            'institution.view-own',
            
            // Logbook management
            'logbooks.view.all',
            'logbooks.view.institution',
            'logbooks.view.own',
            'logbooks.create',
            'logbooks.update.all',
            'logbooks.update.own',
            'logbooks.delete.own',
            'logbooks.verify',
            'logbooks.export',
            'logbooks.export.manage',
            'logbooks.export.delete.institution',
            
            // Template management
            'templates.view',
            'templates.view.all',
            'templates.view.institution',
            'templates.create',
            'templates.create.institution',
            'templates.update',
            'templates.update.institution',
            'templates.delete.institution',
            'templates.publish',
            'templates.toggle.institution',
            'templates.manage',
            
            // Data types
            'datatypes.view',
            'datatypes.create',
            'datatypes.update',
            'data-types.manage',
            
            // Reports
            'reports.view.basic',
            'reports.view.advanced',
            'reports.export',
            
            // Audit logs
            'audit.view.own',
            'audit.export',
            
            // Notifications
            'notifications.view',
            'notifications.create',
            'notifications.send',
        ];
        $manager->givePermissionTo($managerPermissions);
        
        // ===== USER - BASIC PERMISSIONS =====
        $userPermissions = [
            // Own profile
            'users.view.own',
            'users.update.own',
            
            // Logbooks (own only)
            'logbooks.view.own',
            'logbooks.create',
            'logbooks.update.own',
            'logbooks.delete.own',
            
            // Templates - User can create and manage their own templates
            'templates.view',
            'templates.create',
            'templates.update.own',
            'templates.delete.own',
            
            // Data types (view only)
            'datatypes.view',
            
            // Reports (basic only)
            'reports.view.basic',
            
            // Audit logs (own only)
            'audit.view.own',
            
            // Notifications
            'notifications.view',
        ];
        $user->givePermissionTo($userPermissions);
        
        $this->command->info('✅ Permissions assigned to roles successfully!');
        $this->command->info('   - Super Admin: ' . $superAdmin->permissions->count() . ' permissions');
        $this->command->info('   - Admin: ' . $admin->permissions->count() . ' permissions');
        $this->command->info('   - Manager: ' . $manager->permissions->count() . ' permissions');
        $this->command->info('   - Institution Admin: ' . $institutionAdmin->permissions->count() . ' permissions');
        $this->command->info('   - User: ' . $user->permissions->count() . ' permissions');
    }
}
