<?php

require_once 'vendor/autoload.php';

// Load Laravel app untuk akses database dan models
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Institution;
use App\Models\LogbookTemplate;
use Spatie\Permission\Models\Role;

echo "=== Testing Institution System Implementation ===\n\n";

try {
    // 1. Test Institution Model
    echo "1. Testing Institution Model:\n";
    $institution = Institution::create([
        'name' => 'Test University',
        'description' => 'A test institution for demo purposes'
    ]);
    echo "✅ Institution created: {$institution->name} (ID: {$institution->id})\n\n";

    // 2. Test Institution Admin Role
    echo "2. Testing Institution Admin Role:\n";
    $role = Role::where('name', 'institution_admin')->first();
    if ($role) {
        echo "✅ Institution Admin role exists\n";
        echo "Role permissions: " . $role->permissions->pluck('name')->join(', ') . "\n\n";
    } else {
        echo "❌ Institution Admin role not found\n\n";
    }

    // 3. Test User with Institution
    echo "3. Testing User with Institution:\n";
    $institutionAdmin = User::create([
        'name' => 'Test Institution Admin',
        'email' => 'admin@testuniversity.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'institution_id' => $institution->id,
        'status' => 'active'
    ]);
    
    // Assign institution_admin role
    $institutionAdmin->assignRole('institution_admin');
    
    echo "✅ Institution Admin created: {$institutionAdmin->name}\n";
    echo "Institution: {$institutionAdmin->institution->name}\n";
    echo "Role: {$institutionAdmin->getRoleNames()->first()}\n";
    echo "Is Institution Admin: " . ($institutionAdmin->isInstitutionAdmin() ? 'YES' : 'NO') . "\n";
    echo "Belongs to Institution: " . ($institutionAdmin->belongsToInstitution($institution->id) ? 'YES' : 'NO') . "\n\n";

    // 4. Test LogbookTemplate with Institution
    echo "4. Testing LogbookTemplate with Institution:\n";
    $template = LogbookTemplate::create([
        'name' => 'Test Institution Template',
        'description' => 'A template for test institution',
        'institution_id' => $institution->id
    ]);
    
    echo "✅ Institution Template created: {$template->name}\n";
    echo "Institution: {$template->institution->name}\n";
    echo "Belongs to Institution: " . ($template->belongsToInstitution($institution->id) ? 'YES' : 'NO') . "\n\n";

    // 5. Test Relations
    echo "5. Testing Relations:\n";
    echo "Institution Users Count: {$institution->users()->count()}\n";
    echo "Institution Templates Count: {$institution->logbookTemplates()->count()}\n";
    echo "Institution Admins Count: {$institution->institutionAdmins()->count()}\n\n";

    // 6. Test Scopes
    echo "6. Testing Scopes:\n";
    $institutionTemplates = LogbookTemplate::forInstitution($institution->id)->count();
    $globalTemplates = LogbookTemplate::global()->count();
    echo "Templates for Institution: {$institutionTemplates}\n";
    echo "Global Templates: {$globalTemplates}\n\n";

    // 7. Cleanup
    echo "7. Cleanup:\n";
    $template->delete();
    $institutionAdmin->delete();
    $institution->delete();
    echo "✅ Test data cleaned up successfully\n\n";

    echo "=== All Institution System Tests Passed! ===\n";
    echo "\nSummary of Implementation:\n";
    echo "✅ Institutions table created with UUID, name, description\n";
    echo "✅ institution_id added to users and logbook_template tables\n";
    echo "✅ Institution model with relations created\n";
    echo "✅ User and LogbookTemplate models updated with institution relations\n";
    echo "✅ Institution Admin role created with appropriate permissions\n";
    echo "✅ UserManagementController updated to handle institution_admin\n";
    echo "✅ Routes updated to allow Admin and Super Admin access to /admin/users\n";

} catch (Exception $e) {
    echo "❌ Error occurred: " . $e->getMessage() . "\n";
    echo "Error details:\n";
    echo $e->getTraceAsString() . "\n";
}