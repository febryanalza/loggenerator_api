<?php

namespace App\Listeners;

use App\Events\NotificationSent;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener to send FCM push notifications when NotificationSent event is fired
 * 
 * This listener runs in the queue to avoid blocking the request
 */
class SendFCMNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [10, 30, 60];

    /**
     * Firebase service instance
     */
    protected FirebaseService $firebaseService;

    /**
     * Create the event listener.
     */
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle the event.
     *
     * @param NotificationSent $event
     * @return void
     */
    public function handle(NotificationSent $event): void
    {
        try {
            // Check if Firebase is configured
            if (!$this->firebaseService->isConfigured()) {
                Log::warning('FCM not configured. Skipping push notification.');
                return;
            }

            // Add notification type to data if provided
            $data = $event->data;
            if ($event->type) {
                $data['type'] = $event->type;
            }

            // Send to single user or multiple users
            if (is_array($event->userId)) {
                $result = $this->firebaseService->sendToUsers(
                    $event->userId,
                    $event->title,
                    $event->body,
                    $data
                );
            } else {
                $result = $this->firebaseService->sendToUser(
                    $event->userId,
                    $event->title,
                    $event->body,
                    $data
                );
            }

            Log::info('FCM notification sent', [
                'user_id' => $event->userId,
                'type' => $event->type,
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification: ' . $e->getMessage(), [
                'user_id' => $event->userId,
                'type' => $event->type,
                'exception' => $e,
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(NotificationSent $event, \Throwable $exception): void
    {
        Log::error('FCM notification failed after all retries', [
            'user_id' => $event->userId,
            'type' => $event->type,
            'exception' => $exception->getMessage(),
        ]);
    }
}
