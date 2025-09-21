<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogbookRole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logbook_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the permissions for this logbook role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            LogbookPermission::class,
            'logbook_role_permissions',
            'logbook_role_id',
            'logbook_permission_id'
        )->withTimestamps();
    }

    /**
     * Get the user access records for this role.
     */
    public function userAccess(): HasMany
    {
        return $this->hasMany(UserLogbookAccess::class, 'logbook_role_id');
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Give permission to this role.
     */
    public function givePermissionTo(string|LogbookPermission $permission): self
    {
        if (is_string($permission)) {
            $permission = LogbookPermission::where('name', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([$permission->id]);

        return $this;
    }

    /**
     * Revoke permission from this role.
     */
    public function revokePermissionTo(string|LogbookPermission $permission): self
    {
        if (is_string($permission)) {
            $permission = LogbookPermission::where('name', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission->id);

        return $this;
    }
}