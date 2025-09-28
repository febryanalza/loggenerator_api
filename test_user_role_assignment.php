<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== TEST USER REGISTRATION ROLE ASSIGNMENT ===\n\n";

// 1. Cek apakah role User ada
$userRole = Role::where('name', 'User')->where('guard_name', 'web')->first();
if (!$userRole) {
    echo "❌ Role 'User' tidak ditemukan!\n";
    echo "Membuat role 'User'...\n";
    $userRole = Role::create(['name' => 'User', 'guard_name' => 'web']);
    echo "✅ Role 'User' berhasil dibuat\n";
} else {
    echo "✅ Role 'User' sudah ada (ID: {$userRole->id})\n";
}

// 2. Test trigger database
echo "\n=== TEST DATABASE TRIGGER ===\n";

$testEmail = 'test.trigger.' . time() . '@example.com';
echo "Creating test user: {$testEmail}\n";

try {
    // Create user langsung via database untuk test trigger
    $userId = DB::table('users')->insertGetId([
        'id' => DB::raw('uuid_generate_v4()'),
        'name' => 'Test Trigger User',
        'email' => $testEmail,
        'password' => bcrypt('password123'),
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Ambil ID yang actual dari database
    $actualUser = DB::table('users')->where('email', $testEmail)->first();
    echo "✅ User created with ID: {$actualUser->id}\n";
    
    // Cek apakah trigger memberikan role otomatis
    $roleAssignment = DB::table('model_has_roles')
        ->where('model_id', $actualUser->id)
        ->where('model_type', 'App\Models\User')
        ->where('role_id', $userRole->id)
        ->first();
    
    if ($roleAssignment) {
        echo "✅ Database trigger BEKERJA - Role otomatis assigned\n";
    } else {
        echo "❌ Database trigger TIDAK BEKERJA - Role tidak assigned\n";
    }
    
    // Cleanup
    DB::table('model_has_roles')->where('model_id', $actualUser->id)->delete();
    DB::table('users')->where('id', $actualUser->id)->delete();
    echo "✅ Test user cleaned up\n";
    
} catch (\Exception $e) {
    echo "❌ Error testing trigger: " . $e->getMessage() . "\n";
}

// 3. Test melalui Eloquent Model
echo "\n=== TEST ELOQUENT MODEL CREATION ===\n";

$testEmail2 = 'test.eloquent.' . time() . '@example.com';
echo "Creating test user via Eloquent: {$testEmail2}\n";

try {
    $testUser = User::create([
        'name' => 'Test Eloquent User',
        'email' => $testEmail2,
        'password' => bcrypt('password123'),
        'status' => 'active',
    ]);
    
    echo "✅ User created via Eloquent: {$testUser->id}\n";
    
    // Cek role assignment
    if ($testUser->hasRole('User')) {
        echo "✅ Eloquent creation BEKERJA - User has 'User' role\n";
    } else {
        echo "❌ Eloquent creation - Role tidak assigned otomatis\n";
        
        // Manual assign untuk test
        $testUser->assignRole('User');
        echo "✅ Manual role assignment completed\n";
    }
    
    // Cleanup
    $testUser->delete();
    echo "✅ Test user cleaned up\n";
    
} catch (\Exception $e) {
    echo "❌ Error testing Eloquent: " . $e->getMessage() . "\n";
}

echo "\n=== REKOMENDASI ===\n";
echo "1. Jika trigger tidak bekerja, perlu update AuthController\n";
echo "2. Tambahkan fallback mechanism di User model atau Controller\n";
echo "3. Pastikan semua user registration mendapat role 'User' otomatis\n";