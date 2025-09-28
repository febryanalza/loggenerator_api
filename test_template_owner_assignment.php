<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LogbookTemplate;
use App\Models\UserLogbookAccess;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== TEST TEMPLATE OWNER ASSIGNMENT ===\n\n";

// 1. Cek logbook_roles yang tersedia
echo "📋 Available Logbook Roles:\n";
$roles = DB::table('logbook_roles')->get();
foreach ($roles as $role) {
    echo "  - ID: {$role->id} | Name: {$role->name} | Description: {$role->description}\n";
}

if ($roles->isEmpty()) {
    echo "❌ ERROR: No logbook roles found! Please run seeders first.\n";
    exit(1);
}

$ownerRoleId = DB::table('logbook_roles')->where('name', 'Owner')->value('id');
if (!$ownerRoleId) {
    echo "❌ ERROR: Owner role not found in logbook_roles table!\n";
    exit(1);
}

echo "\n✅ Owner role found with ID: {$ownerRoleId}\n\n";

// 2. Pilih user untuk test (gunakan Super Admin)
$testUser = User::whereHas('roles', function($q) {
    $q->where('name', 'Super Admin');
})->first();

if (!$testUser) {
    echo "❌ ERROR: No Super Admin user found! Please run seeders first.\n";
    exit(1);
}

echo "👤 Test User: {$testUser->name} ({$testUser->email})\n";
echo "   User ID: {$testUser->id}\n\n";

// 3. Login sebagai user test
Auth::login($testUser);
echo "🔐 Logged in as: " . Auth::user()->name . "\n\n";

// 4. Buat template baru untuk test
echo "📝 Creating new template...\n";
$templateName = "Test Template - " . date('Y-m-d H:i:s');

try {
    // Simulate template creation process
    $template = LogbookTemplate::create([
        'name' => $templateName,
        'description' => 'Test template to verify owner assignment'
    ]);

    // Set created_by (seperti yang ada di controller)
    $template->created_by = Auth::id();
    $template->save();

    echo "✅ Template created successfully!\n";
    echo "   Template ID: {$template->id}\n";
    echo "   Template Name: {$template->name}\n";
    echo "   Created By: {$template->created_by}\n\n";

    // 5. Cek apakah user_logbook_access record dibuat otomatis
    echo "🔍 Checking user_logbook_access records...\n";
    
    $userAccess = UserLogbookAccess::where('logbook_template_id', $template->id)
        ->where('user_id', $testUser->id)
        ->with('logbookRole')
        ->get();
    
    if ($userAccess->count() > 0) {
        echo "✅ User access records found:\n";
        foreach ($userAccess as $access) {
            echo "   - User: {$access->user_id}\n";
            echo "   - Template: {$access->logbook_template_id}\n";
            echo "   - Role: {$access->logbookRole->name} (ID: {$access->logbook_role_id})\n";
            echo "   - Created: {$access->created_at}\n\n";
        }
        
        // Cek apakah ada Owner role
        $ownerAccess = $userAccess->where('logbook_role_id', $ownerRoleId)->first();
        if ($ownerAccess) {
            echo "✅ SUCCESS: Template creator was automatically assigned as Owner!\n";
            $success = true;
        } else {
            echo "❌ PROBLEM: Template creator was NOT assigned as Owner!\n";
            echo "   Available roles: " . $userAccess->pluck('logbookRole.name')->implode(', ') . "\n";
            $success = false;
        }
    } else {
        echo "❌ PROBLEM: No user_logbook_access records found for this template!\n";
        echo "   This means the model event is not working properly.\n";
        $success = false;
    }

    // 6. Test middleware simulation
    echo "\n🧪 Testing middleware access simulation...\n";
    
    // Simulate middleware check for Owner role
    $hasOwnerAccess = UserLogbookAccess::where('user_id', $testUser->id)
        ->where('logbook_template_id', $template->id)
        ->whereHas('logbookRole', function($q) {
            $q->where('name', 'Owner');
        })
        ->exists();
    
    echo "   Can access as Owner: " . ($hasOwnerAccess ? "✅ YES" : "❌ NO") . "\n";
    
    // Simulate middleware check for Editor,Supervisor,Owner roles
    $hasEditAccess = UserLogbookAccess::where('user_id', $testUser->id)
        ->where('logbook_template_id', $template->id)
        ->whereHas('logbookRole', function($q) {
            $q->whereIn('name', ['Editor', 'Supervisor', 'Owner']);
        })
        ->exists();
    
    echo "   Can access as Editor/Supervisor/Owner: " . ($hasEditAccess ? "✅ YES" : "❌ NO") . "\n";

    // 7. Clean up - hapus template test
    echo "\n🧹 Cleaning up test data...\n";
    
    // Hapus user access records
    UserLogbookAccess::where('logbook_template_id', $template->id)->delete();
    
    // Hapus template
    $template->delete();
    
    echo "✅ Test data cleaned up.\n\n";

    // 8. Summary
    echo "=== SUMMARY ===\n";
    if ($success) {
        echo "✅ Template owner assignment is working correctly!\n";
        echo "   - Template creator is automatically assigned as Owner\n";
        echo "   - Owner can access template for editing\n";
        echo "   - Middleware checks should work properly\n";
    } else {
        echo "❌ Template owner assignment has issues!\n";
        echo "   - Check LogbookTemplate model booted() method\n";
        echo "   - Verify logbook_roles seeding\n";
        echo "   - Check database constraints\n";
    }

} catch (\Exception $e) {
    echo "❌ ERROR during template creation: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Logout
Auth::logout();
echo "\n🔓 Logged out.\n";