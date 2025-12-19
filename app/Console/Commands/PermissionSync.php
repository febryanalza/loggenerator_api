<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\PermissionRegistry;

class PermissionSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync 
                            {--force : Force sync without confirmation}
                            {--show-only : Only show diff without syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from PermissionRegistry to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════╗');
        $this->info('║   Permission Synchronization Tool                 ║');
        $this->info('╚════════════════════════════════════════════════════╝');
        $this->newLine();

        // Get registry and database permissions
        $registryPermissions = PermissionRegistry::getPermissionNames();
        $dbPermissions = Permission::pluck('name')->toArray();
        
        // Calculate diff
        $missing = array_diff($registryPermissions, $dbPermissions);
        $extra = array_diff($dbPermissions, $registryPermissions);
        
        // Display status
        $this->table(
            ['Source', 'Count'],
            [
                ['Permission Registry (Code)', count($registryPermissions)],
                ['Database', count($dbPermissions)],
                ['Missing in DB', count($missing)],
                ['Extra in DB', count($extra)],
            ]
        );

        if (empty($missing) && empty($extra)) {
            $this->info('✓ Permissions are in sync!');
            return Command::SUCCESS;
        }

        // Show missing permissions
        if (!empty($missing)) {
            $this->newLine();
            $this->warn('⚠ Missing in Database (' . count($missing) . '):');
            foreach ($missing as $perm) {
                $this->line("  - {$perm}");
            }
        }

        // Show extra permissions
        if (!empty($extra)) {
            $this->newLine();
            $this->warn('⚠ Extra in Database (not in registry) (' . count($extra) . '):');
            foreach ($extra as $perm) {
                $this->line("  - {$perm}");
            }
        }

        // If show-only mode, exit
        if ($this->option('show-only')) {
            $this->newLine();
            $this->info('Showing diff only (use without --show-only to sync)');
            return Command::SUCCESS;
        }

        // Confirm sync
        if (!$this->option('force')) {
            $this->newLine();
            if (!$this->confirm('Do you want to sync these permissions?', true)) {
                $this->info('Sync cancelled.');
                return Command::SUCCESS;
            }
        }

        // Perform sync
        $this->newLine();
        $this->info('Starting sync...');
        
        $created = 0;
        $skipped = 0;

        // Create missing permissions
        foreach ($missing as $permName) {
            try {
                Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'web'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
                $this->line("  ✓ Created: {$permName}");
                $created++;
            } catch (\Exception $e) {
                $this->error("  ✗ Failed: {$permName} - " . $e->getMessage());
                $skipped++;
            }
        }

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->newLine();
        $this->info("╔════════════════════════════════════════════════════╗");
        $this->info("║   Sync Complete!                                   ║");
        $this->info("╠════════════════════════════════════════════════════╣");
        $this->info("║   Created: {$created}");
        $this->info("║   Skipped: {$skipped}");
        $this->info("║   Extra in DB (not removed): " . count($extra));
        $this->info("╚════════════════════════════════════════════════════╝");

        if (!empty($extra)) {
            $this->newLine();
            $this->warn('Note: Extra permissions in database were NOT removed.');
            $this->warn('To remove them, use: php artisan permission:clean');
        }

        return Command::SUCCESS;
    }
}
