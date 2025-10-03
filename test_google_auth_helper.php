<?php

require_once 'vendor/autoload.php';

use App\Helpers\GoogleAuthHelper;

echo "=== Testing GoogleAuthHelper ===\n\n";

// Test 1: Generate random password (default 20 chars)
echo "1. Testing generateRandomPassword() - default 20 chars:\n";
$password1 = GoogleAuthHelper::generateRandomPassword();
echo "Generated: $password1\n";
echo "Length: " . strlen($password1) . "\n";
echo "Contains only alphanumeric: " . (ctype_alnum($password1) ? 'YES' : 'NO') . "\n\n";

// Test 2: Generate random password with custom length
echo "2. Testing generateRandomPassword() - custom 15 chars:\n";
$password2 = GoogleAuthHelper::generateRandomPassword(15);
echo "Generated: $password2\n";
echo "Length: " . strlen($password2) . "\n\n";

// Test 3: Generate secure random password
echo "3. Testing generateSecureRandomPassword():\n";
$securePassword = GoogleAuthHelper::generateSecureRandomPassword();
echo "Generated: $securePassword\n";
echo "Length: " . strlen($securePassword) . "\n";
echo "Has lowercase: " . (preg_match('/[a-z]/', $securePassword) ? 'YES' : 'NO') . "\n";
echo "Has uppercase: " . (preg_match('/[A-Z]/', $securePassword) ? 'YES' : 'NO') . "\n";
echo "Has numbers: " . (preg_match('/[0-9]/', $securePassword) ? 'YES' : 'NO') . "\n";
echo "Has special chars: " . (preg_match('/[!@#$%^&*]/', $securePassword) ? 'YES' : 'NO') . "\n\n";

// Test 4: Generate Google user password
echo "4. Testing generateGoogleUserPassword():\n";
$googlePassword = GoogleAuthHelper::generateGoogleUserPassword();
echo "Generated: $googlePassword\n";
echo "Length: " . strlen($googlePassword) . "\n\n";

// Test 5: Test needsRandomPassword method
echo "5. Testing needsRandomPassword():\n";
echo "Empty password + google auth: " . (GoogleAuthHelper::needsRandomPassword(null, 'google') ? 'NEEDS' : 'NO NEED') . "\n";
echo "Has password + google auth: " . (GoogleAuthHelper::needsRandomPassword('hashed_password', 'google') ? 'NEEDS' : 'NO NEED') . "\n";
echo "Empty password + email auth: " . (GoogleAuthHelper::needsRandomPassword(null, 'email') ? 'NEEDS' : 'NO NEED') . "\n\n";

// Test 6: Multiple password generation to check uniqueness
echo "6. Testing password uniqueness (generating 5 passwords):\n";
$passwords = [];
for ($i = 1; $i <= 5; $i++) {
    $pwd = GoogleAuthHelper::generateGoogleUserPassword();
    $passwords[] = $pwd;
    echo "Password $i: $pwd\n";
}
$uniqueCount = count(array_unique($passwords));
echo "Unique passwords: $uniqueCount/5 " . ($uniqueCount === 5 ? '✅' : '❌') . "\n\n";

// Test 7: Hash test (simulating Laravel Hash::make)
echo "7. Testing with password hashing:\n";
$plainPassword = GoogleAuthHelper::generateGoogleUserPassword();
echo "Plain password: $plainPassword\n";

// Simulate Laravel Hash::make
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
echo "Hashed password: $hashedPassword\n";
echo "Verification: " . (password_verify($plainPassword, $hashedPassword) ? 'PASS ✅' : 'FAIL ❌') . "\n\n";

echo "=== All tests completed ===\n";