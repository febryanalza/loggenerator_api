# FCM Push Notification Implementation Examples

This document provides practical examples of how to trigger FCM push notifications in the LogGenerator application.

## Table of Contents
1. [Basic Usage](#basic-usage)
2. [Common Use Cases](#common-use-cases)
3. [Notification Types](#notification-types)
4. [Integration Examples](#integration-examples)

---

## Basic Usage

### Sending Notification to Single User

```php
use App\Events\NotificationSent;

// Trigger FCM notification
event(new NotificationSent(
    userId: 'user-uuid-here',
    title: 'Notification Title',
    body: 'Notification message body',
    data: [
        'key' => 'value',
        // Additional data
    ],
    type: 'notification_type' // optional
));
```

### Sending Notification to Multiple Users

```php
use App\Events\NotificationSent;

event(new NotificationSent(
    userId: ['user-1-uuid', 'user-2-uuid', 'user-3-uuid'],
    title: 'Notification Title',
    body: 'Notification message body',
    data: [
        'key' => 'value',
    ],
    type: 'notification_type'
));
```

---

## Common Use Cases

### 1. Logbook Approval Notification

**Trigger**: When supervisor approves a logbook

```php
// In LogbookVerificationController.php

use App\Events\NotificationSent;

public function approve(Request $request, $verificationId)
{
    $verification = LogbookVerification::findOrFail($verificationId);
    $logbook = $verification->logbookData;
    $user = $logbook->user;

    // Update verification status
    $verification->update([
        'status' => 'approved',
        'verified_at' => now(),
    ]);

    // Create in-app notification
    Notification::create([
        'id' => Str::uuid(),
        'user_id' => $user->id,
        'title' => 'Logbook Approved',
        'message' => "Your logbook '{$logbook->title}' has been approved",
        'type' => 'logbook_approval',
        'related_type' => 'logbook',
        'related_id' => $logbook->id,
    ]);

    // 🔥 Trigger FCM Push Notification
    event(new NotificationSent(
        userId: $user->id,
        title: 'Logbook Approved ✅',
        body: "Your logbook '{$logbook->title}' has been approved by supervisor",
        data: [
            'logbook_id' => $logbook->id,
            'verification_id' => $verification->id,
            'action' => 'view_logbook',
        ],
        type: 'logbook_approval'
    ));

    return response()->json([
        'success' => true,
        'message' => 'Logbook approved successfully',
    ]);
}
```

### 2. Comment Reply Notification

**Trigger**: When someone replies to a user's comment

```php
// In CommentController.php (hypothetical)

use App\Events\NotificationSent;

public function store(Request $request)
{
    $comment = Comment::create([
        'logbook_id' => $request->logbook_id,
        'user_id' => auth()->id(),
        'content' => $request->content,
        'parent_id' => $request->parent_id, // Reply to another comment
    ]);

    // If this is a reply, notify the original commenter
    if ($comment->parent_id) {
        $parentComment = Comment::find($comment->parent_id);
        $parentAuthor = $parentComment->user;

        // Don't notify if replying to own comment
        if ($parentAuthor->id !== auth()->id()) {
            // 🔥 Trigger FCM Push Notification
            event(new NotificationSent(
                userId: $parentAuthor->id,
                title: '💬 New Reply',
                body: auth()->user()->name . " replied to your comment",
                data: [
                    'logbook_id' => $comment->logbook_id,
                    'comment_id' => $comment->id,
                    'parent_comment_id' => $comment->parent_id,
                    'action' => 'view_comment',
                ],
                type: 'comment_reply'
            ));
        }
    }

    return response()->json([
        'success' => true,
        'data' => $comment,
    ]);
}
```

### 3. Logbook Access Granted Notification

**Trigger**: When user is granted access to a logbook template

```php
// In UserLogbookAccessController.php

use App\Events\NotificationSent;
use App\Events\LogbookAccessGranted;

public function grantAccess(Request $request)
{
    $access = UserLogbookAccess::create([
        'user_id' => $request->user_id,
        'logbook_template_id' => $request->template_id,
        'role' => $request->role,
    ]);

    $user = User::find($request->user_id);
    $template = LogbookTemplate::find($request->template_id);

    // Fire LogbookAccessGranted event (existing)
    event(new LogbookAccessGranted($access));

    // 🔥 Trigger FCM Push Notification
    event(new NotificationSent(
        userId: $user->id,
        title: '🔓 New Access Granted',
        body: "You've been granted {$access->role} access to '{$template->name}'",
        data: [
            'template_id' => $template->id,
            'access_id' => $access->id,
            'role' => $access->role,
            'action' => 'view_template',
        ],
        type: 'access_granted'
    ));

    return response()->json([
        'success' => true,
        'message' => 'Access granted successfully',
    ]);
}
```

### 4. Logbook Deadline Reminder

**Trigger**: Scheduled job that runs daily to check approaching deadlines

```php
// In app/Console/Commands/SendDeadlineReminders.php

namespace App\Console\Commands;

use App\Events\NotificationSent;
use App\Models\LogbookData;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendDeadlineReminders extends Command
{
    protected $signature = 'logbook:deadline-reminders';
    protected $description = 'Send deadline reminders for logbooks';

    public function handle()
    {
        // Get logbooks with deadlines in 3 days
        $upcomingLogbooks = LogbookData::whereNotNull('deadline')
            ->whereBetween('deadline', [now(), now()->addDays(3)])
            ->where('status', '!=', 'submitted')
            ->get();

        foreach ($upcomingLogbooks as $logbook) {
            $daysLeft = now()->diffInDays($logbook->deadline);

            // 🔥 Trigger FCM Push Notification
            event(new NotificationSent(
                userId: $logbook->user_id,
                title: '⏰ Deadline Reminder',
                body: "Logbook '{$logbook->title}' is due in {$daysLeft} days",
                data: [
                    'logbook_id' => $logbook->id,
                    'deadline' => $logbook->deadline->toIso8601String(),
                    'days_left' => $daysLeft,
                    'action' => 'view_logbook',
                ],
                type: 'deadline_reminder'
            ));
        }

        $this->info("Sent {$upcomingLogbooks->count()} deadline reminders");
        return 0;
    }
}
```

Register in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('logbook:deadline-reminders')
        ->dailyAt('09:00'); // Run every day at 9 AM
}
```

### 5. Logbook Rejection Notification

**Trigger**: When supervisor rejects a logbook

```php
// In LogbookVerificationController.php

use App\Events\NotificationSent;

public function reject(Request $request, $verificationId)
{
    $verification = LogbookVerification::findOrFail($verificationId);
    $logbook = $verification->logbookData;
    $user = $logbook->user;

    $verification->update([
        'status' => 'rejected',
        'rejection_reason' => $request->reason,
        'verified_at' => now(),
    ]);

    // 🔥 Trigger FCM Push Notification
    event(new NotificationSent(
        userId: $user->id,
        title: '❌ Logbook Needs Revision',
        body: "Your logbook '{$logbook->title}' requires changes",
        data: [
            'logbook_id' => $logbook->id,
            'verification_id' => $verification->id,
            'reason' => $request->reason,
            'action' => 'view_logbook',
        ],
        type: 'logbook_rejection'
    ));

    return response()->json([
        'success' => true,
        'message' => 'Logbook rejected',
    ]);
}
```

### 6. Broadcast Notification to All Users

**Trigger**: Admin wants to send announcement to everyone

```php
// In NotificationController.php

use App\Events\NotificationSent;

public function sendBroadcast(Request $request)
{
    $this->authorize('notifications.send.all');

    $request->validate([
        'title' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    // Get all active users
    $userIds = User::where('is_active', true)
        ->pluck('id')
        ->toArray();

    // 🔥 Trigger FCM Push Notification to all users
    event(new NotificationSent(
        userId: $userIds,
        title: $request->title,
        body: $request->message,
        data: [
            'broadcast' => true,
            'action' => 'view_announcement',
        ],
        type: 'broadcast'
    ));

    return response()->json([
        'success' => true,
        'message' => "Broadcast sent to " . count($userIds) . " users",
    ]);
}
```

---

## Notification Types

Define notification types for consistent handling:

```php
// Can be stored in config/notifications.php

return [
    'types' => [
        'logbook_approval' => [
            'icon' => '✅',
            'color' => '#10b981', // green
            'sound' => 'default',
        ],
        'logbook_rejection' => [
            'icon' => '❌',
            'color' => '#ef4444', // red
            'sound' => 'default',
        ],
        'comment_reply' => [
            'icon' => '💬',
            'color' => '#3b82f6', // blue
            'sound' => 'default',
        ],
        'access_granted' => [
            'icon' => '🔓',
            'color' => '#8b5cf6', // purple
            'sound' => 'default',
        ],
        'deadline_reminder' => [
            'icon' => '⏰',
            'color' => '#f59e0b', // orange
            'sound' => 'urgent',
        ],
        'broadcast' => [
            'icon' => '📢',
            'color' => '#06b6d4', // cyan
            'sound' => 'default',
        ],
    ],
];
```

---

## Integration Examples

### Example 1: In Existing Notification System

Modify existing notification creation to also trigger FCM:

```php
// Before (only in-app notification)
Notification::create([
    'user_id' => $user->id,
    'title' => 'Title',
    'message' => 'Message',
]);

// After (in-app + FCM push)
$notification = Notification::create([
    'user_id' => $user->id,
    'title' => 'Title',
    'message' => 'Message',
    'type' => 'notification_type',
]);

// Trigger FCM
event(new NotificationSent(
    userId: $user->id,
    title: $notification->title,
    body: $notification->message,
    data: [
        'notification_id' => $notification->id,
        'type' => $notification->type,
    ],
    type: $notification->type
));
```

### Example 2: Using Database Transactions

Ensure notification is only sent if database operation succeeds:

```php
use Illuminate\Support\Facades\DB;
use App\Events\NotificationSent;

DB::transaction(function () use ($logbook, $user) {
    // Update logbook
    $logbook->update(['status' => 'approved']);

    // Create in-app notification
    $notification = Notification::create([
        'user_id' => $user->id,
        'title' => 'Logbook Approved',
        'message' => "Your logbook has been approved",
    ]);

    // Only trigger FCM if transaction succeeds
    event(new NotificationSent(
        userId: $user->id,
        title: $notification->title,
        body: $notification->message,
        data: ['logbook_id' => $logbook->id],
        type: 'logbook_approval'
    ));
});
```

### Example 3: Conditional FCM Based on User Preferences

```php
// Check if user has enabled push notifications
if ($user->settings['push_notifications_enabled'] ?? true) {
    event(new NotificationSent(
        userId: $user->id,
        title: 'Title',
        body: 'Message',
        data: [],
        type: 'type'
    ));
}
```

---

## Testing Push Notifications

### Manual Test via Tinker

```bash
php artisan tinker
```

```php
>>> use App\Events\NotificationSent;
>>> event(new NotificationSent(
...     userId: 'your-user-uuid',
...     title: 'Test Notification',
...     body: 'This is a test from Tinker',
...     data: ['test' => true],
...     type: 'test'
... ));
```

### Test Route (Development Only)

```php
// routes/api.php (remove in production!)

Route::get('/test-push/{userId}', function ($userId) {
    event(new \App\Events\NotificationSent(
        userId: $userId,
        title: 'Test Push',
        body: 'Testing FCM integration',
        data: ['test' => true],
        type: 'test'
    ));

    return response()->json(['message' => 'Push sent']);
})->middleware('auth:sanctum');
```

---

## Best Practices

1. ✅ **Always include notification type** for proper handling in Flutter
2. ✅ **Provide related_id** for navigation (logbook_id, comment_id, etc.)
3. ✅ **Keep titles under 65 characters** for mobile display
4. ✅ **Keep body under 240 characters** to avoid truncation
5. ✅ **Use emojis sparingly** in titles for visual appeal
6. ✅ **Test all notification types** before deploying
7. ✅ **Handle FCM failures gracefully** - don't block user actions
8. ✅ **Queue notifications** for bulk sends to avoid timeouts
9. ✅ **Log notification sends** for debugging
10. ✅ **Respect user notification preferences**

---

## Monitoring & Debugging

### Check FCM Tokens in Database

```sql
-- Active tokens
SELECT user_id, device_type, device_name, last_used_at 
FROM fcm_tokens 
WHERE is_active = 1;

-- Inactive tokens
SELECT user_id, device_type, updated_at 
FROM fcm_tokens 
WHERE is_active = 0;
```

### Monitor Queue Jobs

```bash
# Watch queue processing
php artisan queue:listen --verbose
```

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log | grep FCM
```

---

**Last Updated**: December 2025  
**Version**: 1.0.0
