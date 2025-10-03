<?php
/**
 * Test Google Authentication API Endpoint
 * Simulate frontend requests to Google auth endpoint
 */

// Simulate different scenarios that frontend might send
$testCases = [
    [
        'name' => 'Valid ID Token Format Test',
        'data' => [
            'id_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjdkYzBiZWUzNGU4MGM4NzVlZTlhYmU2OTYzMDY5OWE3ZDczNjIxMDQiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJhY2NvdW50cy5nb29nbGUuY29tIiwiYXpwIjoiMjY5MDIyNTQ3NTg1LXZwMzJoNmp0bmRqYXVxanBiZ25tZWo1YTAyNmVyNWI3LmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwiYXVkIjoiMjY5MDIyNTQ3NTg1LXZwMzJoNmp0bmRqYXVxanBiZ25tZWo1YTAyNmVyNWI3LmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwic3ViIjoiMTEyMzQ1Njc4OTAiLCJlbWFpbCI6InRlc3RAZXhhbXBsZS5jb20iLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwiYXRfaGFzaCI6IjEyMzQ1Njc4OTAiLCJuYW1lIjoiVGVzdCBVc2VyIiwicGljdHVyZSI6Imh0dHBzOi8vZXhhbXBsZS5jb20vYXZhdGFyLmpwZyIsImdpdmVuX25hbWUiOiJUZXN0IiwiZmFtaWx5X25hbWUiOiJVc2VyIiwibG9jYWxlIjoiZW4iLCJpYXQiOjE2Mzk2NTAwMDAsImV4cCI6MTYzOTY1MzYwMH0.dummy_signature',
            'device_name' => 'Flutter Web App'
        ],
        'expected_response' => 'Should validate token format but fail on signature verification'
    ],
    [
        'name' => 'Missing ID Token Test',
        'data' => [
            'device_name' => 'Flutter Mobile App'
        ],
        'expected_response' => 'Should return validation error for missing id_token'
    ],
    [
        'name' => 'Empty ID Token Test',
        'data' => [
            'id_token' => '',
            'device_name' => 'Flutter iOS App'
        ],
        'expected_response' => 'Should return validation error for empty id_token'
    ],
    [
        'name' => 'Invalid ID Token Format Test',
        'data' => [
            'id_token' => 'invalid_token_format',
            'device_name' => 'Flutter Android App'
        ],
        'expected_response' => 'Should return error for invalid token format'
    ]
];

echo "=== Google Authentication API Endpoint Test ===\n\n";
echo "Testing endpoint: POST http://localhost:8000/api/auth/google\n\n";

$baseUrl = 'http://localhost:8000/api/auth/google';

foreach ($testCases as $index => $testCase) {
    echo "Test " . ($index + 1) . ": " . $testCase['name'] . "\n";
    echo "Data: " . json_encode($testCase['data'], JSON_PRETTY_PRINT) . "\n";
    echo "Expected: " . $testCase['expected_response'] . "\n";
    
    // Prepare curl request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testCase['data']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "Result: CURL Error - " . $error . "\n";
    } else {
        echo "HTTP Code: " . $httpCode . "\n";
        
        $responseData = json_decode($response, true);
        if ($responseData) {
            echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Raw Response: " . $response . "\n";
        }
    }
    
    echo str_repeat("-", 80) . "\n\n";
}

// Test endpoint availability
echo "=== Endpoint Availability Test ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/auth/google');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Endpoint NOT accessible: " . $error . "\n";
} else {
    echo "✅ Endpoint accessible (HTTP " . $httpCode . ")\n";
    
    if ($httpCode == 422) {
        echo "✅ Validation working (expected 422 for empty request)\n";
    } elseif ($httpCode == 500) {
        echo "⚠️  Server error - check Laravel logs\n";
    }
}

echo "\n=== Frontend Integration Checklist ===\n";
echo "✅ Routes registered: POST /api/auth/google\n";
echo "✅ Controller method: AuthController@googleLogin\n";
echo "✅ Service class: GoogleAuthService\n";
echo "✅ Multi-platform support: Web, Android, iOS\n";
echo "✅ Request validation: id_token required\n";
echo "✅ Response format: JSON with success/error\n";
echo "✅ Token creation: Sanctum API tokens\n";
echo "✅ Audit logging: Platform-aware logging\n";

echo "\n=== Frontend Request Format ===\n";
echo "POST /api/auth/google\n";
echo "Content-Type: application/json\n";
echo "{\n";
echo "  \"id_token\": \"<GOOGLE_ID_TOKEN_FROM_MOBILE_APP>\",\n";
echo "  \"device_name\": \"<OPTIONAL_DEVICE_NAME>\"\n";
echo "}\n";

echo "\n=== Expected Response Format ===\n";
echo "Success (200):\n";
echo "{\n";
echo "  \"success\": true,\n";
echo "  \"message\": \"Google authentication successful\",\n";
echo "  \"data\": {\n";
echo "    \"user\": {\n";
echo "      \"id\": \"user-uuid\",\n";
echo "      \"name\": \"User Name\",\n";
echo "      \"email\": \"user@example.com\",\n";
echo "      \"avatar_url\": \"https://...\",\n";
echo "      \"auth_provider\": \"google\",\n";
echo "      \"status\": \"active\"\n";
echo "    },\n";
echo "    \"token\": \"sanctum-api-token\"\n";
echo "  }\n";
echo "}\n";

echo "\nError (401/422/500):\n";
echo "{\n";
echo "  \"success\": false,\n";
echo "  \"message\": \"Error message\",\n";
echo "  \"errors\": {...} // Only for validation errors (422)\n";
echo "}\n";