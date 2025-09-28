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

echo "=== DIAGNOSIS LOGBOOK ACCESS PERMISSION ISSUE ===\n\n";

// Input template ID untuk diagnosis
echo "Masukkan Template ID yang bermasalah: ";
$handle = fopen("php://stdin", "r");
$templateId = trim(fgets($handle));
fclose($handle);

if (empty($templateId)) {
    echo "❌ Template ID tidak boleh kosong\n";
    exit(1);
}

try {
    // 1. Cek apakah template ada
    $template = LogbookTemplate::find($templateId);
    if (!$template) {
        echo "❌ Template dengan ID '$templateId' tidak ditemukan\n";
        exit(1);
    }

    echo "✅ Template ditemukan: {$template->name}\n";
    echo "   Created by: {$template->user_id}\n\n";

    // 2. Cek siapa owner template ini
    $owner = User::find($template->user_id);
    if ($owner) {
        echo "👤 Template Owner:\n";
        echo "   Name: {$owner->name}\n";
        echo "   Email: {$owner->email}\n";
        echo "   User ID: {$owner->id}\n\n";
    }

    // 3. Cek semua user yang memiliki akses ke template ini
    echo "📋 Semua User Access untuk template ini:\n";
    $allAccess = UserLogbookAccess::where('logbook_template_id', $templateId)
        ->with(['user', 'logbookRole'])
        ->get();

    if ($allAccess->isEmpty()) {
        echo "❌ TIDAK ADA USER yang memiliki akses ke template ini!\n";
        echo "   Ini adalah MASALAH UTAMA - Owner template tidak memiliki akses!\n\n";
    } else {
        foreach ($allAccess as $access) {
            $user = $access->user;
            $role = $access->logbookRole;
            echo "   - {$user->name} ({$user->email}) = Role: {$role->name}\n";
            
            if ($user->id === $template->user_id) {
                echo "     ^ INI ADALAH OWNER TEMPLATE ✅\n";
            }
        }
        echo "\n";
    }

    // 4. Cek apakah Owner template memiliki akses Owner
    $ownerAccess = UserLogbookAccess::where('user_id', $template->user_id)
        ->where('logbook_template_id', $templateId)
        ->with('logbookRole')
        ->first();

    echo "🔍 Owner Template Access Check:\n";
    if (!$ownerAccess) {
        echo "❌ MASALAH DITEMUKAN: Owner template TIDAK memiliki entry di user_logbook_access!\n";
        echo "   Owner ID: {$template->user_id}\n";
        echo "   Template ID: {$templateId}\n\n";
        
        echo "🛠️  PENYEBAB MASALAH:\n";
        echo "   - Template dibuat tanpa auto-assign Owner role\n";
        echo "   - LogbookTemplate model event 'created' tidak berjalan\n";
        echo "   - Ada error saat membuat template\n\n";
        
        echo "💡 SOLUSI OTOMATIS:\n";
        echo "   Apakah Anda ingin saya otomatis memperbaiki akses Owner? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $fix = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($fix) === 'y') {
            // Auto-fix: berikan akses Owner ke owner template
            $ownerRoleId = DB::table('logbook_role')->where('name', 'Owner')->value('id');
            
            if ($ownerRoleId) {
                UserLogbookAccess::create([
                    'user_id' => $template->user_id,
                    'logbook_template_id' => $templateId,
                    'logbook_role_id' => $ownerRoleId
                ]);
                
                echo "✅ BERHASIL! Owner access telah diperbaiki\n";
                echo "   User: {$owner->name}\n";
                echo "   Template: {$template->name}\n";
                echo "   Role: Owner\n\n";
            } else {
                echo "❌ Role 'Owner' tidak ditemukan di tabel logbook_role\n";
            }
        }
        
    } else {
        echo "✅ Owner memiliki akses: {$ownerAccess->logbookRole->name}\n";
        
        // Cek apakah role-nya sudah benar
        if ($ownerAccess->logbookRole->name !== 'Owner') {
            echo "⚠️  WARNING: Owner template memiliki role '{$ownerAccess->logbookRole->name}' bukan 'Owner'\n";
        }
    }

    // 5. Test middleware logic
    echo "\n🧪 Test Middleware Logic:\n";
    
    // Simulate middleware check
    $middlewareResult = UserLogbookAccess::where('user_id', $template->user_id)
        ->where('logbook_template_id', $templateId)
        ->whereHas('logbookRole', function ($q) {
            $q->whereIn('name', ['Editor', 'Supervisor', 'Owner']);
        })
        ->exists();
    
    echo "   Middleware check untuk role 'Editor,Supervisor,Owner': ";
    echo $middlewareResult ? "✅ PASS" : "❌ FAIL";
    echo "\n";

    // 6. Cek apakah Owner adalah Super Admin atau Admin
    $isSystemAdmin = DB::table('model_has_roles')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where('model_has_roles.model_id', $template->user_id)
        ->where('model_has_roles.model_type', User::class)
        ->whereIn('roles.name', ['Super Admin', 'Admin'])
        ->exists();
    
    echo "   Owner adalah System Admin/Super Admin: ";
    echo $isSystemAdmin ? "✅ YES (Should bypass logbook access)" : "❌ NO";
    echo "\n\n";

    // 7. Recommendations
    echo "📝 RECOMMENDATIONS:\n";
    if (!$ownerAccess) {
        echo "   1. ✅ Perbaiki akses Owner template (sudah ditawarkan di atas)\n";
        echo "   2. ✅ Periksa LogbookTemplate model event 'created'\n";
        echo "   3. ✅ Pastikan semua template baru auto-assign Owner role\n";
    } else {
        echo "   1. ✅ Akses Owner sudah benar\n";
        echo "   2. ✅ Periksa request format dari mobile app\n";
        echo "   3. ✅ Periksa apakah template_id dikirim dengan benar\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DIAGNOSIS SELESAI ===\n";