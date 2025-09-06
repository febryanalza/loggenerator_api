<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $auditLogs = [];
        
        $admin = DB::table('users')->where('email', 'admin@example.com')->first();
        $manager = DB::table('users')->where('email', 'manager@example.com')->first();
        $user = DB::table('users')->where('email', 'user@example.com')->first();
        
        // Admin audit logs
        $auditLogs[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $admin->id,
            'action' => 'LOGIN',
            'description' => 'User logged in successfully',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
            'created_at' => now()->subHours(2),
        ];
        
        $auditLogs[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $admin->id,
            'action' => 'CREATE_TEMPLATE',
            'description' => 'Created new template: Daily Activity Log',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
            'created_at' => now()->subHours(1),
        ];
        
        // Manager audit logs
        $auditLogs[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $manager->id,
            'action' => 'LOGIN',
            'description' => 'User logged in successfully',
            'ip_address' => '192.168.1.5',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.2 Safari/605.1.15',
            'created_at' => now()->subDay(),
        ];
        
        $auditLogs[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $manager->id,
            'action' => 'CREATE_TEMPLATE',
            'description' => 'Created new template: Incident Report',
            'ip_address' => '192.168.1.5',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.2 Safari/605.1.15',
            'created_at' => now()->subDays(1)->addHours(2),
        ];
        
        // User audit logs
        $auditLogs[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $user->id,
            'action' => 'LOGIN',
            'description' => 'User logged in successfully',
            'ip_address' => '192.168.1.10',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.2 Mobile/15E148 Safari/604.1',
            'created_at' => now()->subHours(5),
        ];
        
        $auditLogs[] = [
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $user->id,
            'action' => 'CREATE_LOGBOOK_ENTRY',
            'description' => 'Created new logbook entry for Daily Activity Log',
            'ip_address' => '192.168.1.10',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.2 Mobile/15E148 Safari/604.1',
            'created_at' => now()->subHours(4),
        ];
        
        DB::table('audit_logs')->insert($auditLogs);
    }
}