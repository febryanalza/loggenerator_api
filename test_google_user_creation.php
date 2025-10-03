<?php

require_once 'vendor/autoload.php';

// Load Laravel app untuk akses database dan models
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Helpers\GoogleAuthHelper;
use Illuminate\Support\Facades\Hash;

echo "=== Testing Google User Creation with Random Password ===\n\n";

try {
    // Test data Google user
    $googleUserData = [
        'name' => 'Test Google User',
        'email' => 'testgoogle_' . time() . '@example.com',
        'google_id' => 'google_test_' . time(),
        'avatar_url' => 'https://lh3.googleusercontent.com/test-avatar',
        'auth_provider' => 'google',
    ];

    echo "1. Generating random password for Google user:\n";
    $randomPassword = GoogleAuthHelper::generateGoogleUserPassword();
    echo "Generated password: $randomPassword\n";
    echo "Password length: " . strlen($randomPassword) . "\n\n";

    echo "2. Creating Google user with random password:\n";
    $user = User::create([
        'name' => $googleUserData['name'],
        'email' => $googleUserData['email'],
        'password' => Hash::make($randomPassword), // Hash the random password
        'google_id' => $googleUserData['google_id'],
        'avatar_url' => $googleUserData['avatar_url'],
        'auth_provider' => $googleUserData['auth_provider'],
        'google_verified_at' => now(),
        'email_verified_at' => now(),
        'status' => 'active',
        'last_login' => now(),
    ]);

    echo "✅ User created successfully!\n";
    echo "User ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Google ID: {$user->google_id}\n";
    echo "Auth Provider: {$user->auth_provider}\n";
    echo "Has Password: " . (!empty($user->password) ? 'YES' : 'NO') . "\n\n";

    echo "3. Testing password verification:\n";
    $passwordCheck = Hash::check($randomPassword, $user->password);
    echo "Password verification: " . ($passwordCheck ? 'PASS ✅' : 'FAIL ❌') . "\n\n";

    echo "4. Testing needsRandomPassword helper:\n";
    $needsPassword = GoogleAuthHelper::needsRandomPassword($user->password, $user->auth_provider);
    echo "Needs random password: " . ($needsPassword ? 'YES' : 'NO') . " (should be NO)\n\n";

    echo "5. Cleaning up - deleting test user:\n";
    $user->delete();
    echo "✅ Test user deleted successfully\n\n";

    echo "=== Test completed successfully! ===\n";
    echo "The Google authentication should now work without password constraint errors.\n";

} catch (Exception $e) {
    echo "❌ Error occurred: " . $e->getMessage() . "\n";
    echo "Error details:\n";
    echo $e->getTraceAsString() . "\n";
}