<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogbookDataSimpleResource extends JsonResource
{
    /**
     * Transform the resource into a simplified array for template-specific queries.
     * This resource excludes redundant template information since we already know the template context.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get verification details
        $verificationDetails = $this->getVerificationDetails();

        return [
            'id' => $this->id,
            'writer' => $this->whenLoaded('writer', function () {
                return [
                    'id' => $this->writer->id,
                    'name' => $this->writer->name,
                ];
            }),
            'data' => $this->data,
            // is_verified: true if ALL supervisors approved OR no supervisors assigned
            'is_verified' => $this->isVerified(),
            // Verification details showing all verifications
            'verification_details' => $verificationDetails,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}