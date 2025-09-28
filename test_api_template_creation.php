<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=== TEST TEMPLATE CREATION VIA API ===\n\n";

// 1. Cari user untuk test
$testUser = User::whereHas('roles', function($q) {
    $q->where('name', 'Super Admin');
})->first();

if (!$testUser) {
    echo "❌ ERROR: No Super Admin user found!\n";
    exit(1);
}

echo "👤 Test User: {$testUser->name} ({$testUser->email})\n\n";

// 2. Login via API untuk mendapatkan token
echo "🔐 Logging in via API...\n";

try {
    $loginResponse = Http::post('http://127.0.0.1:8000/api/login', [
        'email' => $testUser->email,
        'password' => 'password' // Assuming default password
    ]);

    if (!$loginResponse->successful()) {
        echo "❌ Login failed: " . $loginResponse->body() . "\n";
        exit(1);
    }

    $loginData = $loginResponse->json();
    $token = $loginData['data']['token'];
    
    echo "✅ Login successful! Token obtained.\n\n";

    // 3. Buat template via API
    echo "📝 Creating template via API...\n";
    
    $templateName = "API Test Template - " . date('Y-m-d H:i:s');
    
    $createResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->post('http://127.0.0.1:8000/api/templates', [
        'name' => $templateName,
        'description' => 'Template created via API to test owner assignment'
    ]);

    if (!$createResponse->successful()) {
        echo "❌ Template creation failed: " . $createResponse->body() . "\n";
        exit(1);
    }

    $templateData = $createResponse->json();
    $templateId = $templateData['data']['id'];
    
    echo "✅ Template created successfully!\n";
    echo "   Template ID: {$templateId}\n";
    echo "   Template Name: {$templateName}\n\n";

    // 4. Cek user access via database
    echo "🔍 Checking user access in database...\n";
    
    $userAccess = DB::table('user_logbook_access as ula')
        ->join('logbook_roles as lr', 'ula.logbook_role_id', '=', 'lr.id')
        ->join('users as u', 'ula.user_id', '=', 'u.id')
        ->where('ula.logbook_template_id', $templateId)
        ->select('u.name as user_name', 'u.id as user_id', 'lr.name as role_name', 'ula.created_at')
        ->get();
    
    if ($userAccess->count() > 0) {
        echo "✅ User access records found:\n";
        foreach ($userAccess as $access) {
            echo "   - {$access->user_name} ({$access->user_id}) = {$access->role_name}\n";
            echo "     Created: {$access->created_at}\n";
        }
        
        $ownerRecord = $userAccess->where('role_name', 'Owner')->first();
        if ($ownerRecord && $ownerRecord->user_id === $testUser->id) {
            echo "✅ SUCCESS: API template creator was automatically assigned as Owner!\n\n";
        } else {
            echo "❌ PROBLEM: API template creator was NOT assigned as Owner!\n\n";
        }
    } else {
        echo "❌ PROBLEM: No user access records found!\n\n";
    }

    // 5. Cek template details
    echo "📋 Checking template details...\n";
    
    $template = DB::table('logbook_template')->where('id', $templateId)->first();
    if ($template) {
        echo "   Created By: " . ($template->created_by ?? 'NOT SET') . "\n";
        echo "   Expected Creator: {$testUser->id}\n";
        
        if ($template->created_by === $testUser->id) {
            echo "✅ created_by field is correct!\n\n";
        } else {
            echo "❌ created_by field is incorrect or not set!\n\n";
        }
    }

    // 6. Test access via API
    echo "🧪 Testing template access via API...\n";
    
    $accessResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ])->get("http://127.0.0.1:8000/api/templates/user");

    if ($accessResponse->successful()) {
        $userTemplates = $accessResponse->json();
        $foundTemplate = false;
        
        foreach ($userTemplates['data'] as $template) {
            if ($template['id'] === $templateId) {
                $foundTemplate = true;
                echo "✅ Template found in user's accessible templates!\n";
                echo "   Role: {$template['role_name']}\n";
                break;
            }
        }
        
        if (!$foundTemplate) {
            echo "❌ Template NOT found in user's accessible templates!\n";
        }
    } else {
        echo "❌ Failed to fetch user templates: " . $accessResponse->body() . "\n";
    }

    // 7. Clean up
    echo "\n🧹 Cleaning up...\n";
    
    // Delete via API
    $deleteResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ])->delete("http://127.0.0.1:8000/api/templates/{$templateId}");

    if ($deleteResponse->successful()) {
        echo "✅ Template deleted successfully.\n";
    } else {
        echo "⚠️  Failed to delete template via API, cleaning up manually...\n";
        
        // Manual cleanup
        DB::table('user_logbook_access')->where('logbook_template_id', $templateId)->delete();
        DB::table('logbook_template')->where('id', $templateId)->delete();
        
        echo "✅ Manual cleanup completed.\n";
    }

    // 8. Logout
    echo "\n🔓 Logging out...\n";
    
    Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ])->post('http://127.0.0.1:8000/api/logout');
    
    echo "✅ Logged out successfully.\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}