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

            // Sanitize data payload (FCM requires all values to be strings)
            $data = $this->sanitizeDataPayload($data);

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

    /**
     * Sanitize data payload for FCM
     * FCM requires all data values to be strings
     * 
     * @param array $data
     * @return array
     */
    private function sanitizeDataPayload(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                // Convert null to empty string
                $sanitized[$key] = '';
            } elseif (is_array($value) || is_object($value)) {
                // Convert arrays/objects to JSON string
                $sanitized[$key] = json_encode($value);
            } elseif (is_bool($value)) {
                // Convert boolean to string "true" or "false"
                $sanitized[$key] = $value ? 'true' : 'false';
            } elseif (is_numeric($value)) {
                // Convert numbers to string
                $sanitized[$key] = (string) $value;
            } else {
                // Keep strings as-is
                $sanitized[$key] = (string) $value;
            }
        }

        return $sanitized;
    }
}
