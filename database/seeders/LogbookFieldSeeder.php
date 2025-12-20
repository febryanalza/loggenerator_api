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
        $templates = DB::table('logbook_template')
            ->whereIn('name', ['Daily Activity Log', 'Equipment Inspection', 'Incident Report'])
            ->pluck('id', 'name');

        if ($templates->isEmpty()) {
            $this->command?->warn('LogbookFieldSeeder skipped: required templates not found.');
            return;
        }

        $typeMap = [
            'teks' => 'text',
            'angka' => 'number',
            'tanggal' => 'date',
            'jam' => 'time',
            'gambar' => 'image',
        ];

        $fields = [
            // Daily Activity Log
            ['name' => 'Activity Description', 'data_type' => 'teks', 'template' => 'Daily Activity Log'],
            ['name' => 'Hours Spent', 'data_type' => 'angka', 'template' => 'Daily Activity Log'],
            ['name' => 'Date Performed', 'data_type' => 'tanggal', 'template' => 'Daily Activity Log'],

            // Equipment Inspection
            ['name' => 'Equipment Name', 'data_type' => 'teks', 'template' => 'Equipment Inspection'],
            ['name' => 'Inspection Date', 'data_type' => 'tanggal', 'template' => 'Equipment Inspection'],
            ['name' => 'Condition Rating', 'data_type' => 'angka', 'template' => 'Equipment Inspection'],
            ['name' => 'Photo Evidence', 'data_type' => 'gambar', 'template' => 'Equipment Inspection'],

            // Incident Report
            ['name' => 'Incident Title', 'data_type' => 'teks', 'template' => 'Incident Report'],
            ['name' => 'Description', 'data_type' => 'teks', 'template' => 'Incident Report'],
            ['name' => 'Incident Date', 'data_type' => 'tanggal', 'template' => 'Incident Report'],
            ['name' => 'Incident Time', 'data_type' => 'jam', 'template' => 'Incident Report'],
            ['name' => 'Severity Level', 'data_type' => 'angka', 'template' => 'Incident Report'],
        ];

        $now = now();
        $rows = [];

        foreach ($fields as $field) {
            $templateId = $templates->get($field['template']);
            if (!$templateId) {
                $this->command?->warn("Skipping field '{$field['name']}' because template '{$field['template']}' not found.");
                continue;
            }

            $normalizedType = $typeMap[$field['data_type']] ?? $field['data_type'];

            $rows[] = [
                'id' => Uuid::uuid4()->toString(),
                'name' => $field['name'],
                'data_type' => $normalizedType,
                'template_id' => $templateId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('logbook_fields')->insert($rows);
            $this->command?->info('Logbook fields seeded with normalized data types.');
        } else {
            $this->command?->warn('LogbookFieldSeeder did not insert any rows.');
        }
    }
}
