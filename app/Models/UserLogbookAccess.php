<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLogbookAccess extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_logbook_access';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'logbook_template_id',
        'logbook_role_id',
    ];

    /**
     * Get the user that has this access.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the logbook template this access is for.
     */
    public function logbookTemplate(): BelongsTo
    {
        return $this->belongsTo(LogbookTemplate::class, 'logbook_template_id');
    }

    /**
     * Get the logbook role for this access.
     */
    public function logbookRole(): BelongsTo
    {
        return $this->belongsTo(LogbookRole::class, 'logbook_role_id');
    }

    /**
     * Check if the user has a specific logbook permission for this template.
     */
    public function hasLogbookPermission(string $permissionName): bool
    {
        return $this->logbookRole->hasPermission($permissionName);
    }
}