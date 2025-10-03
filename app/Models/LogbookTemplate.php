<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogbookTemplate extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logbook_template';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'institution_id',
        'has_been_assessed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_been_assessed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * This is the enterprise-standard way to handle automatic data insertion
     */
    protected static function booted(): void
    {
        // Automatically create user logbook access when template is created
        static::created(function (LogbookTemplate $template) {
            // Ensure we have an authenticated user
            if (Auth::check()) {
                // Use database transaction for data consistency
                DB::transaction(function () use ($template) {
                    // Get Owner role ID dynamically
                    $ownerRoleId = DB::table('logbook_roles')->where('name', 'Owner')->value('id');
                    
                    if ($ownerRoleId) {
                        DB::table('user_logbook_access')->insert([
                            'id' => DB::raw('uuid_generate_v4()'),
                            'user_id' => Auth::id(),
                            'logbook_template_id' => $template->id,
                            'logbook_role_id' => $ownerRoleId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });
            }
        });
    }

    /**
     * Get the fields for this template.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(LogbookField::class, 'template_id');
    }

    /**
     * Get the data entries for this template.
     */
    public function data(): HasMany
    {
        return $this->hasMany(LogbookData::class, 'template_id');
    }

    /**
     * Get the user access entries for this template.
     */
    public function userAccess(): HasMany
    {
        return $this->hasMany(UserLogbookAccess::class, 'logbook_template_id');
    }

    /**
     * Get users who have access to this template.
     */
    public function accessibleUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_logbook_access', 'logbook_template_id', 'user_id')
                    ->withPivot(['logbook_role_id', 'created_at', 'updated_at'])
                    ->withTimestamps();
    }

    /**
     * Get the institution that this template belongs to
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    /**
     * Check if template belongs to a specific institution
     */
    public function belongsToInstitution(string $institutionId): bool
    {
        return $this->institution_id === $institutionId;
    }

    /**
     * Scope to get templates for a specific institution
     */
    public function scopeForInstitution($query, string $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    /**
     * Scope to get templates without institution (global templates)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('institution_id');
    }
}