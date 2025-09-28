<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\Api\LogbookTemplateController;
use App\Http\Controllers\Api\LogbookFieldController;

echo "=== AUDIT: API ENDPOINT FLOW TEST ===\n\n";

// Login sebagai user
$testUser = User::where('email', 'user@example.com')->first();
Auth::login($testUser);
echo "✅ Logged in as: {$testUser->email}\n\n";

echo "=== STEP 1: CREATE TEMPLATE VIA API ===\n";

try {
    // Simulate HTTP request untuk create template
    $templateController = new LogbookTemplateController();
    
    $request = new Request();
    $request->merge([
        'name' => 'API Test Template - ' . now()->format('H:i:s'),
        'description' => 'Test template via API untuk audit flow'
    ]);
    $request->setUserResolver(function () use ($testUser) {
        return $testUser;
    });
    
    // Mock IP and User Agent
    $request->server->set('REMOTE_ADDR', '127.0.0.1');
    $request->headers->set('User-Agent', 'Test Agent');
    
    echo "Calling LogbookTemplateController@store...\n";
    $response = $templateController->store($request);
    $responseData = json_decode($response->getContent(), true);
    
    if ($responseData['success']) {
        echo "✅ Template created successfully via API\n";
        echo "Template ID: {$responseData['data']['id']}\n";
        echo "Template Name: {$responseData['data']['name']}\n";
        
        $templateId = $responseData['data']['id'];
        
        echo "\n=== STEP 2: VERIFY USER ACCESS VIA API ===\n";
        
        // Verify user access was created
        $userAccess = DB::table('user_logbook_access')
            ->where('user_id', $testUser->id)
            ->where('logbook_template_id', $templateId)
            ->where('logbook_role_id', 1)
            ->first();
        
        if ($userAccess) {
            echo "✅ User access verified - User is Owner\n";
            echo "Access ID: {$userAccess->id}\n";
        } else {
            echo "❌ User access NOT found!\n";
        }
        
        echo "\n=== STEP 3: ADD FIELDS VIA API ===\n";
        
        // Test batch field creation via API
        $fieldController = new LogbookFieldController();
        
        $fieldRequest = new Request();
        $fieldRequest->merge([
            'template_id' => $templateId,
            'fields' => [
                ['name' => 'API Field 1', 'data_type' => 'teks'],
                ['name' => 'API Field 2', 'data_type' => 'angka'],
                ['name' => 'API Field 3', 'data_type' => 'gambar']
            ]
        ]);
        $fieldRequest->setUserResolver(function () use ($testUser) {
            return $testUser;
        });
        $fieldRequest->server->set('REMOTE_ADDR', '127.0.0.1');
        $fieldRequest->headers->set('User-Agent', 'Test Agent');
        
        echo "Calling LogbookFieldController@storeBatch...\n";
        $fieldResponse = $fieldController->storeBatch($fieldRequest);
        $fieldResponseData = json_decode($fieldResponse->getContent(), true);
        
        if ($fieldResponseData['success']) {
            echo "✅ Fields created successfully via API\n";
            echo "Fields count: " . count($fieldResponseData['data']) . "\n";
            
            foreach ($fieldResponseData['data'] as $field) {
                echo "  - {$field['name']} ({$field['data_type']})\n";
            }
        } else {
            echo "❌ Fields creation failed\n";
            echo "Error: " . $fieldResponseData['message'] . "\n";
        }
        
        echo "\n=== FINAL API VERIFICATION ===\n";
        
        // Get final counts
        $finalFields = DB::table('logbook_fields')->where('template_id', $templateId)->count();
        $finalAccess = DB::table('user_logbook_access')->where('logbook_template_id', $templateId)->count();
        
        echo "Template ID: {$templateId}\n";
        echo "User Access Records: {$finalAccess}\n";
        echo "Fields Count: {$finalFields}\n";
        
        if ($finalAccess > 0 && $finalFields > 0) {
            echo "\n🎉 API AUDIT RESULT: SUCCESS\n";
            echo "✅ API flow berjalan sesuai requirement\n";
            echo "✅ Template → User Access → Fields (via API)\n";
        } else {
            echo "\n❌ API AUDIT RESULT: FAILURE\n";
        }
        
        // Cleanup
        echo "\n=== CLEANUP ===\n";
        DB::table('logbook_fields')->where('template_id', $templateId)->delete();
        DB::table('user_logbook_access')->where('logbook_template_id', $templateId)->delete();
        DB::table('logbook_template')->where('id', $templateId)->delete();
        echo "✅ Test data cleaned up\n";
        
    } else {
        echo "❌ Template creation failed via API\n";
        echo "Error: " . $responseData['message'] . "\n";
        if (isset($responseData['errors'])) {
            print_r($responseData['errors']);
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error during API test: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}