<?php

require_once 'vendor/autoload.php';

// Load Laravel app untuk akses database dan models
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== Cleaning Up Old Institution Admin Role ===\n\n";

try {
    // 1. Find old and new roles
    $oldRole = Role::where('name', 'institution_admin')->first();
    $newRole = Role::where('name', 'Institution Admin')->first();

    if (!$newRole) {
        echo "❌ New 'Institution Admin' role not found. Please run the seeder first.\n";
        exit(1);
    }

    echo "✅ New 'Institution Admin' role found\n";

    if (!$oldRole) {
        echo "✅ Old 'institution_admin' role not found. Nothing to clean up.\n";
        exit(0);
    }

    echo "⚠️  Old 'institution_admin' role found. Starting cleanup...\n\n";

    // 2. Find users with old role
    $usersWithOldRole = User::role('institution_admin')->get();
    echo "Users with old role: " . $usersWithOldRole->count() . "\n";

    // 3. Migrate users to new role
    foreach ($usersWithOldRole as $user) {
        echo "Migrating user: {$user->name} ({$user->email})\n";
        
        // Remove old role and assign new role
        $user->removeRole('institution_admin');
        $user->assignRole('Institution Admin');
        
        echo "  ✅ User migrated successfully\n";
    }

    // 4. Remove old role
    echo "\nRemoving old role...\n";
    $oldRole->delete();
    echo "✅ Old 'institution_admin' role deleted\n\n";

    // 5. Verify cleanup
    echo "=== Verification ===\n";
    $oldRoleExists = Role::where('name', 'institution_admin')->exists();
    $newRoleExists = Role::where('name', 'Institution Admin')->exists();
    
    echo "Old role exists: " . ($oldRoleExists ? 'YES ❌' : 'NO ✅') . "\n";
    echo "New role exists: " . ($newRoleExists ? 'YES ✅' : 'NO ❌') . "\n";
    
    $institutionAdminsCount = User::role('Institution Admin')->count();
    echo "Users with Institution Admin role: {$institutionAdminsCount}\n";

    echo "\n=== Cleanup Completed Successfully! ===\n";

} catch (Exception $e) {
    echo "❌ Error occurred: " . $e->getMessage() . "\n";
    echo "Error details:\n";
    echo $e->getTraceAsString() . "\n";
}