<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'message' => $this->message,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at,
            'user' => [
                'id' => $this->whenLoaded('user', function() {
                    return $this->user->id;
                }),
                'name' => $this->whenLoaded('user', function() {
                    return $this->user->name;
                }),
            ],
        ];
    }
}