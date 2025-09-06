<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userRoles = [];
        
        // Admin
        $admin = DB::table('users')->where('email', 'admin@example.com')->first();
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        $userRoles[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $admin->id,
            'role_id' => $adminRole->id,
            'created_at' => now(),
        ];
        
        // Manager
        $manager = DB::table('users')->where('email', 'manager@example.com')->first();
        $managerRole = DB::table('roles')->where('name', 'Manager')->first();
        $userRoles[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $manager->id,
            'role_id' => $managerRole->id,
            'created_at' => now(),
        ];
        
        // Regular user
        $user = DB::table('users')->where('email', 'user@example.com')->first();
        $userRole = DB::table('roles')->where('name', 'User')->first();
        $userRoles[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $user->id,
            'role_id' => $userRole->id,
            'created_at' => now(),
        ];
        
        // Test user
        $testUser = DB::table('users')->where('email', 'test@example.com')->first();
        $userRoles[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $testUser->id,
            'role_id' => $userRole->id,
            'created_at' => now(),
        ];
        
        DB::table('user_roles')->insert($userRoles);
    }
}