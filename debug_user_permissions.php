<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test user permissions
$user = App\Models\User::where('email', 'user@example.com')->first();
if($user) {
    echo "User ID: {$user->id}\n";
    echo "User Email: {$user->email}\n";
    echo "User Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";  
    echo "User Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";
    echo "Has 'manage templates': " . ($user->can('manage templates') ? 'YES' : 'NO') . "\n";
    echo "\n";
    
    // Check specific permission details
    echo "=== Permission Check Details ===\n";
    $hasPermission = $user->hasPermissionTo('manage templates');
    echo "hasPermissionTo('manage templates'): " . ($hasPermission ? 'YES' : 'NO') . "\n";
    
    // Check roles and their permissions
    foreach($user->roles as $role) {
        echo "\nRole: {$role->name}\n";
        echo "Role Permissions: " . $role->permissions->pluck('name')->implode(', ') . "\n";
    }
    
} else {
    echo "User admin@admin.com not found\n";
    
    echo "Available users:\n";
    foreach(App\Models\User::all() as $u) {
        echo "- {$u->email} (ID: {$u->id})\n";
    }
}