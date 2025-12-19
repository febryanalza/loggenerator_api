<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string|int $userId,
        public string $userName,
        public string $roleName,
        public string $performedBy,
        public ?array $metadata = []
    ) {}
}
