<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Logbook Roles ===\n\n";

$roles = DB::table('logbook_roles')->get();

if ($roles->count() > 0) {
    echo "Found logbook roles:\n";
    foreach ($roles as $role) {
        echo "- {$role->name} (ID: {$role->id})\n";
    }
} else {
    echo "No logbook roles found in database.\n";
}

echo "\nTotal roles: " . $roles->count() . "\n";