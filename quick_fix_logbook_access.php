<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\LogbookTemplate;
use App\Models\UserLogbookAccess;
use Illuminate\Support\Facades\DB;

echo "=== QUICK FIX LOGBOOK ACCESS ISSUE ===\n\n";

try {
    // 1. Cek logbook_roles table
    echo "📋 Checking logbook_roles table:\n";
    $roles = DB::table('logbook_roles')->get();
    foreach ($roles as $role) {
        echo "   ID: {$role->id} - Name: {$role->name}\n";
    }
    echo "\n";

    // 2. Find templates without owner access
    echo "🔍 Looking for templates without owner access:\n";
    $templatesWithoutOwner = DB::select("
        SELECT lt.id, lt.name, lt.created_by, u.name as owner_name, u.email as owner_email
        FROM logbook_template lt
        JOIN users u ON lt.created_by = u.id
        LEFT JOIN user_logbook_access ula ON lt.id = ula.logbook_template_id AND lt.created_by = ula.user_id
        WHERE ula.id IS NULL AND lt.created_by IS NOT NULL
    ");

    if (empty($templatesWithoutOwner)) {
        echo "✅ All templates have owner access!\n";
    } else {
        echo "❌ Found " . count($templatesWithoutOwner) . " templates without owner access:\n";
        
        foreach ($templatesWithoutOwner as $template) {
            echo "   - Template: {$template->name} (ID: {$template->id})\n";
            echo "     Owner: {$template->owner_name} ({$template->owner_email})\n";
        }
        
        echo "\n💡 Auto-fixing these templates...\n";
        
        // Get Owner role ID
        $ownerRoleId = DB::table('logbook_roles')->where('name', 'Owner')->value('id');
        
        if (!$ownerRoleId) {
            echo "❌ Error: 'Owner' role not found in logbook_role table!\n";
            exit(1);
        }
        
        // Fix each template
        foreach ($templatesWithoutOwner as $template) {
            try {
                DB::table('user_logbook_access')->insert([
                    'user_id' => $template->created_by,
                    'logbook_template_id' => $template->id,
                    'logbook_role_id' => $ownerRoleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "   ✅ Fixed access for template: {$template->name}\n";
                
            } catch (\Exception $e) {
                echo "   ❌ Failed to fix template {$template->name}: {$e->getMessage()}\n";
            }
        }
    }

    // 3. Verify the fix
    echo "\n🧪 Verification after fix:\n";
    $stillBroken = DB::select("
        SELECT lt.id, lt.name, lt.created_by
        FROM logbook_template lt
        LEFT JOIN user_logbook_access ula ON lt.id = ula.logbook_template_id AND lt.created_by = ula.user_id
        WHERE ula.id IS NULL AND lt.created_by IS NOT NULL
    ");

    if (empty($stillBroken)) {
        echo "✅ All templates now have proper owner access!\n";
    } else {
        echo "❌ Still " . count($stillBroken) . " templates without access:\n";
        foreach ($stillBroken as $template) {
            echo "   - {$template->name} (ID: {$template->id})\n";
        }
    }

    // 4. Test specific template if provided
    echo "\n📋 Want to test a specific template? Enter template ID (or press Enter to skip): ";
    $handle = fopen("php://stdin", "r");
    $testTemplate = trim(fgets($handle));
    fclose($handle);

    if (!empty($testTemplate)) {
        echo "\n🔍 Testing template: $testTemplate\n";
        
        $template = LogbookTemplate::find($testTemplate);
        if (!$template) {
            echo "❌ Template not found!\n";
        } else {
            echo "✅ Template found: {$template->name}\n";
            
            // Check owner access
            $ownerAccess = UserLogbookAccess::where('user_id', $template->created_by)
                ->where('logbook_template_id', $testTemplate)
                ->with('logbookRole')
                ->first();
            
            if ($ownerAccess) {
                echo "✅ Owner has access with role: {$ownerAccess->logbookRole->name}\n";
                
                // Test middleware logic
                $canEdit = UserLogbookAccess::where('user_id', $template->created_by)
                    ->where('logbook_template_id', $testTemplate)
                    ->whereHas('logbookRole', function ($q) {
                        $q->whereIn('name', ['Editor', 'Supervisor', 'Owner']);
                    })
                    ->exists();
                
                echo "✅ Can create/edit entries: " . ($canEdit ? "YES" : "NO") . "\n";
            } else {
                echo "❌ Owner does NOT have access!\n";
            }
        }
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIX COMPLETED ===\n";
echo "If the issue persists, check:\n";
echo "1. Mobile app is sending correct template_id\n";
echo "2. Request format matches API expectations\n";
echo "3. User authentication is working properly\n";