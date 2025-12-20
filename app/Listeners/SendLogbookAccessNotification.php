<?php

namespace App\Listeners;

use App\Events\LogbookAccessGranted;
use App\Events\NotificationSent;
use App\Models\AuditLog;
use App\Notifications\LogbookAccessNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SendLogbookAccessNotification // implements ShouldQueue
{
    // use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LogbookAccessGranted $event): void
    {
        try {
            // Send notification to the user who was granted access
            $notification = new LogbookAccessNotification(
                $event->grantedBy,
                $event->logbookTemplate,
                $event->role
            );
            
            $event->grantedUser->notify($notification);

            // 🔥 Trigger FCM push notification
            event(new NotificationSent(
                userId: $event->grantedUser->id,
                title: 'Hak Akses Template',
                body: "Anda telah diberikan hak akses {$event->role} untuk template '{$event->logbookTemplate->title}' oleh {$event->grantedBy->name}",
                data: [
                    'template_id' => $event->logbookTemplate->id,
                    'role' => $event->role,
                    'granted_by_id' => $event->grantedBy->id,
                ],
                type: 'logbook_access_granted'
            ));

            // Create audit log for the notification
            AuditLog::create([
                'user_id' => $event->grantedBy->id,
                'action' => 'SEND_LOGBOOK_ACCESS_NOTIFICATION',
                'description' => "Sent logbook access notification to {$event->grantedUser->email} for template '{$event->logbookTemplate->title}' as {$event->role}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            Log::info('Logbook access notification sent', [
                'granted_user_id' => $event->grantedUser->id,
                'granted_by_id' => $event->grantedBy->id,
                'logbook_template_id' => $event->logbookTemplate->id,
                'role' => $event->role
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send logbook access notification', [
                'error' => $e->getMessage(),
                'granted_user_id' => $event->grantedUser->id,
                'granted_by_id' => $event->grantedBy->id,
                'logbook_template_id' => $event->logbookTemplate->id,
                'role' => $event->role
            ]);
            
            throw $e; // Re-throw to trigger retry mechanism if queue is used
        }
    }
}
