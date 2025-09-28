<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== AVAILABLE TEMPLATES ===\n\n";

$templates = DB::table('logbook_template')->get();

if ($templates->count() > 0) {
    foreach ($templates as $template) {
        echo "Template: {$template->name}\n";
        echo "  - ID: {$template->id}\n";
        echo "  - Description: {$template->description}\n";
        
        // Cek siapa yang punya akses
        $owners = DB::table('user_logbook_access as ula')
            ->join('users as u', 'ula.user_id', '=', 'u.id')
            ->join('logbook_roles as lr', 'ula.logbook_role_id', '=', 'lr.id')
            ->where('ula.logbook_template_id', $template->id)
            ->where('ula.logbook_role_id', 1) // Owner role
            ->select('u.email', 'lr.name as role_name')
            ->get();
            
        if ($owners->count() > 0) {
            echo "  - Owners:\n";
            foreach ($owners as $owner) {
                echo "    * {$owner->email}\n";
            }
        } else {
            echo "  - No owners assigned\n";
        }
        echo "\n";
    }
} else {
    echo "❌ No templates found in database!\n";
    echo "Run seeders to create sample templates.\n";
}