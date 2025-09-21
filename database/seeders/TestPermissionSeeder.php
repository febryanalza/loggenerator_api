<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LogbookTemplate;
use Spatie\Permission\Models\Role;

class TestPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Testing Permission System...');

        // Test 1: Create user and check auto-assigned role
        $this->command->info('=== Test 1: User Creation & Auto Role Assignment ===');
        
        $user = User::firstOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password123')
            ]
        );

        $this->command->info("✓ User created: {$user->name} (ID: {$user->id})");
        
        // Check if user has default role
        $userRoles = $user->roles->pluck('name')->toArray();
        $this->command->info("✓ Auto-assigned roles: " . implode(', ', $userRoles));
        
        if ($user->hasRole('user')) {
            $this->command->info("✓ Default 'user' role assigned successfully");
        } else {
            $this->command->error("✗ Default role assignment failed");
        }

        // Test 2: Create admin user manually
        $this->command->info('=== Test 2: Manual Role Assignment ===');
        
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123')
            ]
        );

        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        $admin->assignRole($adminRole);
        
        $this->command->info("✓ Admin user created and assigned admin role");
        $this->command->info("✓ Admin permissions: " . $admin->getAllPermissions()->pluck('name')->implode(', '));

        // Test 3: Logbook template and data-level permissions
        $this->command->info('=== Test 3: Logbook-Level Permissions ===');
        
        $template = LogbookTemplate::create([
            'name' => 'Test Operations Log',
            'description' => 'Test template for permission system'
        ]);

        $this->command->info("✓ Template created: {$template->name}");

        // Assign logbook roles
        $user->assignLogbookRole($template->id, 'owner');
        $admin->assignLogbookRole($template->id, 'supervisor');

        $this->command->info("✓ User assigned as 'owner' of template");
        $this->command->info("✓ Admin assigned as 'supervisor' of template");

        // Test permissions
        $userRole = $user->getLogbookRole($template->id);
        $adminRole = $admin->getLogbookRole($template->id);

        $this->command->info("✓ User's logbook role: " . ($userRole ? $userRole->name : 'None'));
        $this->command->info("✓ Admin's logbook role: " . ($adminRole ? $adminRole->name : 'None'));

        // Test specific permissions
        $canUserManageTemplate = $user->hasLogbookPermission($template->id, 'manage_template');
        $canAdminApprove = $admin->hasLogbookPermission($template->id, 'approve_entry');
        $canAdminManageTemplate = $admin->hasLogbookPermission($template->id, 'manage_template');

        $this->command->info("✓ User can manage template: " . ($canUserManageTemplate ? 'Yes' : 'No'));
        $this->command->info("✓ Admin can approve entries: " . ($canAdminApprove ? 'Yes' : 'No'));
        $this->command->info("✓ Admin can manage template: " . ($canAdminManageTemplate ? 'Yes' : 'No'));

        $this->command->info('=== Permission System Test Complete ===');
    }
}