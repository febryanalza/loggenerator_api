<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailableTemplate extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'available_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'institution_id',
        'created_by',
        'required_columns',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'required_columns' => 'array',
    ];

    /**
     * Get the institution that owns the template.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the user who created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope query to get active templates only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to get inactive templates only.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope query to get templates for a specific institution.
     */
    public function scopeForInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    /**
     * Check if template is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get required columns as collection.
     */
    public function getRequiredColumnsCollection()
    {
        return collect($this->required_columns ?? []);
    }

    /**
     * Add a required column to the template.
     */
    public function addRequiredColumn(string $name, string $dataType, ?string $description = null): self
    {
        $columns = $this->required_columns ?? [];
        $columns[] = [
            'name' => $name,
            'data_type' => $dataType,
            'description' => $description,
        ];
        $this->required_columns = $columns;
        
        return $this;
    }

    /**
     * Remove a required column by name.
     */
    public function removeRequiredColumn(string $name): self
    {
        $columns = collect($this->required_columns ?? [])
            ->filter(fn($col) => $col['name'] !== $name)
            ->values()
            ->toArray();
        $this->required_columns = $columns;
        
        return $this;
    }
}
