<?php

namespace Database\Seeders;

use App\Models\AvailableDataType;
use Illuminate\Database\Seeder;

class AvailableDataTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataTypes = [
            [
                'name' => 'text',
                'description' => 'Teks pendek atau panjang (string)',
                'is_active' => true,
            ],
            [
                'name' => 'number',
                'description' => 'Angka (integer atau decimal)',
                'is_active' => true,
            ],
            [
                'name' => 'date',
                'description' => 'Tanggal (format: YYYY-MM-DD)',
                'is_active' => true,
            ],
            [
                'name' => 'time',
                'description' => 'Waktu (format: HH:MM:SS)',
                'is_active' => true,
            ],
            [
                'name' => 'datetime',
                'description' => 'Tanggal dan waktu (format: YYYY-MM-DD HH:MM:SS)',
                'is_active' => true,
            ],
            [
                'name' => 'image',
                'description' => 'File gambar (JPG, PNG, GIF, dll.)',
                'is_active' => true,
            ],
            [
                'name' => 'file',
                'description' => 'File dokumen (PDF, DOC, XLS, dll.)',
                'is_active' => false,
            ],
            [
                'name' => 'textarea',
                'description' => 'Teks panjang multi-baris',
                'is_active' => true,
            ],
            [
                'name' => 'url',
                'description' => 'URL atau tautan web',
                'is_active' => false,
            ],
            [
                'name' => 'phone',
                'description' => 'Nomor telepon',
                'is_active' => false,
            ],
            [
                'name' => 'currency',
                'description' => 'Nilai mata uang (format: Rp atau $)',
                'is_active' => false,
            ],
            [
                'name' => 'percentage',
                'description' => 'Nilai persentase (0-100%)',
                'is_active' => false,
            ],
            [
                'name' => 'location',
                'description' => 'Koordinat lokasi (latitude, longitude)',
                'is_active' => false,
            ],
        ];

        foreach ($dataTypes as $dataType) {
            AvailableDataType::updateOrCreate(
                ['name' => $dataType['name']],
                $dataType
            );
        }

        $this->command->info('Available data types seeded successfully!');
    }
}
