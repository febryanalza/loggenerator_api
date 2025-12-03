<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogbookDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get verification details
        $verificationDetails = $this->getVerificationDetails();

        return [
            'id' => $this->id,
            'template_id' => $this->template_id,
            'writer_id' => $this->writer_id,
            'template' => [
                'id' => $this->template->id,
                'name' => $this->template->name,
                'description' => $this->template->description,
            ],
            'writer' => $this->whenLoaded('writer', function () {
                return [
                    'id' => $this->writer->id,
                    'name' => $this->writer->name,
                    'email' => $this->writer->email,
                ];
            }),
            'data' => $this->data,
            // is_verified: true only if ALL supervisors have approved (AND logic)
            'is_verified' => $this->isVerified(),
            // Verification details showing all verifications
            'verification_details' => $verificationDetails,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}