<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogbookRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create logbook template roles (sub-roles for template management)
        $logbookRoles = [
            [
                'id' => 1,
                'name' => 'Owner',
                'description' => 'Full control over template - can view, edit, delete template and manage all users'
            ],
            [
                'id' => 2, 
                'name' => 'Supervisor',
                'description' => 'Can manage template data and user access but cannot modify template structure'
            ],
            [
                'id' => 3,
                'name' => 'Editor', 
                'description' => 'Can create and edit logbook entries but cannot delete or manage users'
            ],
            [
                'id' => 4,
                'name' => 'Viewer',
                'description' => 'Read-only access to view template and logbook data'
            ]
        ];

        foreach ($logbookRoles as $role) {
            DB::table('logbook_roles')->updateOrInsert(
                ['id' => $role['id']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('Logbook roles seeded successfully.');
    }
}