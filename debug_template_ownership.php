<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUG TEMPLATE OWNERSHIP ISSUE ===\n\n";

$templateId = '01998763-ddd8-71f4-adec-4d1bbd86d636';
echo "Template ID: {$templateId}\n\n";

// Cek apakah template ada
$template = DB::table('logbook_template')->where('id', $templateId)->first();
if ($template) {
    echo "✅ Template found: {$template->name}\n";
} else {
    echo "❌ Template NOT found!\n";
    exit;
}

// Cek semua user yang memiliki akses ke template ini
echo "\n=== USERS WITH ACCESS TO THIS TEMPLATE ===\n";
$userAccess = DB::table('user_logbook_access as ula')
    ->join('users as u', 'ula.user_id', '=', 'u.id')
    ->join('logbook_roles as lr', 'ula.logbook_role_id', '=', 'lr.id')
    ->where('ula.logbook_template_id', $templateId)
    ->select('u.email', 'u.id as user_id', 'lr.name as role_name', 'lr.id as role_id')
    ->get();

if ($userAccess->count() > 0) {
    foreach ($userAccess as $access) {
        echo "User: {$access->email}\n";
        echo "  - Role: {$access->role_name} (ID: {$access->role_id})\n";
        echo "  - User ID: {$access->user_id}\n";
        
        if ($access->role_id == 1) {
            echo "  - ✅ IS OWNER - Should have access\n";
        } else {
            echo "  - ❌ NOT OWNER - Will be blocked by middleware\n";
        }
        echo "\n";
    }
} else {
    echo "❌ No users have access to this template!\n";
}

// Cek aplikasi roles untuk users yang punya akses
echo "=== APPLICATION ROLES CHECK ===\n";
foreach ($userAccess as $access) {
    $appRoles = DB::table('model_has_roles as mhr')
        ->join('roles as r', 'mhr.role_id', '=', 'r.id')
        ->where('mhr.model_id', $access->user_id)
        ->where('mhr.model_type', 'App\Models\User')
        ->pluck('r.name')
        ->toArray();
        
    echo "User: {$access->email}\n";
    echo "  - Application Roles: " . implode(', ', $appRoles) . "\n";
    
    $isAdminOrSuperAdmin = in_array('Admin', $appRoles) || in_array('Super Admin', $appRoles);
    if ($isAdminOrSuperAdmin) {
        echo "  - ✅ IS ADMIN/SUPER ADMIN - Has override access\n";
    } else {
        echo "  - ℹ️  Regular user - Needs Owner role for access\n";
    }
    echo "\n";
}

// Test middleware logic simulation
echo "=== MIDDLEWARE LOGIC SIMULATION ===\n";
foreach ($userAccess as $access) {
    echo "Testing user: {$access->email}\n";
    
    // Check if Super Admin or Admin
    $isAdminOrSuperAdmin = DB::table('model_has_roles')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where('model_has_roles.model_id', $access->user_id)
        ->where('model_has_roles.model_type', 'App\Models\User')
        ->whereIn('roles.name', ['Super Admin', 'Admin'])
        ->exists();
    
    if ($isAdminOrSuperAdmin) {
        echo "  - Result: ✅ ALLOWED (Admin/Super Admin override)\n";
    } else {
        // Check if owner
        $isOwner = DB::table('user_logbook_access')
            ->where('user_id', $access->user_id)
            ->where('logbook_template_id', $templateId)
            ->where('logbook_role_id', 1) // Owner role
            ->exists();
            
        if ($isOwner) {
            echo "  - Result: ✅ ALLOWED (Template Owner)\n";
        } else {
            echo "  - Result: ❌ DENIED (Not Owner or Admin)\n";
        }
    }
    echo "\n";
}