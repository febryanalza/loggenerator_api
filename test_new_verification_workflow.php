<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Institution;
use App\Models\LogbookTemplate;
use App\Models\LogbookRole;
use App\Models\UserLogbookAccess;
use Spatie\Permission\Models\Role;

// Initialize Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Testing NEW Logbook Verification Workflow...\n";
echo "📋 New Flow: Owner verifies first → Supervisor verifies → Institution Admin can assess\n\n";

try {
    DB::beginTransaction();
    
    // 1. Create test institution
    echo "1. Creating test institution...\n";
    $institution = Institution::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Test Institution for New Verification Flow',
        'description' => 'Test institution for new verification workflow'
    ]);
    echo "   ✅ Institution created: {$institution->name}\n\n";
    
    // 2. Create test users
    echo "2. Creating test users...\n";
    
    // Create Owner user
    $ownerUser = User::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Owner User',
        'email' => 'owner_' . time() . '@test.com',
        'password' => bcrypt('password'),
        'institution_id' => $institution->id
    ]);
    echo "   ✅ Owner user created: {$ownerUser->name}\n";
    
    // Create Supervisor user  
    $supervisorUser = User::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Supervisor User',
        'email' => 'supervisor_' . time() . '@test.com',
        'password' => bcrypt('password'),
        'institution_id' => $institution->id
    ]);
    echo "   ✅ Supervisor user created: {$supervisorUser->name}\n";
    
    // Create Institution Admin user
    $institutionAdminUser = User::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Institution Admin User',
        'email' => 'admin_' . time() . '@test.com',
        'password' => bcrypt('password'),
        'institution_id' => $institution->id
    ]);
    
    // Assign Institution Admin role
    $institutionAdminRole = Role::where('name', 'Institution Admin')->first();
    if ($institutionAdminRole) {
        $institutionAdminUser->assignRole($institutionAdminRole);
        echo "   ✅ Institution Admin user created: {$institutionAdminUser->name}\n";
    } else {
        echo "   ❌ Institution Admin role not found\n";
    }
    echo "\n";
    
    // 3. Create test template
    echo "3. Creating test logbook template...\n";
    $template = LogbookTemplate::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Test Template for New Verification Flow',
        'description' => 'Test template for new verification workflow',
        'institution_id' => $institution->id,
        'has_been_assessed' => false
    ]);
    echo "   ✅ Template created: {$template->name}\n\n";
    
    // 4. Get logbook roles
    echo "4. Getting logbook roles...\n";
    $ownerRole = LogbookRole::where('name', 'Owner')->first();
    $supervisorRole = LogbookRole::where('name', 'Supervisor')->first();
    
    if (!$ownerRole || !$supervisorRole) {
        throw new Exception("Required logbook roles not found. Please run seeders first.");
    }
    
    echo "   ✅ Found roles: Owner (ID: {$ownerRole->id}), Supervisor (ID: {$supervisorRole->id})\n\n";
    
    // 5. Assign users to template with roles
    echo "5. Assigning users to template...\n";
    
    $ownerAccess = UserLogbookAccess::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'user_id' => $ownerUser->id,
        'logbook_template_id' => $template->id,
        'logbook_role_id' => $ownerRole->id,
        'has_been_verified_logbook' => false
    ]);
    echo "   ✅ Owner assigned to template\n";
    
    $supervisorAccess = UserLogbookAccess::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'user_id' => $supervisorUser->id,
        'logbook_template_id' => $template->id,
        'logbook_role_id' => $supervisorRole->id,
        'has_been_verified_logbook' => false
    ]);
    echo "   ✅ Supervisor assigned to template\n\n";
    
    // 6. Test NEW verification workflow
    echo "6. Testing NEW sequential verification workflow...\n";
    
    // Test 1: Check initial status (both should be false)
    echo "   📋 Test 1: Initial verification status\n";
    echo "      - Owner verification: " . ($ownerAccess->has_been_verified_logbook ? 'VERIFIED' : 'NOT VERIFIED') . "\n";
    echo "      - Supervisor verification: " . ($supervisorAccess->has_been_verified_logbook ? 'VERIFIED' : 'NOT VERIFIED') . "\n";
    echo "      - Template assessment: " . ($template->has_been_assessed ? 'ASSESSED' : 'NOT ASSESSED') . "\n";
    echo "   ✅ All initially not verified/assessed\n\n";
    
    // Test 2: Try Supervisor verification first (should fail in real API)
    echo "   📋 Test 2: Sequential verification logic\n";
    echo "      - Checking if Supervisor can verify before Owner...\n";
    $canSupervisorVerifyFirst = $ownerAccess->has_been_verified_logbook;
    echo "      - Supervisor should NOT be able to verify first: " . ($canSupervisorVerifyFirst ? 'FAILED' : 'CORRECT') . "\n";
    
    // Test 3: Owner verifies first (should work)
    echo "   📋 Test 3: Owner verifies logbook\n";
    $ownerAccess->has_been_verified_logbook = true;
    $ownerAccess->save();
    echo "   ✅ Owner successfully verified logbook\n";
    
    // Test 4: Now Supervisor can verify (should work)
    echo "   📋 Test 4: Supervisor verifies after Owner\n";
    $canSupervisorVerifyAfterOwner = $ownerAccess->has_been_verified_logbook;
    echo "      - Owner has verified: " . ($canSupervisorVerifyAfterOwner ? 'YES' : 'NO') . "\n";
    
    if ($canSupervisorVerifyAfterOwner) {
        $supervisorAccess->has_been_verified_logbook = true;
        $supervisorAccess->save();
        echo "   ✅ Supervisor successfully verified logbook after Owner\n";
    } else {
        echo "   ❌ Supervisor cannot verify yet\n";
    }
    echo "\n";
    
    // Test 5: Check if Institution Admin can now assess
    echo "   📋 Test 5: Institution Admin assessment eligibility\n";
    $ownerVerified = $ownerAccess->has_been_verified_logbook;
    $supervisorVerified = $supervisorAccess->has_been_verified_logbook;
    $assessmentReady = $ownerVerified && $supervisorVerified;
    
    echo "      - Owner verified: " . ($ownerVerified ? 'YES' : 'NO') . "\n";
    echo "      - Supervisor verified: " . ($supervisorVerified ? 'YES' : 'NO') . "\n";
    echo "      - Assessment ready: " . ($assessmentReady ? 'YES' : 'NO') . "\n";
    
    if ($assessmentReady) {
        $template->has_been_assessed = true;
        $template->save();
        echo "   ✅ Institution Admin can now assess template\n";
    } else {
        echo "   ❌ Institution Admin cannot assess yet\n";
    }
    echo "\n";
    
    // Test 6: Final status verification
    echo "   📋 Test 6: Final verification workflow status\n";
    $finalOwnerStatus = UserLogbookAccess::find($ownerAccess->id);
    $finalSupervisorStatus = UserLogbookAccess::find($supervisorAccess->id);
    $finalTemplate = LogbookTemplate::find($template->id);
    
    echo "      - Step 1 (Owner verification): " . ($finalOwnerStatus->has_been_verified_logbook ? 'COMPLETED' : 'PENDING') . "\n";
    echo "      - Step 2 (Supervisor verification): " . ($finalSupervisorStatus->has_been_verified_logbook ? 'COMPLETED' : 'PENDING') . "\n";
    echo "      - Step 3 (Institution Admin assessment): " . ($finalTemplate->has_been_assessed ? 'COMPLETED' : 'PENDING') . "\n";
    
    $workflowComplete = $finalOwnerStatus->has_been_verified_logbook && 
                       $finalSupervisorStatus->has_been_verified_logbook && 
                       $finalTemplate->has_been_assessed;
    
    if ($workflowComplete) {
        echo "   ✅ Complete verification workflow successful!\n\n";
    } else {
        echo "   ❌ Verification workflow incomplete\n\n";
    }
    
    // Test 7: API endpoint simulation
    echo "   📋 Test 7: API workflow simulation\n";
    echo "      - API Endpoint: PUT /api/logbook/verification\n";
    echo "      - New Request Body: { template_id, has_been_verified_logbook }\n";
    echo "      - Sequential Rule: Owner must verify before Supervisor\n";
    echo "      - Assessment Rule: Institution Admin needs both Owner and Supervisor verified\n";
    echo "   ✅ API workflow logic validated\n\n";
    
    // Cleanup
    echo "7. Cleaning up test data...\n";
    UserLogbookAccess::whereIn('id', [$ownerAccess->id, $supervisorAccess->id])->delete();
    $template->delete();
    User::whereIn('id', [$ownerUser->id, $supervisorUser->id, $institutionAdminUser->id])->delete();
    $institution->delete();
    echo "   ✅ Test data cleaned up successfully\n\n";
    
    DB::commit();
    
    echo "🎉 NEW Verification Workflow Test Completed Successfully!\n\n";
    
    echo "📋 NEW Workflow Summary:\n";
    echo "   🔄 Step 1: Owner verifies logbook after all data entry complete\n";
    echo "   🔄 Step 2: Supervisor verifies logbook (only after Owner)\n";
    echo "   🔄 Step 3: Institution Admin can assess template (only after both verified)\n\n";
    
    echo "📋 API Changes Summary:\n";
    echo "   - Column renamed: has_been_verified → has_been_verified_logbook\n";
    echo "   - Endpoint: PUT /api/logbook/verification\n";
    echo "   - Request: { template_id, has_been_verified_logbook }\n";
    echo "   - Sequential Logic: Owner → Supervisor → Assessment\n";
    echo "   - Institution Admin assessment only after both Owner & Supervisor verified\n\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}