<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LogbookDataVerification extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logbook_data_verifications';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'data_id',
        'verifier_id',
        'is_verified',
        'verified_at',
        'verification_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the logbook data that this verification belongs to.
     */
    public function data(): BelongsTo
    {
        return $this->belongsTo(LogbookData::class, 'data_id');
    }

    /**
     * Get the user who made this verification.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    /**
     * Scope a query to only include verified entries.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include rejected entries.
     */
    public function scopeRejected($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope a query to filter by verifier.
     */
    public function scopeByVerifier($query, $verifierId)
    {
        return $query->where('verifier_id', $verifierId);
    }

    /**
     * Scope a query to filter by data.
     */
    public function scopeByData($query, $dataId)
    {
        return $query->where('data_id', $dataId);
    }

    /**
     * Create a verification record.
     */
    public static function verify(string $dataId, string $verifierId, ?string $notes = null): self
    {
        return self::updateOrCreate(
            [
                'data_id' => $dataId,
                'verifier_id' => $verifierId,
            ],
            [
                'is_verified' => true,
                'verified_at' => now(),
                'verification_notes' => $notes,
            ]
        );
    }

    /**
     * Create a rejection record.
     */
    public static function reject(string $dataId, string $verifierId, ?string $notes = null): self
    {
        return self::updateOrCreate(
            [
                'data_id' => $dataId,
                'verifier_id' => $verifierId,
            ],
            [
                'is_verified' => false,
                'verified_at' => now(),
                'verification_notes' => $notes,
            ]
        );
    }

    /**
     * Remove a verification record.
     */
    public static function removeVerification(string $dataId, string $verifierId): bool
    {
        return self::where('data_id', $dataId)
            ->where('verifier_id', $verifierId)
            ->delete() > 0;
    }
}
