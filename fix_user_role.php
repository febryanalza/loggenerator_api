<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('email', 'febryana123@example.com')->first();
echo "User: {$user->email}\n";
echo "ID: {$user->id}\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
echo "Role count: " . $user->roles->count() . "\n";

// Assign User role to this user
$userRole = Spatie\Permission\Models\Role::where('name', 'User')->first();
if($userRole && !$user->hasRole('User')) {
    $user->assignRole('User');
    echo "Assigned 'User' role to {$user->email}\n";
    echo "Updated roles: " . $user->fresh()->roles->pluck('name')->implode(', ') . "\n";
    echo "Now has 'manage templates': " . ($user->fresh()->can('manage templates') ? 'YES' : 'NO') . "\n";
} else {
    echo "User already has role or role not found\n";
}