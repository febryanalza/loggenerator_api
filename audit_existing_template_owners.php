<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LogbookTemplate;
use App\Models\UserLogbookAccess;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== AUDIT EXISTING TEMPLATES OWNER ASSIGNMENT ===\n\n";

// 1. Lihat semua template yang ada
$templates = LogbookTemplate::with(['userAccess.user', 'userAccess.logbookRole'])->get();

if ($templates->count() === 0) {
    echo "📝 No templates found in database.\n";
    exit(0);
}

echo "📊 Found " . $templates->count() . " templates:\n\n";

$ownerRoleId = DB::table('logbook_roles')->where('name', 'Owner')->value('id');
$templatesWithoutOwner = [];
$templatesWithOwner = [];

foreach ($templates as $template) {
    echo "📋 Template: {$template->name}\n";
    echo "   ID: {$template->id}\n";
    echo "   Created By: " . ($template->created_by ?? 'NOT SET') . "\n";
    echo "   Created At: {$template->created_at}\n";
    
    // Cari user access records untuk template ini
    $userAccess = $template->userAccess;
    
    if ($userAccess->count() > 0) {
        echo "   👥 User Access:\n";
        $hasOwner = false;
        
        foreach ($userAccess as $access) {
            $userName = $access->user ? $access->user->name : 'Unknown User';
            $roleName = $access->logbookRole ? $access->logbookRole->name : 'Unknown Role';
            
            echo "      - {$userName} ({$access->user_id}) = {$roleName}\n";
            
            if ($access->logbook_role_id == $ownerRoleId) {
                $hasOwner = true;
                $ownerUserId = $access->user_id;
            }
        }
        
        if ($hasOwner) {
            echo "   ✅ Has Owner: YES\n";
            $templatesWithOwner[] = [
                'template' => $template,
                'owner_id' => $ownerUserId
            ];
            
            // Cek apakah created_by sesuai dengan owner
            if ($template->created_by && $template->created_by === $ownerUserId) {
                echo "   ✅ Owner matches created_by: YES\n";
            } elseif ($template->created_by) {
                echo "   ⚠️  Owner matches created_by: NO (created_by: {$template->created_by}, owner: {$ownerUserId})\n";
            } else {
                echo "   ⚠️  created_by not set, but has owner\n";
            }
        } else {
            echo "   ❌ Has Owner: NO\n";
            $templatesWithoutOwner[] = $template;
        }
    } else {
        echo "   ❌ No user access records found!\n";
        $templatesWithoutOwner[] = $template;
    }
    
    echo "\n";
}

// 2. Summary
echo "=== SUMMARY ===\n";
echo "📊 Total Templates: " . $templates->count() . "\n";
echo "✅ Templates with Owner: " . count($templatesWithOwner) . "\n";
echo "❌ Templates without Owner: " . count($templatesWithoutOwner) . "\n\n";

if (count($templatesWithoutOwner) > 0) {
    echo "⚠️  Templates missing Owner:\n";
    foreach ($templatesWithoutOwner as $template) {
        echo "   - {$template->name} (ID: {$template->id})\n";
    }
    echo "\n";
}

// 3. Cek konsistensi created_by vs owner
echo "🔍 Checking created_by consistency:\n";
$consistentTemplates = 0;
$inconsistentTemplates = 0;
$missingCreatedBy = 0;

foreach ($templatesWithOwner as $item) {
    $template = $item['template'];
    $ownerId = $item['owner_id'];
    
    if (!$template->created_by) {
        $missingCreatedBy++;
    } elseif ($template->created_by === $ownerId) {
        $consistentTemplates++;
    } else {
        $inconsistentTemplates++;
        echo "   ⚠️  Inconsistent: {$template->name} - created_by: {$template->created_by}, owner: {$ownerId}\n";
    }
}

echo "   ✅ Consistent (created_by = owner): {$consistentTemplates}\n";
echo "   ❌ Inconsistent (created_by ≠ owner): {$inconsistentTemplates}\n";
echo "   ⚠️  Missing created_by: {$missingCreatedBy}\n\n";

// 4. Test middleware untuk beberapa template
if (count($templatesWithOwner) > 0) {
    echo "🧪 Testing middleware access for sample templates:\n";
    
    $sampleCount = min(3, count($templatesWithOwner));
    for ($i = 0; $i < $sampleCount; $i++) {
        $item = $templatesWithOwner[$i];
        $template = $item['template'];
        $ownerId = $item['owner_id'];
        
        // Simulate middleware check
        $canEditAsOwner = UserLogbookAccess::where('user_id', $ownerId)
            ->where('logbook_template_id', $template->id)
            ->whereHas('logbookRole', function($q) {
                $q->whereIn('name', ['Editor', 'Supervisor', 'Owner']);
            })
            ->exists();
        
        echo "   📋 {$template->name}: Owner can edit = " . ($canEditAsOwner ? "✅ YES" : "❌ NO") . "\n";
    }
}

echo "\n=== CONCLUSION ===\n";
if (count($templatesWithoutOwner) === 0) {
    echo "✅ All templates have proper Owner assignment!\n";
    echo "   The automatic owner assignment is working correctly.\n";
} else {
    echo "❌ Some templates are missing Owner assignment!\n";
    echo "   This might indicate:\n";
    echo "   - Templates created before the auto-assignment was implemented\n";
    echo "   - Issues with the model booted() method\n";
    echo "   - Manual template creation bypassing the model events\n";
    echo "\n   Recommendation: Run the quick_fix_logbook_access.php script\n";
}

if ($inconsistentTemplates > 0 || $missingCreatedBy > 0) {
    echo "\n⚠️  Some templates have inconsistent ownership data:\n";
    echo "   - This doesn't affect functionality immediately\n";
    echo "   - But it's good to fix for audit trail and consistency\n";
}