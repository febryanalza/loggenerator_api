<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;

echo "=== FIXING ALL USERS WITHOUT ROLES ===\n\n";

$usersWithoutRoles = App\Models\User::doesntHave('roles')->get();
$userRole = Role::where('name', 'User')->first();

if(!$userRole) {
    echo "ERROR: 'User' role not found!\n";
    exit(1);
}

echo "Found {$usersWithoutRoles->count()} users without roles\n";

foreach($usersWithoutRoles as $user) {
    echo "Assigning 'User' role to: {$user->email}\n";
    $user->assignRole('User');
}

echo "\n=== VERIFICATION ===\n";
$stillWithoutRoles = App\Models\User::doesntHave('roles')->count();
echo "Users still without roles: {$stillWithoutRoles}\n";

// Test all users now have manage templates permission
echo "\n=== PERMISSION CHECK ===\n";
$usersWithoutManageTemplates = [];
foreach(App\Models\User::all() as $user) {
    if(!$user->can('manage templates')) {
        $usersWithoutManageTemplates[] = $user->email;
    }
}

if(empty($usersWithoutManageTemplates)) {
    echo "✓ ALL users now have 'manage templates' permission!\n";
} else {
    echo "✗ Users still missing 'manage templates' permission:\n";
    foreach($usersWithoutManageTemplates as $email) {
        echo "  - {$email}\n";
    }
}