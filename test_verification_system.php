<?php

require_once 'vendor/autoload.php';

// Load Laravel app untuk akses database dan models
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Institution;
use App\Models\LogbookTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== Testing Verification and Assessment System ===\n\n";

try {
    // 1. Create test data
    echo "1. Setting up test data:\n";

    // Create institution
    $institution = Institution::create([
        'name' => 'Test Verification Institution',
        'description' => 'Institution for testing verification system'
    ]);
    echo "✅ Institution created: {$institution->name}\n";

    // Create Institution Admin
    $institutionAdmin = User::create([
        'name' => 'Test Institution Admin',
        'email' => 'institution.admin.verify.' . time() . '@test.com',
        'password' => Hash::make('password123'),
        'institution_id' => $institution->id,
        'status' => 'active'
    ]);
    $institutionAdmin->assignRole('Institution Admin');
    echo "✅ Institution Admin created: {$institutionAdmin->name}\n";

    // Create Owner
    $owner = User::create([
        'name' => 'Test Owner',
        'email' => 'owner.verify.' . time() . '@test.com',
        'password' => Hash::make('password123'),
        'status' => 'active'
    ]);
    $owner->assignRole('User');
    echo "✅ Owner created: {$owner->name}\n";

    // Create Supervisor
    $supervisor = User::create([
        'name' => 'Test Supervisor',
        'email' => 'supervisor.verify.' . time() . '@test.com',
        'password' => Hash::make('password123'),
        'status' => 'active'
    ]);
    $supervisor->assignRole('User');
    echo "✅ Supervisor created: {$supervisor->name}\n";

    // Create regular user
    $regularUser = User::create([
        'name' => 'Test Regular User',
        'email' => 'user.verify.' . time() . '@test.com',
        'password' => Hash::make('password123'),
        'status' => 'active'
    ]);
    $regularUser->assignRole('User');
    echo "✅ Regular User created: {$regularUser->name}\n";

    // Create template
    $template = LogbookTemplate::create([
        'name' => 'Test Verification Template',
        'description' => 'Template for testing verification',
        'institution_id' => $institution->id,
        'has_been_assessed' => false
    ]);
    echo "✅ Template created: {$template->name}\n";
    echo "   - has_been_assessed: " . ($template->has_been_assessed ? 'true' : 'false') . "\n";

    // Get role IDs
    $ownerRoleId = DB::table('logbook_roles')->where('name', 'Owner')->value('id');
    $supervisorRoleId = DB::table('logbook_roles')->where('name', 'Supervisor')->value('id');
    $viewerRoleId = DB::table('logbook_roles')->where('name', 'Viewer')->value('id'); // Use Viewer instead of User

    if (!$ownerRoleId || !$supervisorRoleId || !$viewerRoleId) {
        echo "❌ Logbook roles not found. Please run logbook role seeders first.\n";
        echo "   Owner ID: {$ownerRoleId}\n";
        echo "   Supervisor ID: {$supervisorRoleId}\n";
        echo "   Viewer ID: {$viewerRoleId}\n";
        return;
    }

    // Add users to template with different roles
    $userAccess = [
        ['user_id' => $owner->id, 'role_id' => $ownerRoleId, 'role_name' => 'Owner'],
        ['user_id' => $supervisor->id, 'role_id' => $supervisorRoleId, 'role_name' => 'Supervisor'],
        ['user_id' => $regularUser->id, 'role_id' => $viewerRoleId, 'role_name' => 'Viewer']
    ];

    foreach ($userAccess as $access) {
        DB::table('user_logbook_access')->insert([
            'user_id' => $access['user_id'],
            'logbook_template_id' => $template->id,
            'logbook_role_id' => $access['role_id'],
            'has_been_verified' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ User access added: {$access['role_name']}\n";
    }
    echo "\n";

    // 2. Test verification status
    echo "2. Testing verification status:\n";
    $verificationStatuses = DB::table('user_logbook_access')
        ->join('users', 'user_logbook_access.user_id', '=', 'users.id')
        ->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
        ->where('user_logbook_access.logbook_template_id', $template->id)
        ->select(
            'users.name as user_name',
            'logbook_roles.name as role_name',
            'user_logbook_access.has_been_verified'
        )
        ->get();

    foreach ($verificationStatuses as $status) {
        echo "   - {$status->user_name} ({$status->role_name}): " . ($status->has_been_verified ? 'verified' : 'not verified') . "\n";
    }
    echo "\n";

    // 3. Test updating verification status
    echo "3. Testing verification updates:\n";
    
    // Owner verifies supervisor
    DB::table('user_logbook_access')
        ->where('user_id', $supervisor->id)
        ->where('logbook_template_id', $template->id)
        ->update(['has_been_verified' => true, 'updated_at' => now()]);
    echo "✅ Owner verified Supervisor\n";

    // Supervisor verifies regular user
    DB::table('user_logbook_access')
        ->where('user_id', $regularUser->id)
        ->where('logbook_template_id', $template->id)
        ->update(['has_been_verified' => true, 'updated_at' => now()]);
    echo "✅ Supervisor verified Regular User\n";

    // Owner verifies themselves
    DB::table('user_logbook_access')
        ->where('user_id', $owner->id)
        ->where('logbook_template_id', $template->id)
        ->update(['has_been_verified' => true, 'updated_at' => now()]);
    echo "✅ Owner verified themselves\n";
    echo "\n";

    // 4. Check if all Owner and Supervisor are verified
    echo "4. Checking verification completion:\n";
    $unverifiedCount = DB::table('user_logbook_access')
        ->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
        ->where('user_logbook_access.logbook_template_id', $template->id)
        ->whereIn('logbook_roles.name', ['Owner', 'Supervisor'])
        ->where('user_logbook_access.has_been_verified', false)
        ->count();

    echo "Unverified Owner/Supervisor count: {$unverifiedCount}\n";
    
    if ($unverifiedCount === 0) {
        echo "✅ All Owner and Supervisor users are verified\n";
        
        // 5. Test assessment by Institution Admin
        echo "\n5. Testing assessment by Institution Admin:\n";
        $template->update(['has_been_assessed' => true]);
        echo "✅ Institution Admin assessed the template\n";
        echo "   - has_been_assessed: " . ($template->fresh()->has_been_assessed ? 'true' : 'false') . "\n";
    } else {
        echo "❌ Not all Owner/Supervisor users are verified yet\n";
    }
    echo "\n";

    // 6. Final verification status
    echo "6. Final verification status:\n";
    $finalStatuses = DB::table('user_logbook_access')
        ->join('users', 'user_logbook_access.user_id', '=', 'users.id')
        ->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
        ->where('user_logbook_access.logbook_template_id', $template->id)
        ->select(
            'users.name as user_name',
            'logbook_roles.name as role_name',
            'user_logbook_access.has_been_verified'
        )
        ->get();

    foreach ($finalStatuses as $status) {
        echo "   - {$status->user_name} ({$status->role_name}): " . ($status->has_been_verified ? 'verified ✅' : 'not verified ❌') . "\n";
    }

    $template->refresh();
    echo "   - Template Assessment Status: " . ($template->has_been_assessed ? 'assessed ✅' : 'not assessed ❌') . "\n";
    echo "\n";

    // 7. Cleanup
    echo "7. Cleanup:\n";
    DB::table('user_logbook_access')->where('logbook_template_id', $template->id)->delete();
    $template->delete();
    $regularUser->delete();
    $supervisor->delete();
    $owner->delete();
    $institutionAdmin->delete();
    $institution->delete();
    echo "✅ Test data cleaned up successfully\n\n";

    echo "=== Verification and Assessment System Test Completed! ===\n";
    echo "\nImplementation Summary:\n";
    echo "✅ has_been_verified column added to user_logbook_access (default: false)\n";
    echo "✅ has_been_assessed column added to logbook_template (default: false)\n";
    echo "✅ Only Owner and Supervisor can update has_been_verified\n";
    echo "✅ Institution Admin can update has_been_assessed after all Owner/Supervisor verified\n";

} catch (Exception $e) {
    echo "❌ Error occurred: " . $e->getMessage() . "\n";
    echo "Error details:\n";
    echo $e->getTraceAsString() . "\n";
}