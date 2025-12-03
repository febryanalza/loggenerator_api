<?php

namespace App\Events;

use App\Models\User;
use App\Models\LogbookTemplate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupervisorAddedToTemplate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The supervisor user who was added.
     *
     * @var \App\Models\User
     */
    public User $supervisor;

    /**
     * The logbook template the supervisor was added to.
     *
     * @var \App\Models\LogbookTemplate
     */
    public LogbookTemplate $template;

    /**
     * Create a new event instance.
     *
     * @param User $supervisor
     * @param LogbookTemplate $template
     */
    public function __construct(User $supervisor, LogbookTemplate $template)
    {
        $this->supervisor = $supervisor;
        $this->template = $template;
    }
}
