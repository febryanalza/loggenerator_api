<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class LogbookDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];
        
        // Daily Activity Log data
        $dailyActivityTemplate = DB::table('logbook_template')->where('name', 'Daily Activity Log')->first();
        
        $user = DB::table('users')->where('email', 'admin@example.com')->first();
        
        $data[] = [
            'id' => Uuid::uuid4()->toString(),
            'template_id' => $dailyActivityTemplate->id,
            'writer_id' => $user->id,
            'data' => json_encode([
                'Activity Description' => 'Completed project documentation',
                'Hours Spent' => 4,
                'Date Performed' => '2025-09-01'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $data[] = [
            'id' => Uuid::uuid4()->toString(),
            'template_id' => $dailyActivityTemplate->id,
            'writer_id' => $user->id,
            'data' => json_encode([
                'Activity Description' => 'Team meeting and sprint planning',
                'Hours Spent' => 2,
                'Date Performed' => '2025-09-02'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Equipment Inspection data
        $inspectionTemplate = DB::table('logbook_template')->where('name', 'Equipment Inspection')->first();
        
        $manager = DB::table('users')->where('email', 'manager@example.com')->first();
        
        $data[] = [
            'id' => Uuid::uuid4()->toString(),
            'template_id' => $inspectionTemplate->id,
            'writer_id' => $manager->id,
            'data' => json_encode([
                'Equipment Name' => 'Server Rack #3',
                'Inspection Date' => '2025-08-28',
                'Condition Rating' => 8,
                'Photo Evidence' => 'server_rack3_20250828.jpg'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Incident Report data
        $incidentTemplate = DB::table('logbook_template')->where('name', 'Incident Report')->first();
        
        $regularUser = DB::table('users')->where('email', 'user@example.com')->first();
        
        $data[] = [
            'id' => Uuid::uuid4()->toString(),
            'template_id' => $incidentTemplate->id,
            'writer_id' => $regularUser->id,
            'data' => json_encode([
                'Incident Title' => 'Network Outage',
                'Description' => 'Temporary network outage affecting the east wing offices',
                'Incident Date' => '2025-09-05',
                'Incident Time' => '14:30',
                'Severity Level' => 3
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('logbook_datas')->insert($data);
    }
}