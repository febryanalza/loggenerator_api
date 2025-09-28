<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUG: LOGBOOK PERMISSION MAPPING ===\n\n";

// Get all logbook permissions with their IDs
$permissions = DB::table('logbook_permissions')->orderBy('id')->get();

echo "LOGBOOK PERMISSIONS WITH IDs:\n";
foreach ($permissions as $perm) {
    echo "ID {$perm->id}: {$perm->name} - {$perm->description}\n";
}

echo "\n=== CURRENT ROLE-PERMISSION ASSIGNMENTS (BY ID) ===\n";
$assignments = DB::table('logbook_role_permissions')
    ->join('logbook_roles', 'logbook_role_permissions.logbook_role_id', '=', 'logbook_roles.id')
    ->select('logbook_roles.name as role_name', 'logbook_role_permissions.logbook_permission_id as perm_id')
    ->orderBy('logbook_roles.name')
    ->get();

$rolePermissionIds = [];
foreach ($assignments as $assignment) {
    $rolePermissionIds[$assignment->role_name][] = $assignment->perm_id;
}

foreach ($rolePermissionIds as $roleName => $permIds) {
    echo "\n{$roleName} (IDs: " . implode(', ', $permIds) . "):\n";
    foreach ($permIds as $permId) {
        $permission = $permissions->where('id', $permId)->first();
        if ($permission) {
            echo "  ID {$permId}: {$permission->name}\n";
        }
    }
}

echo "\n=== ISSUES IDENTIFIED ===\n";
echo "1. Owner needs 'manage_access' permission to assign template access\n";
echo "2. Supervisor should NOT have 'delete logbook entries' permission\n";

echo "\n=== END DEBUG ===\n";