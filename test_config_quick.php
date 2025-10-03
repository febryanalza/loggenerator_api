<?php
/**
 * Quick test for Google Auth configuration
 */

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Google Multi-Client Configuration Test ===\n\n";

// Test configuration
$allowedClientIds = config('services.google.allowed_client_ids');
echo "Allowed Client IDs:\n";
foreach ($allowedClientIds as $index => $clientId) {
    $platform = match($index) {
        0 => 'Web',
        1 => 'Android', 
        2 => 'iOS',
        default => 'Unknown'
    };
    echo "  [{$index}] {$platform}: {$clientId}\n";
}

echo "\nTesting GoogleAuthService:\n";
try {
    $service = new App\Services\GoogleAuthService();
    echo "  ✓ Service initialized successfully\n";
    echo "  ✓ Client IDs count: " . count($service->getAllowedClientIds()) . "\n";
    
    // Test each client ID validation
    foreach ($allowedClientIds as $clientId) {
        $isAllowed = $service->isClientIdAllowed($clientId);
        echo "  " . ($isAllowed ? "✓" : "✗") . " Client ID allowed: " . substr($clientId, 0, 30) . "...\n";
    }
    
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";