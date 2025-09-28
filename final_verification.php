<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FINAL PERMISSION VERIFICATION ===\n\n";

// Test semua user dan pastikan mereka punya permission manage templates
$allUsers = App\Models\User::all();
$usersWithoutManageTemplates = [];

foreach($allUsers as $user) {
    $hasPermission = $user->can('manage templates');
    echo "User: {$user->email}\n";
    echo "  Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "  Has 'manage templates': " . ($hasPermission ? 'YES' : 'NO') . "\n";
    
    if(!$hasPermission) {
        $usersWithoutManageTemplates[] = $user->email;
    }
    
    // Cek juga owner access
    $ownerAccess = DB::table('user_logbook_access as ula')
        ->join('logbook_roles as lr', 'ula.logbook_role_id', '=', 'lr.id')
        ->join('logbook_template as lt', 'ula.logbook_template_id', '=', 'lt.id')
        ->where('ula.user_id', $user->id)
        ->where('lr.name', 'Owner')
        ->select('lt.name as template_name', 'lt.id as template_id')
        ->get();
        
    if($ownerAccess->count() > 0) {
        echo "  Owner access to:\n";
        foreach($ownerAccess as $access) {
            echo "    - {$access->template_name} (ID: {$access->template_id})\n";
        }
    }
    echo "\n";
}

echo "=== SUMMARY ===\n";
if(empty($usersWithoutManageTemplates)) {
    echo "✅ ALL users have 'manage templates' permission!\n";
    echo "✅ Problem resolved: Owner users can now access api/fields/batch\n";
} else {
    echo "❌ Users still missing 'manage templates' permission:\n";
    foreach($usersWithoutManageTemplates as $email) {
        echo "  - {$email}\n";
    }
}

// Test trigger dengan user baru
echo "\n=== TESTING DEFAULT ROLE TRIGGER ===\n";
$testUser = App\Models\User::create([
    'name' => 'Test Default Role',
    'email' => 'test.trigger@example.com',  
    'password' => bcrypt('password')
]);

$testUser->refresh(); // Reload untuk mendapat roles yang ter-assign otomatis
echo "Created test user: {$testUser->email}\n";
echo "Auto-assigned roles: " . $testUser->roles->pluck('name')->implode(', ') . "\n";
echo "Has 'manage templates': " . ($testUser->can('manage templates') ? 'YES' : 'NO') . "\n";

// Clean up test user
$testUser->delete();
echo "Test user cleaned up.\n";