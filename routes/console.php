<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled tasks. These tasks will be
| run by Laravel's scheduler when the `schedule:run` command is invoked.
|
*/

// Cleanup expired logbook exports daily at 2:00 AM
// This will delete files and database records older than 7 days
Schedule::command('exports:cleanup --force')
    ->dailyAt('02:00')
    ->appendOutputTo(storage_path('logs/exports-cleanup.log'))
    ->emailOutputOnFailure(env('ADMIN_EMAIL'))
    ->description('Cleanup expired logbook export files');
