<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUG USER febryan12@example.com ===\n\n";

$userEmail = 'febryan12@example.com';
$user = DB::table('users')->where('email', $userEmail)->first();

if (!$user) {
    echo "❌ User {$userEmail} not found!\n";
    
    // Show available users
    echo "\nAvailable users:\n";
    $users = DB::table('users')->select('email', 'id')->get();
    foreach ($users as $u) {
        echo "- {$u->email} (ID: {$u->id})\n";
    }
    exit;
}

echo "✅ User found: {$user->email}\n";
echo "User ID: {$user->id}\n\n";

// Cek aplikasi roles
$appRoles = DB::table('model_has_roles as mhr')
    ->join('roles as r', 'mhr.role_id', '=', 'r.id')
    ->where('mhr.model_id', $user->id)
    ->where('mhr.model_type', 'App\Models\User')
    ->select('r.name', 'r.id')
    ->get();

echo "Application Roles:\n";
if ($appRoles->count() > 0) {
    foreach ($appRoles as $role) {
        echo "- {$role->name} (ID: {$role->id})\n";
    }
} else {
    echo "❌ No application roles assigned!\n";
}

// Cek logbook access
$logbookAccess = DB::table('user_logbook_access as ula')
    ->join('logbook_template as lt', 'ula.logbook_template_id', '=', 'lt.id')
    ->join('logbook_roles as lr', 'ula.logbook_role_id', '=', 'lr.id')
    ->where('ula.user_id', $user->id)
    ->select('lt.name as template_name', 'lt.id as template_id', 'lr.name as role_name', 'lr.id as role_id')
    ->get();

echo "\nLogbook Access:\n";
if ($logbookAccess->count() > 0) {
    foreach ($logbookAccess as $access) {
        echo "- Template: {$access->template_name}\n";
        echo "  * Template ID: {$access->template_id}\n";
        echo "  * Role: {$access->role_name} (ID: {$access->role_id})\n";
        if ($access->role_id == 1) {
            echo "  * ✅ IS OWNER\n";
        }
        echo "\n";
    }
} else {
    echo "❌ No logbook access assigned!\n";
}

// Test middleware logic untuk user ini
echo "=== MIDDLEWARE TEST ===\n";

// Check if Super Admin or Admin
$isAdminOrSuperAdmin = DB::table('model_has_roles')
    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
    ->where('model_has_roles.model_id', $user->id)
    ->where('model_has_roles.model_type', 'App\Models\User')
    ->whereIn('roles.name', ['Super Admin', 'Admin'])
    ->exists();

echo "Is Admin/Super Admin: " . ($isAdminOrSuperAdmin ? 'YES' : 'NO') . "\n";

// Test template access
if ($logbookAccess->count() > 0) {
    foreach ($logbookAccess as $access) {
        if ($access->role_id == 1) { // Owner
            echo "Can manage template '{$access->template_name}': ✅ YES (Owner)\n";
        }
    }
}

// Assign User role if missing
if ($appRoles->count() == 0) {
    echo "\n=== FIXING MISSING ROLE ===\n";
    $userRole = DB::table('roles')->where('name', 'User')->first();
    if ($userRole) {
        DB::table('model_has_roles')->insert([
            'role_id' => $userRole->id,
            'model_type' => 'App\Models\User',
            'model_id' => $user->id
        ]);
        echo "✅ Assigned 'User' role to {$user->email}\n";
    }
}