<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users for each role
        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'role' => 'Super Admin',
                'email_verified_at' => now()
            ],
            [
                'name' => 'System Administrator', 
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'Admin',
                'email_verified_at' => now()
            ],
            [
                'name' => 'Department Manager',
                'email' => 'manager@example.com', 
                'password' => Hash::make('password'),
                'role' => 'Manager',
                'email_verified_at' => now()
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'role' => 'User',
                'email_verified_at' => now()
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'), 
                'role' => 'User',
                'email_verified_at' => now()
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'role' => 'User',
                'email_verified_at' => now()
            ]
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'email_verified_at' => now(),
                    'status' => 'active',
                    'phone_number' => null
                ]
            );

            // Assign role to user
            $role = Role::where('name', $userData['role'])->first();
            if ($role && !$user->hasRole($userData['role'])) {
                $user->assignRole($role);
            }
        }

        $this->command->info('Sample users seeded successfully.');
        $this->command->info('Login credentials:');
        $this->command->info('Super Admin: superadmin@example.com / password');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Manager: manager@example.com / password');
        $this->command->info('User: user@example.com / password');
    }
}