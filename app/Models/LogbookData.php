<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     * This prevents internal Laravel tracking attributes from being exposed.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        '_originalDataContent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * The attributes that should NOT be persisted to the database.
     * This prevents Laravel's internal dirty tracking attributes from being saved.
     *
     * @return array
     */
    public static function boot()
    {
        parent::boot();

        // Remove any internal tracking attributes before saving
        static::saving(function ($model) {
            // Remove Laravel's internal array cast tracking attributes
            unset($model->attributes['_originalDataContent']);
        });
    }

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
     * Get all verifications for this logbook entry.
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(LogbookDataVerification::class, 'data_id');
    }

    /**
     * Get all verified verifications for this logbook entry.
     */
    public function approvedVerifications(): HasMany
    {
        return $this->hasMany(LogbookDataVerification::class, 'data_id')
            ->where('is_verified', true);
    }

    /**
     * Get all rejected verifications for this logbook entry.
     */
    public function rejectedVerifications(): HasMany
    {
        return $this->hasMany(LogbookDataVerification::class, 'data_id')
            ->where('is_verified', false);
    }

    /**
     * Get all supervisors for this logbook's template.
     */
    public function getTemplateSupervisors()
    {
        $supervisorRole = LogbookRole::where('name', 'Supervisor')->first();
        
        if (!$supervisorRole) {
            return collect();
        }

        return UserLogbookAccess::where('logbook_template_id', $this->template_id)
            ->where('logbook_role_id', $supervisorRole->id)
            ->with('user')
            ->get()
            ->pluck('user');
    }

    /**
     * Get the count of supervisors for this template.
     */
    public function getSupervisorCount(): int
    {
        $supervisorRole = LogbookRole::where('name', 'Supervisor')->first();
        
        if (!$supervisorRole) {
            return 0;
        }

        return UserLogbookAccess::where('logbook_template_id', $this->template_id)
            ->where('logbook_role_id', $supervisorRole->id)
            ->count();
    }

    /**
     * Scope query to get entries that have at least one verified verification.
     */
    public function scopeVerified($query)
    {
        return $query->whereHas('verifications', function ($q) {
            $q->where('is_verified', true);
        });
    }

    /**
     * Scope query to get entries that have no verified verifications.
     */
    public function scopeUnverified($query)
    {
        return $query->whereDoesntHave('verifications', function ($q) {
            $q->where('is_verified', true);
        });
    }

    /**
     * Check if this entry is FULLY verified (all supervisors have approved).
     * Uses AND logic: only true if ALL supervisors have verified.
     * If no supervisors assigned, automatically returns true (no verification needed).
     */
    public function isVerified(): bool
    {
        $supervisorCount = $this->getSupervisorCount();
        
        // If no supervisors assigned, consider it automatically verified
        // (no verification process needed for this logbook)
        if ($supervisorCount === 0) {
            return true;
        }

        // Count how many supervisors have approved
        $approvedCount = $this->verifications()
            ->where('is_verified', true)
            ->count();

        // Only verified if ALL supervisors have approved
        return $approvedCount >= $supervisorCount;
    }

    /**
     * Check if this entry has been verified by a specific user.
     */
    public function isVerifiedBy(string $userId): bool
    {
        return $this->verifications()
            ->where('verifier_id', $userId)
            ->where('is_verified', true)
            ->exists();
    }

    /**
     * Check if a user has already given any verification (approved or rejected).
     */
    public function hasVerificationFrom(string $userId): bool
    {
        return $this->verifications()
            ->where('verifier_id', $userId)
            ->exists();
    }

    /**
     * Get verification status from a specific user.
     */
    public function getVerificationFrom(string $userId): ?LogbookDataVerification
    {
        return $this->verifications()
            ->where('verifier_id', $userId)
            ->first();
    }

    /**
     * Get the count of verified verifications (approved).
     */
    public function getVerifiedCount(): int
    {
        return $this->verifications()->where('is_verified', true)->count();
    }

    /**
     * Get the count of rejected verifications.
     */
    public function getRejectedCount(): int
    {
        return $this->verifications()->where('is_verified', false)->count();
    }

    /**
     * Get the count of pending verifications (not yet verified by supervisors).
     */
    public function getPendingCount(): int
    {
        return $this->verifications()
            ->whereNull('verified_at')
            ->count();
    }

    /**
     * Get all verifiers who approved this entry.
     */
    public function getApprovedVerifiers()
    {
        return User::whereIn('id', $this->verifications()
            ->where('is_verified', true)
            ->pluck('verifier_id')
        )->get();
    }

    /**
     * Get verification details for response.
     */
    public function getVerificationDetails(): array
    {
        $supervisorCount = $this->getSupervisorCount();
        
        // If no supervisors, return special response indicating no verification needed
        if ($supervisorCount === 0) {
            return [
                'total_supervisors' => 0,
                'approved_count' => 0,
                'rejected_count' => 0,
                'pending_count' => 0,
                'no_supervisor_required' => true,
                'message' => 'No supervisors assigned - verification not required',
                'verifications' => [],
            ];
        }
        
        $verifications = $this->verifications()->with('verifier:id,name,email')->get();
        
        $approvedCount = $verifications->where('is_verified', true)->whereNotNull('verified_at')->count();
        $rejectedCount = $verifications->where('is_verified', false)->whereNotNull('verified_at')->count();
        $pendingCount = $verifications->whereNull('verified_at')->count();

        return [
            'total_supervisors' => $supervisorCount,
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'pending_count' => $pendingCount,
            'no_supervisor_required' => false,
            'verifications' => $verifications->map(function ($v) {
                return [
                    'id' => $v->id,
                    'verifier' => $v->verifier ? [
                        'id' => $v->verifier->id,
                        'name' => $v->verifier->name,
                        'email' => $v->verifier->email,
                    ] : null,
                    'is_verified' => $v->is_verified,
                    'verified_at' => $v->verified_at,
                    'verification_notes' => $v->verification_notes,
                ];
            }),
        ];
    }

    /**
     * Create initial verification records for all supervisors.
     * Called when a new logbook entry is created.
     */
    public function createInitialVerifications(): void
    {
        $supervisors = $this->getTemplateSupervisors();

        foreach ($supervisors as $supervisor) {
            LogbookDataVerification::firstOrCreate(
                [
                    'data_id' => $this->id,
                    'verifier_id' => $supervisor->id,
                ],
                [
                    'is_verified' => false,
                    'verified_at' => null,
                    'verification_notes' => null,
                ]
            );
        }
    }

    /**
     * Mark this entry as verified by a user.
     */
    public function verify(string $userId, ?string $notes = null): LogbookDataVerification
    {
        return LogbookDataVerification::verify($this->id, $userId, $notes);
    }

    /**
     * Mark this entry as rejected by a user.
     */
    public function reject(string $userId, ?string $notes = null): LogbookDataVerification
    {
        return LogbookDataVerification::reject($this->id, $userId, $notes);
    }

    /**
     * Remove verification from a user.
     */
    public function removeVerification(string $userId): bool
    {
        return LogbookDataVerification::removeVerification($this->id, $userId);
    }

    /**
     * Reset all verifications to pending state.
     * Used when data is updated and requires re-verification.
     *
     * @param string|null $reason Optional reason for the reset
     * @return int Number of verifications reset
     */
    public function resetAllVerifications(?string $reason = null): int
    {
        $verifications = $this->verifications()
            ->whereNotNull('verified_at')
            ->get();

        $resetCount = 0;

        foreach ($verifications as $verification) {
            $previousStatus = $verification->is_verified ? 'Approved' : 'Rejected';
            $previousNote = $verification->verification_notes;

            $newNote = "[RESET] " . ($reason ?? "Data was modified") . " on " . now()->format('Y-m-d H:i:s') . ". ";
            $newNote .= "Previous status: {$previousStatus}. ";
            
            if ($previousNote) {
                $newNote .= "Previous note: {$previousNote}";
            }

            $verification->update([
                'is_verified' => false,
                'verified_at' => null,
                'verification_notes' => $newNote,
            ]);

            $resetCount++;
        }

        return $resetCount;
    }

    /**
     * Check if this entry has any verified verifications that would need reset.
     *
     * @return bool
     */
    public function hasVerifiedRecords(): bool
    {
        return $this->verifications()
            ->whereNotNull('verified_at')
            ->exists();
    }
}