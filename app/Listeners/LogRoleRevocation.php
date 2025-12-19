<?php

namespace App\Listeners;

use App\Events\RoleRevoked;
use App\Models\AuditLog;

class LogRoleRevocation
{
    /**
     * Handle the event.
     */
    public function handle(RoleRevoked $event): void
    {
        AuditLog::create([
            'user_id' => $event->userId,
            'action' => 'ROLE_REVOKED',
            'description' => "{$event->performedBy} revoked role '{$event->roleName}' from user '{$event->userName}'",
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
