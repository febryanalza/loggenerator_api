<?php

namespace App\Listeners;

use App\Events\RoleAssigned;
use App\Models\AuditLog;

class LogRoleAssignment
{
    /**
     * Handle the event.
     */
    public function handle(RoleAssigned $event): void
    {
        AuditLog::create([
            'user_id' => $event->userId,
            'action' => 'ROLE_ASSIGNED',
            'description' => "{$event->performedBy} assigned role '{$event->roleName}' to user '{$event->userName}'",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => json_encode(array_merge([
                'target_user_id' => $event->userId,
                'target_user_name' => $event->userName,
                'role_name' => $event->roleName,
                'performed_by' => $event->performedBy,
            ], $event->metadata ?? []))
        ]);
    }
}
