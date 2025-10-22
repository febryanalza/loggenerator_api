<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\LogbookTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LogbookAccessNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected User $grantedBy;
    protected LogbookTemplate $logbookTemplate;
    protected string $role;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $grantedBy, LogbookTemplate $logbookTemplate, string $role)
    {
        $this->grantedBy = $grantedBy;
        $this->logbookTemplate = $logbookTemplate;
        $this->role = $role;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Hak Akses Template')
            ->line("Anda telah diberikan hak akses oleh {$this->grantedBy->email} untuk template {$this->logbookTemplate->name} sebagai {$this->role}")
            ->action('Lihat Template', url('/logbook/templates/' . $this->logbookTemplate->id))
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'logbook_access_granted',
            'title' => 'Hak Akses Template',
            'message' => "Anda telah diberikan hak akses oleh {$this->grantedBy->email} untuk template {$this->logbookTemplate->name} sebagai {$this->role}",
            'granted_by' => [
                'id' => $this->grantedBy->id,
                'name' => $this->grantedBy->name,
                'email' => $this->grantedBy->email,
            ],
            'logbook_template' => [
                'id' => $this->logbookTemplate->id,
                'title' => $this->logbookTemplate->name,
            ],
            'role' => $this->role,
            'action_url' => '/logbook/templates/' . $this->logbookTemplate->id,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Hak Akses Template',
            'message' => "Anda telah diberikan hak akses oleh {$this->grantedBy->email} untuk template {$this->logbookTemplate->name} sebagai {$this->role}",
            'granted_by' => [
                'id' => $this->grantedBy->id,
                'name' => $this->grantedBy->name,
                'email' => $this->grantedBy->email,
            ],
            'logbook_template' => [
                'id' => $this->logbookTemplate->id,
                'name' => $this->logbookTemplate->name,
            ],
            'role' => $this->role,
            'action_text' => 'Lihat Template',
            'action_url' => '/logbook/templates/' . $this->logbookTemplate->id,
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
