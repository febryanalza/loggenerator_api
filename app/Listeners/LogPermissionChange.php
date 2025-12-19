<?php

namespace App\Listeners;

use App\Events\PermissionChanged;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class LogPermissionChange
{
    /**
     * Handle the event.
     */
    public function handle(PermissionChanged $event): void
    {
        $user = Auth::user();
        
        AuditLog::create([
            'user_id' => $event->userId ?? $user?->id,
            'action' => 'PERMISSION_' . strtoupper($event->action),
            'description' => $this->buildDescription($event),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => json_encode(array_merge([
                'entity_type' => $event->entityType,
                'entity_name' => $event->entityName,
                'performed_by' => $event->performedBy ?? $user?->name,
            ], $event->metadata ?? []))
        ]);
    }

    private function buildDescription(PermissionChanged $event): string
    {
        $performer = $event->performedBy ?? Auth::user()?->name ?? 'System';
        
        return match($event->action) {
            'assigned' => "{$performer} assigned permission '{$event->entityName}' to {$event->entityType}",
            'revoked' => "{$performer} revoked permission '{$event->entityName}' from {$event->entityType}",
            'created' => "{$performer} created new permission '{$event->entityName}'",
            'deleted' => "{$performer} deleted permission '{$event->entityName}'",
            'synced' => "{$performer} synchronized permissions for {$event->entityType}",
            default => "{$performer} performed '{$event->action}' on permission '{$event->entityName}'",
        };
    }
}
