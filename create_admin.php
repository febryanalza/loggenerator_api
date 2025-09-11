<?php

require __DIR__ . '/vendor/autoload.php';

// Load .env file
$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Create admin user for testing
$admin = User::create([
    'name' => 'Admin Test',
    'email' => 'admin@example.com',
    'password' => Hash::make('password')
]);

echo "Admin user created:\n";
echo "Email: admin@example.com\n";
echo "Password: password\n";
echo "ID: " . $admin->id . "\n";
