<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'phone_number',
        'address',
        'company_type',
        'company_email',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the users that belong to this institution
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'institution_id');
    }

    /**
     * Get the logbook templates that belong to this institution
     */
    public function logbookTemplates(): HasMany
    {
        return $this->hasMany(LogbookTemplate::class, 'institution_id');
    }

    /**
     * Get institution admins for this institution
     */
    public function institutionAdmins(): HasMany
    {
        return $this->hasMany(User::class, 'institution_id')
                    ->role('Institution Admin');
    }

    /**
     * Scope to get institutions with their users count
     */
    public function scopeWithUsersCount($query)
    {
        return $query->withCount('users');
    }

    /**
     * Scope to get institutions with their templates count
     */
    public function scopeWithTemplatesCount($query)
    {
        return $query->withCount('logbookTemplates');
    }
}
