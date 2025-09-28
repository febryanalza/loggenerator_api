<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Api\LogbookTemplateController;
use App\Models\LogbookTemplate;
use App\Models\UserLogbookAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== TEST TEMPLATE CREATION VIA CONTROLLER ===\n\n";

// 1. Setup test user
$testUser = User::whereHas('roles', function($q) {
    $q->where('name', 'Super Admin');
})->first();

if (!$testUser) {
    echo "❌ ERROR: No Super Admin user found!\n";
    exit(1);
}

echo "👤 Test User: {$testUser->name} ({$testUser->email})\n";
echo "   User ID: {$testUser->id}\n\n";

// 2. Login sebagai test user
Auth::login($testUser);
echo "🔐 Logged in as: " . Auth::user()->name . "\n\n";

// 3. Buat request object
$request = new Request();
$request->merge([
    'name' => 'Controller Test Template - ' . date('Y-m-d H:i:s'),
    'description' => 'Template created via controller to test owner assignment'
]);

// Set additional request properties untuk simulasi
$request->setUserResolver(function () use ($testUser) {
    return $testUser;
});

// 4. Instantiate controller dan call store method
echo "📝 Creating template via controller...\n";

try {
    $controller = new LogbookTemplateController();
    $response = $controller->store($request);
    
    // Get response data
    $responseData = $response->getData(true);
    
    if ($response->getStatusCode() === 201) {
        echo "✅ Template created successfully!\n";
        
        $templateData = $responseData['data'];
        $templateId = $templateData['id'];
        $templateName = $templateData['name'];
        
        echo "   Template ID: {$templateId}\n";
        echo "   Template Name: {$templateName}\n\n";
        
        // 5. Cek database records
        echo "🔍 Checking database records...\n";
        
        // Cek template record
        $template = LogbookTemplate::find($templateId);
        if ($template) {
            echo "✅ Template found in database\n";
            echo "   Created By: " . ($template->created_by ?? 'NOT SET') . "\n";
            echo "   Expected: {$testUser->id}\n";
            
            if ($template->created_by === $testUser->id) {
                echo "✅ created_by is correct!\n";
            } else {
                echo "❌ created_by is incorrect or not set!\n";
            }
        }
        
        // Cek user access records
        $userAccess = UserLogbookAccess::where('logbook_template_id', $templateId)
            ->with(['user', 'logbookRole'])
            ->get();
        
        if ($userAccess->count() > 0) {
            echo "✅ User access records found:\n";
            $hasOwnerAccess = false;
            
            foreach ($userAccess as $access) {
                $userName = $access->user ? $access->user->name : 'Unknown';
                $roleName = $access->logbookRole ? $access->logbookRole->name : 'Unknown';
                
                echo "   - {$userName} ({$access->user_id}) = {$roleName}\n";
                
                if ($access->user_id === $testUser->id && $roleName === 'Owner') {
                    $hasOwnerAccess = true;
                }
            }
            
            if ($hasOwnerAccess) {
                echo "✅ SUCCESS: Template creator has Owner access!\n";
            } else {
                echo "❌ PROBLEM: Template creator does NOT have Owner access!\n";
            }
        } else {
            echo "❌ PROBLEM: No user access records found!\n";
        }
        
        // 6. Test middleware simulation
        echo "\n🧪 Testing middleware simulation...\n";
        
        $canAccessAsOwner = UserLogbookAccess::where('user_id', $testUser->id)
            ->where('logbook_template_id', $templateId)
            ->whereHas('logbookRole', function($q) {
                $q->where('name', 'Owner');
            })
            ->exists();
        
        echo "   Can access as Owner: " . ($canAccessAsOwner ? "✅ YES" : "❌ NO") . "\n";
        
        $canEditEntry = UserLogbookAccess::where('user_id', $testUser->id)
            ->where('logbook_template_id', $templateId)
            ->whereHas('logbookRole', function($q) {
                $q->whereIn('name', ['Editor', 'Supervisor', 'Owner']);
            })
            ->exists();
        
        echo "   Can edit entries: " . ($canEditEntry ? "✅ YES" : "❌ NO") . "\n";
        
        // 7. Cleanup
        echo "\n🧹 Cleaning up test data...\n";
        
        // Delete user access
        UserLogbookAccess::where('logbook_template_id', $templateId)->delete();
        
        // Delete template
        $template->delete();
        
        echo "✅ Test data cleaned up.\n";
        
    } else {
        echo "❌ Template creation failed!\n";
        echo "   Status Code: " . $response->getStatusCode() . "\n";
        echo "   Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR during template creation: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// 8. Logout
Auth::logout();
echo "\n🔓 Logged out.\n";

echo "\n=== FINAL VERIFICATION ===\n";

// Count existing templates with proper ownership
$totalTemplates = LogbookTemplate::count();
$templatesWithOwner = DB::table('logbook_template as lt')
    ->join('user_logbook_access as ula', 'lt.id', '=', 'ula.logbook_template_id')
    ->join('logbook_roles as lr', 'ula.logbook_role_id', '=', 'lr.id')
    ->where('lr.name', 'Owner')
    ->distinct('lt.id')
    ->count('lt.id');

echo "📊 Total templates: {$totalTemplates}\n";
echo "📊 Templates with Owner: {$templatesWithOwner}\n";

if ($totalTemplates === $templatesWithOwner) {
    echo "✅ All templates have proper Owner assignment!\n";
} else {
    $missing = $totalTemplates - $templatesWithOwner;
    echo "⚠️  {$missing} templates are missing Owner assignment.\n";
}

echo "\n✅ Owner assignment verification completed!\n";