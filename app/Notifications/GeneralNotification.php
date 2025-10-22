<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;
    protected $actionText;
    protected $actionUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $actionText = null, $actionUrl = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->actionText = $actionText;
        $this->actionUrl = $actionUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->line($this->message);
            
        if ($this->actionText && $this->actionUrl) {
            $mail->action($this->actionText, $this->actionUrl);
        }
        
        return $mail->line('Thank you for using our application!');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_text' => $this->actionText,
            'action_url' => $this->actionUrl,
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
