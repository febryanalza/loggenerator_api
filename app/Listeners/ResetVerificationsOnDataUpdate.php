<?php

namespace App\Listeners;

use App\Events\LogbookDataUpdated;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ResetVerificationsOnDataUpdate implements ShouldQueue
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Handle the event.
     *
     * @param LogbookDataUpdated $event
     * @return void
     */
    public function handle(LogbookDataUpdated $event): void
    {
        $logbookData = $event->logbookData;

        // Only reset if data content actually changed
        if (!$event->hasDataContentChanged()) {
            Log::info('LogbookData updated but no content change detected, skipping verification reset', [
                'logbook_data_id' => $logbookData->id,
            ]);
            return;
        }

        // Get all verifications that have been verified (approved or rejected)
        $verifications = $logbookData->verifications()
            ->whereNotNull('verified_at')
            ->get();

        if ($verifications->isEmpty()) {
            Log::info('No verified verifications to reset for logbook data', [
                'logbook_data_id' => $logbookData->id,
            ]);
            return;
        }

        $resetCount = 0;
        $verifierNames = [];

        foreach ($verifications as $verification) {
            // Store verifier name for audit log
            if ($verification->verifier) {
                $verifierNames[] = $verification->verifier->name;
            }

            // Reset verification to pending state
            $verification->update([
                'is_verified' => false,
                'verified_at' => null,
                'verification_notes' => $this->buildResetNote($verification, $event),
            ]);

            $resetCount++;
        }

        Log::info('Verification records reset due to data update', [
            'logbook_data_id' => $logbookData->id,
            'reset_count' => $resetCount,
            'updated_by' => $event->updatedBy?->id,
            'changed_fields' => $event->getChangedFields(),
        ]);

        // Create audit log
        $this->createAuditLog($event, $resetCount, $verifierNames);
    }

    /**
     * Build a note explaining why verification was reset.
     *
     * @param mixed $verification
     * @param LogbookDataUpdated $event
     * @return string
     */
    protected function buildResetNote($verification, LogbookDataUpdated $event): string
    {
        $previousStatus = $verification->is_verified ? 'Approved' : 'Rejected';
        $previousNote = $verification->verification_notes;
        $updatedByName = $event->updatedBy?->name ?? 'System';
        $changedFields = $event->getChangedFields();

        $note = "[RESET] Data was modified by {$updatedByName} on " . now()->format('Y-m-d H:i:s') . ". ";
        $note .= "Previous status: {$previousStatus}. ";
        
        if (!empty($changedFields)) {
            $note .= "Changed fields: " . implode(', ', $changedFields) . ". ";
        }
        
        if ($previousNote) {
            $note .= "Previous note: {$previousNote}";
        }

        return $note;
    }

    /**
     * Create an audit log entry for the verification reset.
     *
     * @param LogbookDataUpdated $event
     * @param int $resetCount
     * @param array $verifierNames
     * @return void
     */
    protected function createAuditLog(LogbookDataUpdated $event, int $resetCount, array $verifierNames): void
    {
        try {
            $logbookData = $event->logbookData;
            $templateName = $logbookData->template?->name ?? 'Unknown Template';
            $changedFields = $event->getChangedFields();

            $description = "Reset {$resetCount} verification(s) for logbook entry in '{$templateName}' due to data update. ";
            
            if (!empty($verifierNames)) {
                $description .= "Affected verifiers: " . implode(', ', $verifierNames) . ". ";
            }
            
            if (!empty($changedFields)) {
                $description .= "Changed fields: " . implode(', ', $changedFields) . ".";
            }

            AuditLog::create([
                'user_id' => $event->updatedBy?->id,
                'action' => 'RESET_VERIFICATIONS_ON_UPDATE',
                'description' => $description,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit log for verification reset', [
                'error' => $e->getMessage(),
                'logbook_data_id' => $event->logbookData->id,
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param LogbookDataUpdated $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(LogbookDataUpdated $event, \Throwable $exception): void
    {
        Log::error('Failed to reset verifications on data update', [
            'logbook_data_id' => $event->logbookData->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
