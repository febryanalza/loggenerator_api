<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'user_id',
    ];

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
     * Get the user that created this template.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}