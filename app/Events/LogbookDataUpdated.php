<?php

namespace App\Events;

use App\Models\LogbookData;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogbookDataUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The logbook data that was updated.
     *
     * @var LogbookData
     */
    public LogbookData $logbookData;

    /**
     * The user who updated the data.
     *
     * @var User|null
     */
    public ?User $updatedBy;

    /**
     * The original data before update.
     *
     * @var array
     */
    public array $originalData;

    /**
     * The new data after update.
     *
     * @var array
     */
    public array $newData;

    /**
     * Create a new event instance.
     *
     * @param LogbookData $logbookData
     * @param User|null $updatedBy
     * @param array $originalData
     * @param array $newData
     */
    public function __construct(
        LogbookData $logbookData,
        ?User $updatedBy = null,
        array $originalData = [],
        array $newData = []
    ) {
        $this->logbookData = $logbookData;
        $this->updatedBy = $updatedBy;
        $this->originalData = $originalData;
        $this->newData = $newData;
    }

    /**
     * Check if the actual data content was changed (not just metadata).
     *
     * @return bool
     */
    public function hasDataContentChanged(): bool
    {
        // Compare original and new data arrays
        return $this->originalData !== $this->newData;
    }

    /**
     * Get the fields that were changed.
     *
     * @return array
     */
    public function getChangedFields(): array
    {
        $changedFields = [];

        // Check for new or modified fields
        foreach ($this->newData as $key => $value) {
            if (!isset($this->originalData[$key]) || $this->originalData[$key] !== $value) {
                $changedFields[] = $key;
            }
        }

        // Check for removed fields
        foreach ($this->originalData as $key => $value) {
            if (!isset($this->newData[$key]) && !in_array($key, $changedFields)) {
                $changedFields[] = $key;
            }
        }

        return $changedFields;
    }
}
