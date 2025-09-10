<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class LogbookTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = DB::table('users')->where('email', 'admin@example.com')->first();
        $manager = DB::table('users')->where('email', 'manager@example.com')->first();
        
        $templates = [
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Daily Activity Log',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Equipment Inspection',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Incident Report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('logbook_template')->insert($templates);
    }
}