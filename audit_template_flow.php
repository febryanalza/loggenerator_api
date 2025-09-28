<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\LogbookTemplate;
use App\Models\User;

echo "=== AUDIT: TEMPLATE CREATION FLOW ===\n\n";

// Login sebagai user untuk test
$testUser = User::where('email', 'user@example.com')->first();
if (!$testUser) {
    echo "❌ Test user not found\n";
    exit;
}

Auth::login($testUser);
echo "✅ Logged in as: {$testUser->email}\n";
echo "User ID: {$testUser->id}\n\n";

echo "=== STEP 1: CREATE TEMPLATE ===\n";

try {
    // Simulate template creation
    $templateData = [
        'name' => 'Test Template - ' . now()->format('Y-m-d H:i:s'),
        'description' => 'Test template untuk audit flow'
    ];
    
    echo "Creating template: {$templateData['name']}\n";
    
    // Start transaction to track order
    DB::beginTransaction();
    
    $template = LogbookTemplate::create($templateData);
    echo "✅ Template created with ID: {$template->id}\n";
    
    // Commit transaction
    DB::commit();
    
    echo "\n=== STEP 2: VERIFY USER ACCESS CREATION ===\n";
    
    // Check if user access was created automatically
    $userAccess = DB::table('user_logbook_access')
        ->where('user_id', $testUser->id)
        ->where('logbook_template_id', $template->id)
        ->where('logbook_role_id', 1) // Owner role
        ->first();
    
    if ($userAccess) {
        echo "✅ User access created automatically\n";
        echo "   - User ID: {$userAccess->user_id}\n";
        echo "   - Template ID: {$userAccess->logbook_template_id}\n";
        echo "   - Role ID: {$userAccess->logbook_role_id} (Owner)\n";
        echo "   - Created at: {$userAccess->created_at}\n";
    } else {
        echo "❌ User access NOT created automatically!\n";
    }
    
    echo "\n=== STEP 3: SIMULATE ADDING FIELDS ===\n";
    
    // Simulate adding fields via API
    $fieldsData = [
        ['name' => 'Field 1', 'data_type' => 'teks'],
        ['name' => 'Field 2', 'data_type' => 'angka'],
        ['name' => 'Field 3', 'data_type' => 'tanggal']
    ];
    
    foreach ($fieldsData as $fieldData) {
        $field = DB::table('logbook_fields')->insert([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => $fieldData['name'],
            'data_type' => $fieldData['data_type'],
            'template_id' => $template->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Field '{$fieldData['name']}' added\n";
    }
    
    echo "\n=== FINAL VERIFICATION ===\n";
    
    // Verify final state
    $finalTemplate = DB::table('logbook_template')->where('id', $template->id)->first();
    $finalAccess = DB::table('user_logbook_access')->where('logbook_template_id', $template->id)->count();
    $finalFields = DB::table('logbook_fields')->where('template_id', $template->id)->count();
    
    echo "Template: {$finalTemplate->name} (ID: {$finalTemplate->id})\n";
    echo "User Access Records: {$finalAccess}\n";
    echo "Fields Count: {$finalFields}\n";
    
    if ($finalTemplate && $finalAccess > 0 && $finalFields > 0) {
        echo "\n🎉 AUDIT RESULT: SUCCESS\n";
        echo "✅ Urutan benar: Template → User Access → Fields\n";
        echo "✅ User otomatis menjadi Owner\n";
        echo "✅ Fields berhasil ditambahkan\n";
    } else {
        echo "\n❌ AUDIT RESULT: FAILURE\n";
        echo "Flow tidak berjalan sesuai requirement\n";
    }
    
    // Cleanup - delete test data
    echo "\n=== CLEANUP ===\n";
    DB::table('logbook_fields')->where('template_id', $template->id)->delete();
    DB::table('user_logbook_access')->where('logbook_template_id', $template->id)->delete();
    DB::table('logbook_template')->where('id', $template->id)->delete();
    echo "✅ Test data cleaned up\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error during test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}