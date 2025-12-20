<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent
{
    use Dispatchable, SerializesModels;

    /**
     * User ID or array of user IDs to send notification to
     */
    public string|array $userId;

    /**
     * Notification title
     */
    public string $title;

    /**
     * Notification body
     */
    public string $body;

    /**
     * Additional data payload
     */
    public array $data;

    /**
     * Notification type (e.g., 'logbook_approval', 'comment_reply', etc.)
     */
    public ?string $type;

    /**
     * Create a new event instance.
     *
     * @param string|array $userId User ID(s) to send notification to
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param string|null $type Notification type
     */
    public function __construct(
        string|array $userId,
        string $title,
        string $body,
        array $data = [],
        ?string $type = null
    ) {
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->type = $type;
    }
}
