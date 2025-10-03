<?php
/**
 * Check database structure and identify Google auth issues
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Database Structure Analysis ===\n\n";

try {
    // Check users table columns
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
    echo "1. Users Table Columns:\n";
    foreach ($columns as $column) {
        echo "   - {$column}\n";
    }
    
    // Check if Google fields exist
    $googleFields = ['google_id', 'avatar_url', 'auth_provider', 'google_verified_at'];
    echo "\n2. Google Fields Check:\n";
    foreach ($googleFields as $field) {
        $exists = in_array($field, $columns) ? '✅' : '❌';
        echo "   {$exists} {$field}\n";
    }
    
    // Test Google Auth Service configuration
    echo "\n3. Google Auth Service Test:\n";
    try {
        $service = new App\Services\GoogleAuthService();
        $clientIds = $service->getAllowedClientIds();
        echo "   ✅ Service initialized\n";
        echo "   ✅ Client IDs count: " . count($clientIds) . "\n";
        
        foreach ($clientIds as $index => $clientId) {
            $platform = match($index) {
                0 => 'Web',
                1 => 'Android',
                2 => 'iOS',
                default => 'Unknown'
            };
            echo "   ✅ {$platform}: " . substr($clientId, 0, 30) . "...\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Service Error: " . $e->getMessage() . "\n";
    }
    
    // Test User model fillable fields
    echo "\n4. User Model Configuration:\n";
    $user = new App\Models\User();
    $fillable = $user->getFillable();
    
    foreach ($googleFields as $field) {
        $exists = in_array($field, $fillable) ? '✅' : '❌';
        echo "   {$exists} Fillable: {$field}\n";
    }
    
    // Test database connection with Google fields
    echo "\n5. Database Insert Test:\n";
    try {
        // Try to create a test record (will rollback)
        \Illuminate\Support\Facades\DB::transaction(function () {
            $testUser = App\Models\User::create([
                'name' => 'Test Google User',
                'email' => 'test.google@example.com',
                'google_id' => '123456789',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'auth_provider' => 'google',
                'google_verified_at' => now(),
                'status' => 'active',
                'last_login' => now(),
            ]);
            
            echo "   ✅ Test user creation successful\n";
            echo "   ✅ Google fields accepted\n";
            
            // Rollback the test
            throw new Exception('Test rollback');
        });
    } catch (Exception $e) {
        if (str_contains($e->getMessage(), 'Test rollback')) {
            echo "   ✅ Database schema compatible\n";
        } else {
            echo "   ❌ Database Error: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Analysis Complete ===\n";