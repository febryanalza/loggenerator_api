<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:status 
                            {--user-id= : Show permissions for specific user}
                            {--role= : Show permissions for specific role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current permission system status and statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════╗');
        $this->info('║   Permission System Status                        ║');
        $this->info('╚════════════════════════════════════════════════════╝');
        $this->newLine();

        // If specific user requested
        if ($userId = $this->option('user-id')) {
            $this->showUserPermissions($userId);
            return Command::SUCCESS;
        }

        // If specific role requested
        if ($roleName = $this->option('role')) {
            $this->showRolePermissions($roleName);
            return Command::SUCCESS;
        }

        // General statistics
        $this->showGeneralStats();
        $this->newLine();
        $this->showRoleStats();
        $this->newLine();
        $this->showPermissionDistribution();

        return Command::SUCCESS;
    }

    /**
     * Show general statistics
     */
    private function showGeneralStats()
    {
        $totalPermissions = Permission::count();
        $totalRoles = Role::count();
        $systemRoles = Role::whereIn('name', ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User'])->count();
        $customRoles = $totalRoles - $systemRoles;
        
        $usersWithRoles = DB::table('model_has_roles')
            ->distinct('model_id')
            ->count('model_id');
        
        $usersWithDirectPerms = DB::table('model_has_permissions')
            ->distinct('model_id')
            ->count('model_id');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Permissions', $totalPermissions],
                ['Total Roles', $totalRoles],
                ['├─ System Roles', $systemRoles],
                ['└─ Custom Roles', $customRoles],
                ['Users with Roles', $usersWithRoles],
                ['Users with Direct Permissions', $usersWithDirectPerms],
            ]
        );
    }

    /**
     * Show role statistics
     */
    private function showRoleStats()
    {
        $this->line('📊 <fg=cyan>Roles Overview</>');
        
        $roles = Role::withCount(['permissions', 'users'])->get();
        
        $data = [];
        foreach ($roles as $role) {
            $isSystem = in_array($role->name, ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User']);
            $data[] = [
                $role->name,
                $isSystem ? 'System' : 'Custom',
                $role->permissions_count,
                $role->users_count,
            ];
        }

        $this->table(
            ['Role Name', 'Type', 'Permissions', 'Users'],
            $data
        );
    }

    /**
     * Show permission distribution
     */
    private function showPermissionDistribution()
    {
        $this->line('📈 <fg=cyan>Permission Distribution by Module</>');
        
        $permissions = Permission::pluck('name')->toArray();
        
        $modules = [];
        foreach ($permissions as $perm) {
            $parts = explode('.', $perm);
            $module = $parts[0] ?? 'other';
            
            if (!isset($modules[$module])) {
                $modules[$module] = 0;
            }
            $modules[$module]++;
        }

        arsort($modules);

        $data = [];
        foreach ($modules as $module => $count) {
            $percentage = round(($count / count($permissions)) * 100, 1);
            $data[] = [
                ucfirst($module),
                $count,
                $percentage . '%',
                str_repeat('█', (int)($percentage / 2))
            ];
        }

        $this->table(
            ['Module', 'Count', '%', 'Distribution'],
            $data
        );
    }

    /**
     * Show permissions for specific user
     */
    private function showUserPermissions(string $userId)
    {
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }

        $this->line("👤 <fg=cyan>User: {$user->name} ({$user->email})</>");
        $this->newLine();

        // Roles
        $roles = $user->getRoleNames()->toArray();
        $this->line('<fg=yellow>Roles:</>');
        foreach ($roles as $role) {
            $this->line("  • {$role}");
        }

        // Direct permissions
        $directPerms = $user->permissions->pluck('name')->toArray();
        if (!empty($directPerms)) {
            $this->newLine();
            $this->line('<fg=yellow>Direct Permissions:</>');
            foreach ($directPerms as $perm) {
                $this->line("  • {$perm}");
            }
        }

        // All effective permissions
        $allPerms = $user->getAllPermissions()->pluck('name')->toArray();
        $this->newLine();
        $this->line('<fg=green>Total Effective Permissions: ' . count($allPerms) . '</>');
    }

    /**
     * Show permissions for specific role
     */
    private function showRolePermissions(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("Role '{$roleName}' not found!");
            return;
        }

        $this->line("🔐 <fg=cyan>Role: {$role->name}</>");
        $this->newLine();

        $permissions = $role->permissions->pluck('name')->toArray();
        
        if (empty($permissions)) {
            $this->warn('No permissions assigned to this role.');
            return;
        }

        // Group by module
        $grouped = [];
        foreach ($permissions as $perm) {
            $parts = explode('.', $perm);
            $module = $parts[0] ?? 'other';
            
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $perm;
        }

        foreach ($grouped as $module => $perms) {
            $this->line("<fg=yellow>" . ucfirst($module) . " (" . count($perms) . "):</>");
            foreach ($perms as $perm) {
                $this->line("  • {$perm}");
            }
            $this->newLine();
        }

        $this->line('<fg=green>Total: ' . count($permissions) . ' permissions</>');
    }
}
