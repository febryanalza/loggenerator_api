<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action,
        public string $entityType,
        public string $entityName,
        public ?int $userId = null,
        public ?string $performedBy = null,
        public ?array $metadata = []
    ) {}
}
