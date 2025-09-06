<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [];
        
        $admin = DB::table('users')->where('email', 'admin@example.com')->first();
        $manager = DB::table('users')->where('email', 'manager@example.com')->first();
        $user = DB::table('users')->where('email', 'user@example.com')->first();
        
        // Admin notifications
        $notifications[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $admin->id,
            'title' => 'Welcome to LogGenerator',
            'message' => 'Welcome to the LogGenerator platform. As an admin, you have full access to all features.',
            'is_read' => 0,
            'created_at' => now(),
        ];
        
        $notifications[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $admin->id,
            'title' => 'New User Registration',
            'message' => 'A new user has registered and requires approval.',
            'is_read' => 1,
            'created_at' => now()->subHours(2),
        ];
        
        // Manager notifications
        $notifications[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $manager->id,
            'title' => 'Welcome to LogGenerator',
            'message' => 'Welcome to the LogGenerator platform. As a manager, you can create and manage templates.',
            'is_read' => 1,
            'created_at' => now()->subDay(),
        ];
        
        // Regular user notifications
        $notifications[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $user->id,
            'title' => 'Welcome to LogGenerator',
            'message' => 'Welcome to the LogGenerator platform. You can now start creating logs using available templates.',
            'is_read' => 0,
            'created_at' => now()->subDays(2),
        ];
        
        $notifications[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $user->id,
            'title' => 'New Template Available',
            'message' => 'A new template "Incident Report" is now available for your use.',
            'is_read' => 0,
            'created_at' => now()->subHours(4),
        ];
        
        DB::table('notifications')->insert($notifications);
    }
}