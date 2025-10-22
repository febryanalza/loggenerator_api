<?php

namespace App\Events;

use App\Models\User;
use App\Models\LogbookTemplate;
use App\Models\UserLogbookAccess;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogbookAccessGranted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $grantedUser;
    public User $grantedBy;
    public LogbookTemplate $logbookTemplate;
    public string $role;

    /**
     * Create a new event instance.
     */
    public function __construct(User $grantedUser, User $grantedBy, LogbookTemplate $logbookTemplate, string $role)
    {
        $this->grantedUser = $grantedUser;
        $this->grantedBy = $grantedBy;
        $this->logbookTemplate = $logbookTemplate;
        $this->role = $role;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->grantedUser->id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'type' => 'logbook_access_granted',
            'title' => 'Hak Akses Template',
            'message' => "Anda telah diberikan hak akses oleh {$this->grantedBy->email} untuk template {$this->logbookTemplate->title} sebagai {$this->role}",
            'granted_by' => [
                'id' => $this->grantedBy->id,
                'name' => $this->grantedBy->name,
                'email' => $this->grantedBy->email,
            ],
            'logbook_template' => [
                'id' => $this->logbookTemplate->id,
                'title' => $this->logbookTemplate->title,
            ],
            'role' => $this->role,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'logbook.access.granted';
    }
}
