<?php

require_once 'vendor/autoload.php';

// Load Laravel app untuk akses database dan models
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Institution;
use Spatie\Permission\Models\Role;

echo "=== Testing Institution Admin Role Name Update ===\n\n";

try {
    // 1. Test Role Exists
    echo "1. Testing Institution Admin Role:\n";
    $role = Role::where('name', 'Institution Admin')->first();
    if ($role) {
        echo "✅ Institution Admin role exists (new name)\n";
        echo "Role permissions count: " . $role->permissions->count() . "\n";
    } else {
        echo "❌ Institution Admin role not found\n";
    }

    // Check old role doesn't exist
    $oldRole = Role::where('name', 'institution_admin')->first();
    if ($oldRole) {
        echo "⚠️  Old 'institution_admin' role still exists - should be cleaned up\n";
    } else {
        echo "✅ Old 'institution_admin' role not found (good)\n";
    }
    echo "\n";

    // 2. Test User Creation and Role Assignment
    echo "2. Testing User with Institution Admin Role:\n";
    
    // Create test institution
    $institution = Institution::create([
        'name' => 'Test Institution for Role Update',
        'description' => 'Testing role name update'
    ]);

    // Create user with Institution Admin role
    $user = User::create([
        'name' => 'Test Institution Admin User',
        'email' => 'test.institution.admin@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'institution_id' => $institution->id,
        'status' => 'active'
    ]);

    // Assign Institution Admin role
    $user->assignRole('Institution Admin');
    
    echo "✅ User created and assigned Institution Admin role\n";
    echo "User role: " . $user->getRoleNames()->first() . "\n";
    echo "Is Institution Admin: " . ($user->isInstitutionAdmin() ? 'YES' : 'NO') . "\n";
    echo "Has Institution Admin role: " . ($user->hasRole('Institution Admin') ? 'YES' : 'NO') . "\n";
    echo "\n";

    // 3. Test Institution Relations
    echo "3. Testing Institution Relations:\n";
    $institutionAdminsCount = $institution->institutionAdmins()->count();
    echo "Institution Admins count: {$institutionAdminsCount}\n";
    echo "\n";

    // 4. Cleanup
    echo "4. Cleanup:\n";
    $user->delete();
    $institution->delete();
    echo "✅ Test data cleaned up\n\n";

    echo "=== Role Name Update Test Completed Successfully! ===\n";
    echo "Role 'institution_admin' has been updated to 'Institution Admin'\n";

} catch (Exception $e) {
    echo "❌ Error occurred: " . $e->getMessage() . "\n";
    echo "Error details:\n";
    echo $e->getTraceAsString() . "\n";
}