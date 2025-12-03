<?php

namespace App\Observers;

use App\Events\LogbookDataUpdated;
use App\Models\LogbookData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogbookDataObserver
{
    /**
     * Handle the LogbookData "updating" event.
     * Store original data before update for comparison.
     *
     * @param LogbookData $logbookData
     * @return void
     */
    public function updating(LogbookData $logbookData): void
    {
        // Store original data in a runtime property for later comparison
        $logbookData->_originalDataContent = $logbookData->getOriginal('data');
    }

    /**
     * Handle the LogbookData "updated" event.
     * Fire event if data content was changed.
     *
     * @param LogbookData $logbookData
     * @return void
     */
    public function updated(LogbookData $logbookData): void
    {
        // Check if 'data' field was changed
        if (!$logbookData->wasChanged('data')) {
            return;
        }

        // Get original and new data
        $originalData = $logbookData->_originalDataContent ?? [];
        $newData = $logbookData->data ?? [];

        // Ensure both are arrays
        if (is_string($originalData)) {
            $originalData = json_decode($originalData, true) ?? [];
        }
        if (is_string($newData)) {
            $newData = json_decode($newData, true) ?? [];
        }

        // Skip if data is identical (no actual change)
        if ($originalData === $newData) {
            Log::debug('LogbookData updated but content identical, skipping event', [
                'logbook_data_id' => $logbookData->id,
            ]);
            return;
        }

        // Check if there are any verifications that need to be reset
        $hasVerifiedRecords = $logbookData->verifications()
            ->whereNotNull('verified_at')
            ->exists();

        if (!$hasVerifiedRecords) {
            Log::debug('LogbookData updated but no verified records to reset', [
                'logbook_data_id' => $logbookData->id,
            ]);
            return;
        }

        // Fire the event
        Log::info('LogbookData content changed, firing LogbookDataUpdated event', [
            'logbook_data_id' => $logbookData->id,
            'updated_by' => Auth::id(),
        ]);

        event(new LogbookDataUpdated(
            $logbookData,
            Auth::user(),
            $originalData,
            $newData
        ));

        // Clean up runtime property
        unset($logbookData->_originalDataContent);
    }

    /**
     * Handle the LogbookData "created" event.
     *
     * @param LogbookData $logbookData
     * @return void
     */
    public function created(LogbookData $logbookData): void
    {
        // Initial verifications are created in the controller
        // This observer hook is available for future use if needed
    }

    /**
     * Handle the LogbookData "deleted" event.
     *
     * @param LogbookData $logbookData
     * @return void
     */
    public function deleted(LogbookData $logbookData): void
    {
        // Verifications will be deleted by cascade constraint
        // This observer hook is available for future use if needed
        Log::info('LogbookData deleted', [
            'logbook_data_id' => $logbookData->id,
        ]);
    }
}
