<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LOGBOOK ROLE & PERMISSION SYSTEM ===\n\n";

// Check Logbook Roles
echo "LOGBOOK ROLES:\n";
$logbookRoles = DB::table('logbook_roles')->get();
foreach ($logbookRoles as $role) {
    echo "- {$role->name}: {$role->description}\n";
}

echo "\nTOTAL LOGBOOK ROLES: " . $logbookRoles->count() . "\n\n";

// Check Logbook Permissions
echo "LOGBOOK PERMISSIONS:\n";
$logbookPermissions = DB::table('logbook_permissions')->get();
foreach ($logbookPermissions as $permission) {
    echo "- {$permission->name}: {$permission->description}\n";
}

echo "\nTOTAL LOGBOOK PERMISSIONS: " . $logbookPermissions->count() . "\n\n";

// Check Logbook Role-Permission Assignments
echo "LOGBOOK ROLE-PERMISSION ASSIGNMENTS:\n";
$assignments = DB::table('logbook_role_permissions')
    ->join('logbook_roles', 'logbook_role_permissions.logbook_role_id', '=', 'logbook_roles.id')
    ->join('logbook_permissions', 'logbook_role_permissions.logbook_permission_id', '=', 'logbook_permissions.id')
    ->select('logbook_roles.name as role_name', 'logbook_permissions.name as permission_name')
    ->orderBy('logbook_roles.name')
    ->get();

$currentRole = '';
foreach ($assignments as $assignment) {
    if ($currentRole !== $assignment->role_name) {
        $currentRole = $assignment->role_name;
        echo "\n{$currentRole}:\n";
    }
    echo "  - {$assignment->permission_name}\n";
}

echo "\n=== END OF LOGBOOK SYSTEM REPORT ===\n";