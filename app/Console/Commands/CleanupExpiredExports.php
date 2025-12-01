<?php

namespace App\Console\Commands;

use App\Models\LogbookExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:cleanup 
                            {--days=7 : Number of days after which exports are considered expired}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired logbook export files and database records older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🧹 Logbook Export Cleanup");
        $this->info("========================");
        $this->newLine();

        // Get expired exports
        $expiredExports = LogbookExport::where(function ($query) use ($days) {
            $query->where('expires_at', '<', now())
                  ->orWhere('created_at', '<', now()->subDays($days));
        })->get();

        // Also find orphan files (files without database records)
        $orphanFiles = $this->findOrphanFiles($days);

        $totalExports = $expiredExports->count();
        $totalOrphans = count($orphanFiles);
        $totalFiles = $totalExports + $totalOrphans;

        if ($totalFiles === 0) {
            $this->info("✅ No expired exports or orphan files found. Storage is clean!");
            return Command::SUCCESS;
        }

        // Show summary
        $this->info("Found:");
        $this->line("  • {$totalExports} expired export records");
        $this->line("  • {$totalOrphans} orphan files (no database record)");
        $this->newLine();

        // Show details
        if ($totalExports > 0) {
            $this->info("Expired Exports:");
            $headers = ['ID', 'File Name', 'Size', 'Created At', 'Expires At'];
            $rows = $expiredExports->map(function ($export) {
                return [
                    substr($export->id, 0, 8) . '...',
                    strlen($export->file_name) > 40 
                        ? substr($export->file_name, 0, 37) . '...' 
                        : $export->file_name,
                    $export->formatted_file_size,
                    $export->created_at->format('Y-m-d H:i'),
                    $export->expires_at?->format('Y-m-d H:i') ?? 'N/A',
                ];
            })->toArray();
            $this->table($headers, $rows);
        }

        if ($totalOrphans > 0) {
            $this->info("Orphan Files:");
            foreach (array_slice($orphanFiles, 0, 10) as $file) {
                $this->line("  • {$file}");
            }
            if ($totalOrphans > 10) {
                $this->line("  ... and " . ($totalOrphans - 10) . " more");
            }
            $this->newLine();
        }

        // Calculate total size to be freed
        $totalSize = $expiredExports->sum('file_size');
        foreach ($orphanFiles as $file) {
            if (Storage::disk('public')->exists($file)) {
                $totalSize += Storage::disk('public')->size($file);
            }
        }
        $this->info("Total space to be freed: " . $this->formatFileSize($totalSize));
        $this->newLine();

        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No files will be deleted");
            return Command::SUCCESS;
        }

        // Confirm deletion
        if (!$force && !$this->confirm("Do you want to proceed with deletion?", true)) {
            $this->info("Operation cancelled.");
            return Command::SUCCESS;
        }

        // Perform cleanup
        $this->info("Cleaning up...");
        $this->newLine();

        $deletedExports = 0;
        $deletedOrphans = 0;
        $failedDeletions = 0;

        // Delete expired exports
        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->start();

        foreach ($expiredExports as $export) {
            try {
                // Delete file from storage
                if ($export->file_path && Storage::disk('public')->exists($export->file_path)) {
                    Storage::disk('public')->delete($export->file_path);
                }
                
                // Delete database record
                $export->delete();
                $deletedExports++;
            } catch (\Exception $e) {
                $failedDeletions++;
                Log::error('Failed to delete export: ' . $e->getMessage(), [
                    'export_id' => $export->id,
                    'file_path' => $export->file_path
                ]);
            }
            $progressBar->advance();
        }

        // Delete orphan files
        foreach ($orphanFiles as $file) {
            try {
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                    $deletedOrphans++;
                }
            } catch (\Exception $e) {
                $failedDeletions++;
                Log::error('Failed to delete orphan file: ' . $e->getMessage(), [
                    'file' => $file
                ]);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ Cleanup completed!");
        $this->line("  • Deleted {$deletedExports} export records");
        $this->line("  • Deleted {$deletedOrphans} orphan files");
        if ($failedDeletions > 0) {
            $this->warn("  • Failed to delete {$failedDeletions} items (check logs)");
        }
        $this->line("  • Freed approximately " . $this->formatFileSize($totalSize));

        // Log the cleanup
        Log::info('Logbook export cleanup completed', [
            'deleted_exports' => $deletedExports,
            'deleted_orphans' => $deletedOrphans,
            'failed_deletions' => $failedDeletions,
            'freed_space' => $totalSize,
        ]);

        return Command::SUCCESS;
    }

    /**
     * Find orphan files (files without database records)
     */
    private function findOrphanFiles(int $days): array
    {
        $orphanFiles = [];
        $exportPath = 'export_logbook';
        
        if (!Storage::disk('public')->exists($exportPath)) {
            return [];
        }

        $files = Storage::disk('public')->files($exportPath);
        $threshold = now()->subDays($days);

        foreach ($files as $file) {
            $fileName = basename($file);
            
            // Check if file exists in database
            $existsInDb = LogbookExport::where('file_name', $fileName)->exists();
            
            if (!$existsInDb) {
                // Check file age
                $lastModified = Storage::disk('public')->lastModified($file);
                if (\Carbon\Carbon::createFromTimestamp($lastModified)->lt($threshold)) {
                    $orphanFiles[] = $file;
                }
            }
        }

        return $orphanFiles;
    }

    /**
     * Format file size to human readable
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
