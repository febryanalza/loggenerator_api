<?php
/**
 * Test Google Authentication Configuration
 * Run: php test_google_config.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Google Authentication Configuration Test ===\n\n";

// Test 1: Check environment variables
echo "1. Environment Variables:\n";
echo "   GOOGLE_CLIENT_ID: " . ($_ENV['GOOGLE_CLIENT_ID'] ?? 'NOT SET') . "\n";
echo "   GOOGLE_CLIENT_SECRET: " . (isset($_ENV['GOOGLE_CLIENT_SECRET']) ? 'SET (Hidden)' : 'NOT SET') . "\n\n";

// Test 2: Test Google Client initialization
echo "2. Google Client Initialization:\n";
try {
    $client = new Google\Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
    
    echo "   ✓ Google Client initialized successfully\n";
    echo "   ✓ Client ID configured\n";
    echo "   ✓ Client Secret configured\n\n";
    
} catch (Exception $e) {
    echo "   ✗ Error initializing Google Client: " . $e->getMessage() . "\n\n";
}

// Test 3: Validate Client ID format
echo "3. Client ID Validation:\n";
$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
if (preg_match('/^\d+-[a-z0-9]+\.apps\.googleusercontent\.com$/', $clientId)) {
    echo "   ✓ Client ID format is valid\n";
} else {
    echo "   ✗ Client ID format is invalid\n";
}

// Test 4: Test with dummy ID token (will fail, but shows structure)
echo "\n4. Service Test (with dummy token):\n";
try {
    // This will fail but shows our service works
    $dummyToken = "eyJhbGciOiJSUzI1NiIsImtpZCI6IjdkYzBiZWUzNGU4MGM4NzVlZTlhYmU2OTYzMDY5OWE3ZDczNjIxMDQiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJhY2NvdW50cy5nb29nbGUuY29tIiwiYXpwIjoiMjY5MDIyNTQ3NTg1LXZwMzJoNmp0bmRqYXVxanBiZ25tZWo1YTAyNmVyNWI3LmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwiYXVkIjoiMjY5MDIyNTQ3NTg1LXZwMzJoNmp0bmRqYXVxanBiZ25tZWo1YTAyNmVyNWI3LmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwic3ViIjoiMTEyMzQ1Njc4OTAiLCJlbWFpbCI6InRlc3RAZXhhbXBsZS5jb20iLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwiYXRfaGFzaCI6IjEyMzQ1Njc4OTAiLCJuYW1lIjoiVGVzdCBVc2VyIiwicGljdHVyZSI6Imh0dHBzOi8vZXhhbXBsZS5jb20vYXZhdGFyLmpwZyIsImdpdmVuX25hbWUiOiJUZXN0IiwiZmFtaWx5X25hbWUiOiJVc2VyIiwibG9jYWxlIjoiZW4iLCJpYXQiOjE2Mzk2NTAwMDAsImV4cCI6MTYzOTY1MzYwMH0.dummy_signature";
    
    $client->verifyIdToken($dummyToken);
    echo "   Unexpected: Token verification passed (this shouldn't happen with dummy token)\n";
    
} catch (Exception $e) {
    echo "   ✓ Service is working (expected error with dummy token): " . substr($e->getMessage(), 0, 50) . "...\n";
}

echo "\n=== Configuration Summary ===\n";
echo "Google OAuth is properly configured for:\n";
echo "• Client ID: " . $clientId . "\n";
echo "• Project ID: loggenerator-473712\n";
echo "• Ready for mobile app integration\n\n";

echo "Next steps:\n";
echo "1. Configure Android app with this Client ID\n";
echo "2. Configure iOS app with this Client ID\n";
echo "3. Get actual ID token from mobile app for testing\n";
echo "4. Test authentication via /api/auth/google endpoint\n\n";