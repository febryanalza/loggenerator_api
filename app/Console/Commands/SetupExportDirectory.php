<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetupExportDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:setup 
                            {--check : Only check if directory exists and is writable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup export directory for logbook exports';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $exportPath = 'export_logbook';
        $fullPath = storage_path('app/public/' . $exportPath);
        $storagePath = storage_path('app/public');

        $this->info('Export Directory Setup');
        $this->line(str_repeat('=', 50));

        // Check if storage/app/public exists
        $this->line('');
        $this->info('1. Checking storage/app/public directory...');
        
        if (!is_dir($storagePath)) {
            if ($this->option('check')) {
                $this->error("   ✗ Directory does not exist: {$storagePath}");
                return Command::FAILURE;
            }
            
            $this->warn("   Directory does not exist. Creating...");
            if (!mkdir($storagePath, 0755, true)) {
                $this->error("   ✗ Failed to create directory: {$storagePath}");
                return Command::FAILURE;
            }
            $this->info("   ✓ Created: {$storagePath}");
        } else {
            $this->info("   ✓ Exists: {$storagePath}");
        }

        // Check if export_logbook directory exists
        $this->line('');
        $this->info('2. Checking export_logbook directory...');
        
        if (!is_dir($fullPath)) {
            if ($this->option('check')) {
                $this->error("   ✗ Directory does not exist: {$fullPath}");
                return Command::FAILURE;
            }
            
            $this->warn("   Directory does not exist. Creating...");
            if (!mkdir($fullPath, 0755, true)) {
                $this->error("   ✗ Failed to create directory: {$fullPath}");
                return Command::FAILURE;
            }
            $this->info("   ✓ Created: {$fullPath}");
        } else {
            $this->info("   ✓ Exists: {$fullPath}");
        }

        // Check if directory is writable
        $this->line('');
        $this->info('3. Checking write permissions...');
        
        if (!is_writable($fullPath)) {
            if ($this->option('check')) {
                $this->error("   ✗ Directory is not writable: {$fullPath}");
                return Command::FAILURE;
            }
            
            $this->warn("   Directory is not writable. Attempting to fix...");
            if (!chmod($fullPath, 0755)) {
                $this->error("   ✗ Failed to change permissions. Please run:");
                $this->line("      chmod -R 755 {$fullPath}");
                $this->line("      chown -R www-data:www-data {$fullPath}");
                return Command::FAILURE;
            }
            $this->info("   ✓ Permissions fixed");
        } else {
            $this->info("   ✓ Directory is writable");
        }

        // Test write capability
        $this->line('');
        $this->info('4. Testing write capability...');
        
        $testFile = $fullPath . '/.test_write_' . time();
        if (@file_put_contents($testFile, 'test') === false) {
            $this->error("   ✗ Cannot write to directory");
            return Command::FAILURE;
        }
        @unlink($testFile);
        $this->info("   ✓ Write test passed");

        // Check storage link
        $this->line('');
        $this->info('5. Checking storage symbolic link...');
        
        $publicStorageLink = public_path('storage');
        if (!file_exists($publicStorageLink)) {
            if ($this->option('check')) {
                $this->error("   ✗ Storage link does not exist");
                $this->line("   Run: php artisan storage:link");
                return Command::FAILURE;
            }
            
            $this->warn("   Storage link does not exist. Creating...");
            $this->call('storage:link');
            $this->info("   ✓ Storage link created");
        } else {
            $this->info("   ✓ Storage link exists");
        }

        // Summary
        $this->line('');
        $this->line(str_repeat('=', 50));
        $this->info('✓ Export directory setup completed successfully!');
        $this->line('');
        $this->line('Export path: ' . $fullPath);
        $this->line('Public URL:  ' . url('storage/' . $exportPath));
        
        return Command::SUCCESS;
    }
}
