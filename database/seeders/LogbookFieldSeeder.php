<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class LogbookFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [];
        
        // Daily Activity Log fields
        $dailyActivityTemplate = DB::table('logbook_template')->where('name', 'Daily Activity Log')->first();
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Activity Description',
            'data_type' => 'teks',
            'template_id' => $dailyActivityTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Hours Spent',
            'data_type' => 'angka',
            'template_id' => $dailyActivityTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Date Performed',
            'data_type' => 'tanggal',
            'template_id' => $dailyActivityTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Equipment Inspection fields
        $inspectionTemplate = DB::table('logbook_template')->where('name', 'Equipment Inspection')->first();
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Equipment Name',
            'data_type' => 'teks',
            'template_id' => $inspectionTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Inspection Date',
            'data_type' => 'tanggal',
            'template_id' => $inspectionTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Condition Rating',
            'data_type' => 'angka',
            'template_id' => $inspectionTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Photo Evidence',
            'data_type' => 'gambar',
            'template_id' => $inspectionTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Incident Report fields
        $incidentTemplate = DB::table('logbook_template')->where('name', 'Incident Report')->first();
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Incident Title',
            'data_type' => 'teks',
            'template_id' => $incidentTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Description',
            'data_type' => 'teks',
            'template_id' => $incidentTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Incident Date',
            'data_type' => 'tanggal',
            'template_id' => $incidentTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Incident Time',
            'data_type' => 'jam',
            'template_id' => $incidentTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $fields[] = [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Severity Level',
            'data_type' => 'angka',
            'template_id' => $incidentTemplate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('logbook_fields')->insert($fields);
    }
}
