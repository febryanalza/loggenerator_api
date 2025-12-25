<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Logbook Participant Model
 * 
 * Stores participant data for logbook templates.
 * 
 * The 'data' column is a JSON field that contains participant information
 * based on the institution's required_data_participants configuration.
 * 
 * Example data structure:
 * {
 *   "Nama Lengkap": "John Doe",
 *   "NIM": "12345678",
 *   "Email": "john@example.com",
 *   "Nomor Telepon": "08123456789"
 * }
 * 
 * Keys are taken from required_data_participants.data_name
 * Number of fields varies by institution
 */
class LogbookParticipant extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logbook_participants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',
        'data',
        'grade',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'grade' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Get the template that this participant belongs to.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LogbookTemplate::class, 'template_id');
    }

    /**
     * Scope to get participants for a specific template
     */
    public function scopeForTemplate($query, string $templateId)
    {
        return $query->where('template_id', $templateId);
    }

    /**
     * Scope to get participants with grade in range
     */
    public function scopeWithGradeRange($query, int $min, int $max)
    {
        return $query->whereBetween('grade', [$min, $max]);
    }

    /**
     * Get participant name from data JSON (first available field value)
     */
    public function getNameAttribute(): ?string
    {
        // Try common name fields first
        if (isset($this->data['name'])) return $this->data['name'];
        if (isset($this->data['nama'])) return $this->data['nama'];
        if (isset($this->data['full_name'])) return $this->data['full_name'];
        if (isset($this->data['participant_name'])) return $this->data['participant_name'];
        
        // If not found, return first value from data array
        if (is_array($this->data) && !empty($this->data)) {
            return reset($this->data);
        }
        
        return null;
    }

    /**
     * Check if participant has a passing grade
     */
    public function hasPassed(int $passingGrade = 60): bool
    {
        return $this->grade !== null && $this->grade >= $passingGrade;
    }
}
