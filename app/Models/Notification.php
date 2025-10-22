<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'data' => 'array',
        'read_at' => 'datetime',
    ];
    
    /**
     * Get the notification title from data.
     */
    public function getTitleAttribute()
    {
        return $this->data['title'] ?? '';
    }
    
    /**
     * Get the notification message from data.
     */
    public function getMessageAttribute()
    {
        return $this->data['message'] ?? '';
    }
    
    /**
     * Check if the notification has been read.
     */
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }
    
    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
    
    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
    
    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }
    
    /**
     * Mark the notification as unread.
     */
    public function markAsUnread()
    {
        if (!is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }
}