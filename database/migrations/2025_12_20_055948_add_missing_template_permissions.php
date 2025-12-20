<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add missing templates.update.own and templates.delete.own permissions
     */
    public function up(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Add missing permissions
        $permissions = [
            ['name' => 'templates.update.own', 'guard_name' => 'web', 'description' => 'Update own templates'],
            ['name' => 'templates.delete.own', 'guard_name' => 'web', 'description' => 'Delete own templates'],
        ];

        foreach ($permissions as $permission) {
            // Check if permission already exists
            if (!Permission::where('name', $permission['name'])->where('guard_name', $permission['guard_name'])->exists()) {
                Permission::create($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the permissions
        Permission::whereIn('name', ['templates.update.own', 'templates.delete.own'])->delete();
        
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
