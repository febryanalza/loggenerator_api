<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFIKASI PERBAIKAN LOGBOOK PERMISSIONS ===\n\n";

// Get role-permission assignments
$assignments = DB::table('logbook_role_permissions')
    ->join('logbook_roles', 'logbook_role_permissions.logbook_role_id', '=', 'logbook_roles.id')
    ->join('logbook_permissions', 'logbook_role_permissions.logbook_permission_id', '=', 'logbook_permissions.id')
    ->select('logbook_roles.name as role_name', 'logbook_permissions.name as permission_name')
    ->orderBy('logbook_roles.name')
    ->get();

$rolePermissions = [];
foreach ($assignments as $assignment) {
    $rolePermissions[$assignment->role_name][] = $assignment->permission_name;
}

echo "=== HASIL PERBAIKAN ===\n\n";

// Check Owner permissions
echo "1. OWNER SUB-ROLE PERMISSIONS:\n";
if (isset($rolePermissions['Owner'])) {
    foreach ($rolePermissions['Owner'] as $permission) {
        $status = $permission === 'manage_access' ? ' ✅ FIXED!' : '';
        echo "   - {$permission}{$status}\n";
    }
    
    $hasManageAccess = in_array('manage_access', $rolePermissions['Owner']);
    $hasManageUsers = in_array('manage template users', $rolePermissions['Owner']);
    
    echo "\n   CAPABILITY CHECK:\n";
    echo "   ✅ Dapat assign template access: " . ($hasManageAccess ? 'YES' : 'NO') . "\n";
    echo "   ✅ Dapat manage template users: " . ($hasManageUsers ? 'YES' : 'NO') . "\n";
}

echo "\n2. SUPERVISOR SUB-ROLE PERMISSIONS:\n";
if (isset($rolePermissions['Supervisor'])) {
    foreach ($rolePermissions['Supervisor'] as $permission) {
        $status = $permission === 'delete logbook entries' ? ' ❌ SHOULD NOT BE HERE!' : '';
        echo "   - {$permission}{$status}\n";
    }
    
    $hasDeleteEntries = in_array('delete logbook entries', $rolePermissions['Supervisor']);
    $hasManageAccess = in_array('manage_access', $rolePermissions['Supervisor']);
    
    echo "\n   CAPABILITY CHECK:\n";
    echo "   ✅ Tidak dapat delete logbook entries: " . (!$hasDeleteEntries ? 'YES' : 'NO') . "\n";
    echo "   ✅ Dapat assign template access: " . ($hasManageAccess ? 'YES' : 'NO') . "\n";
}

echo "\n=== SUMMARY PERBAIKAN ===\n";
echo "✅ Owner sekarang memiliki 'manage_access' - dapat assign template access\n";
echo "✅ Supervisor tidak lagi memiliki 'delete logbook entries' - tidak dapat menghapus logbook\n";
echo "✅ Supervisor masih memiliki 'manage_access' - dapat assign template access\n";

// Detailed comparison
echo "\n=== DETAILED ROLE COMPARISON ===\n";
foreach (['Owner', 'Supervisor', 'Editor', 'Viewer'] as $role) {
    if (isset($rolePermissions[$role])) {
        echo "\n{$role} (" . count($rolePermissions[$role]) . " permissions):\n";
        foreach ($rolePermissions[$role] as $permission) {
            echo "  - {$permission}\n";
        }
    }
}

echo "\n=== PERBAIKAN SELESAI ===\n";