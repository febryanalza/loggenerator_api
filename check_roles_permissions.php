<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== ROLE & PERMISSION SYSTEM STATUS ===\n\n";

// Check Roles
echo "ROLES:\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

echo "\nTOTAL ROLES: " . $roles->count() . "\n\n";

// Check Permissions
echo "PERMISSIONS:\n";
$permissions = Permission::all();
foreach ($permissions as $permission) {
    echo "- {$permission->name}\n";
}

echo "\nTOTAL PERMISSIONS: " . $permissions->count() . "\n\n";

// Check Role-Permission Assignments
echo "ROLE-PERMISSION ASSIGNMENTS:\n";
$rolesWithPermissions = Role::with('permissions')->get();
foreach ($rolesWithPermissions as $role) {
    echo "\n{$role->name} ({$role->permissions->count()} permissions):\n";
    foreach ($role->permissions as $permission) {
        echo "  - {$permission->name}\n";
    }
}

echo "\n=== END OF REPORT ===\n";