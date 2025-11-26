<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookData extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logbook_datas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',
        'writer_id',
        'data',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the template that this data belongs to.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LogbookTemplate::class, 'template_id');
    }

    /**
     * Get the user who wrote this logbook entry.
     */
    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    /**
     * Get the user who verified this logbook entry.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope query to get verified entries only.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope query to get unverified entries only.
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Check if this entry is verified.
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Mark this entry as verified.
     */
    public function markAsVerified(string $userId, string $notes = null): bool
    {
        return $this->update([
            'is_verified' => true,
            'verified_by' => $userId,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    /**
     * Mark this entry as unverified.
     */
    public function markAsUnverified(): bool
    {
        return $this->update([
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
            'verification_notes' => null,
        ]);
    }
}