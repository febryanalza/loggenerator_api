<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuids, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'phone_number',
        'profile_picture',
        'last_login'
    ];
    
    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    
    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login' => 'datetime',
    ];
    
    /**
     * The "booted" method of the model.
     * Ensures every new user gets the default 'User' role
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            // Delay role assignment to ensure all database operations are complete
            // This is a fallback if the database trigger fails
            if (!$user->roles()->exists()) {
                try {
                    if (\Spatie\Permission\Models\Role::where('name', 'User')->where('guard_name', 'web')->exists()) {
                        $user->assignRole('User');
                    }
                } catch (\Exception $e) {
                    // Log error but don't break user creation
                    Log::warning('Failed to assign default role to user: ' . $e->getMessage(), [
                        'user_id' => $user->id,
                        'user_email' => $user->email
                    ]);
                }
            }
        });
    }
    
    /**
     * Get the templates created by this user.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(LogbookTemplate::class);
    }
    
    /**
     * Get the audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
    
    /**
     * Get the notifications for this user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the logbook access records for this user.
     */
    public function logbookAccess(): HasMany
    {
        return $this->hasMany(UserLogbookAccess::class);
    }

    /**
     * Check if the user has access to a specific logbook template.
     */
    public function hasLogbookAccess(string $templateId): bool
    {
        return $this->logbookAccess()
            ->where('logbook_template_id', $templateId)
            ->exists();
    }

    /**
     * Get the user's role for a specific logbook template.
     */
    public function getLogbookRole(string $templateId): ?LogbookRole
    {
        $access = $this->logbookAccess()
            ->where('logbook_template_id', $templateId)
            ->with('logbookRole')
            ->first();

        return $access?->logbookRole;
    }

    /**
     * Check if the user has a specific logbook permission for a template.
     */
    public function hasLogbookPermission(string $templateId, string $permissionName): bool
    {
        $access = $this->logbookAccess()
            ->where('logbook_template_id', $templateId)
            ->with('logbookRole.permissions')
            ->first();

        return $access?->hasLogbookPermission($permissionName) ?? false;
    }

    /**
     * Assign a logbook role to the user for a specific template.
     */
    public function assignLogbookRole(string $templateId, string|int $roleId): UserLogbookAccess
    {
        if (is_string($roleId)) {
            $role = LogbookRole::where('name', $roleId)->firstOrFail();
            $roleId = $role->id;
        }

        return $this->logbookAccess()->updateOrCreate(
            ['logbook_template_id' => $templateId],
            ['logbook_role_id' => $roleId]
        );
    }

    /**
     * Remove logbook access for a specific template.
     */
    public function removeLogbookAccess(string $templateId): bool
    {
        return $this->logbookAccess()
            ->where('logbook_template_id', $templateId)
            ->delete() > 0;
    }
}
