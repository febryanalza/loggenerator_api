<?php

namespace App\Listeners;

use App\Events\SupervisorAddedToTemplate;
use App\Models\LogbookData;
use App\Models\LogbookDataVerification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateVerificationRecordsForNewSupervisor implements ShouldQueue
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 10;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param SupervisorAddedToTemplate $event
     * @return void
     */
    public function handle(SupervisorAddedToTemplate $event): void
    {
        $supervisor = $event->supervisor;
        $template = $event->template;

        Log::info("Creating verification records for new supervisor", [
            'supervisor_id' => $supervisor->id,
            'supervisor_email' => $supervisor->email,
            'template_id' => $template->id,
            'template_name' => $template->name,
        ]);

        try {
            // Get all existing logbook data for this template
            $existingDataIds = LogbookData::where('template_id', $template->id)
                ->pluck('id');

            if ($existingDataIds->isEmpty()) {
                Log::info("No existing logbook data found for template", [
                    'template_id' => $template->id,
                ]);
                return;
            }

            $createdCount = 0;
            $skippedCount = 0;

            // Process in chunks to avoid memory issues for large datasets
            $existingDataIds->chunk(100)->each(function ($chunk) use ($supervisor, &$createdCount, &$skippedCount) {
                DB::transaction(function () use ($chunk, $supervisor, &$createdCount, &$skippedCount) {
                    foreach ($chunk as $dataId) {
                        // Use firstOrCreate to prevent duplicates
                        $verification = LogbookDataVerification::firstOrCreate(
                            [
                                'data_id' => $dataId,
                                'verifier_id' => $supervisor->id,
                            ],
                            [
                                'is_verified' => false,
                                'verified_at' => null,
                                'verification_notes' => null,
                            ]
                        );

                        if ($verification->wasRecentlyCreated) {
                            $createdCount++;
                        } else {
                            $skippedCount++;
                        }
                    }
                });
            });

            Log::info("Verification records created for new supervisor", [
                'supervisor_id' => $supervisor->id,
                'template_id' => $template->id,
                'created_count' => $createdCount,
                'skipped_count' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to create verification records for new supervisor", [
                'supervisor_id' => $supervisor->id,
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     *
     * @param SupervisorAddedToTemplate $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(SupervisorAddedToTemplate $event, \Throwable $exception): void
    {
        Log::error("Failed to process SupervisorAddedToTemplate event after all retries", [
            'supervisor_id' => $event->supervisor->id,
            'template_id' => $event->template->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
