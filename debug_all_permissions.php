<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Test all users and their permissions
echo "=== ALL USERS PERMISSION ANALYSIS ===\n\n";

$users = App\Models\User::all();
foreach($users as $user) {
    echo "User: {$user->email} (ID: {$user->id})\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Has 'manage templates': " . ($user->can('manage templates') ? 'YES' : 'NO') . "\n";
    
    // Check logbook access as Owner
    $logbookAccess = DB::table('user_logbook_access')
        ->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
        ->where('user_logbook_access.user_id', $user->id)
        ->where('logbook_roles.name', 'Owner')
        ->get();
        
    if($logbookAccess->count() > 0) {
        echo "OWNER ACCESS to logbooks: " . $logbookAccess->count() . " templates\n";
        foreach($logbookAccess as $access) {
            echo "  - Template ID: {$access->logbook_template_id}\n";
        }
    }
    echo "---\n";
}

// Check specific permission middleware simulation
echo "\n=== PERMISSION MIDDLEWARE SIMULATION ===\n";
$testUser = App\Models\User::where('email', 'user@example.com')->first();
if($testUser) {
    echo "Testing user: {$testUser->email}\n";
    
    // Simulate middleware check
    $permissions = ['manage templates'];
    
    // Check direct permissions
    $directPermissions = DB::table('model_has_permissions')
        ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
        ->where('model_has_permissions.model_id', $testUser->id)
        ->where('model_has_permissions.model_type', App\Models\User::class)
        ->whereIn('permissions.name', $permissions)
        ->get();
    
    echo "Direct permissions: " . $directPermissions->count() . "\n";    
    
    // Check permissions through roles
    $rolePermissions = DB::table('model_has_roles')
        ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
        ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
        ->where('model_has_roles.model_id', $testUser->id)
        ->where('model_has_roles.model_type', App\Models\User::class)
        ->whereIn('permissions.name', $permissions)
        ->get();
        
    echo "Role permissions: " . $rolePermissions->count() . "\n";
    
    foreach($rolePermissions as $perm) {
        echo "  - {$perm->name}\n";
    }
    
    $hasPermission = $directPermissions->count() > 0 || $rolePermissions->count() > 0;
    echo "Final result: " . ($hasPermission ? 'ALLOWED' : 'DENIED') . "\n";
}