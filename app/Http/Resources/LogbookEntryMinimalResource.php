<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogbookEntryMinimalResource extends JsonResource
{
    /**
     * Transform the resource into a minimal array for listing purposes.
     * Only includes essential fields for listing/browsing entries.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $supervisorCount = $this->getSupervisorCount();
        $verifiedCount = $this->getVerifiedCount();

        return [
            'id' => $this->id,
            'writer_name' => $this->whenLoaded('writer', fn() => $this->writer->name),
            // is_verified: true only if ALL supervisors have approved (AND logic)
            'is_verified' => $this->isVerified(),
            // Verification progress
            'verification_progress' => [
                'approved' => $verifiedCount,
                'total_supervisors' => $supervisorCount,
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            // Only include a preview of data, not full content
            'data_preview' => $this->getDataPreview(),
        ];
    }

    /**
     * Get a preview/summary of the entry data
     */
    private function getDataPreview(): array
    {
        if (!is_array($this->data)) {
            return [];
        }

        $preview = [];
        $count = 0;
        $maxFields = 3; // Only show first 3 fields

        foreach ($this->data as $key => $value) {
            if ($count >= $maxFields) {
                $preview['...'] = 'dan ' . (count($this->data) - $maxFields) . ' field lainnya';
                break;
            }

            // Truncate long values
            if (is_string($value) && strlen($value) > 50) {
                $preview[$key] = substr($value, 0, 47) . '...';
            } else {
                $preview[$key] = $value;
            }
            $count++;
        }

        return $preview;
    }
}