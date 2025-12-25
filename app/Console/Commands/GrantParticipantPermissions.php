<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GrantParticipantPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:grant-participant-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant participant permissions to Institution Admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Granting participant permissions to Institution Admin role...');

        // Ensure permissions exist
        $permissions = [
            'participants.view',
            'participants.create',
            'participants.update',
            'participants.delete',
            'participants.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ], [
                'description' => 'Participant management permission'
            ]);
        }

        // Grant to Institution Admin
        $institutionAdmin = Role::where('name', 'Institution Admin')->first();
        if ($institutionAdmin) {
            $institutionAdmin->givePermissionTo($permissions);
            $this->info('✓ Granted participant permissions to Institution Admin');
        } else {
            $this->error('✗ Institution Admin role not found');
        }

        $this->info('Done!');
        return Command::SUCCESS;
    }
}
