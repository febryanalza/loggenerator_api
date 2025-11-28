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
            'verifier' => $this->whenLoaded('verifier', function () {
                return [
                    'id' => $this->verifier->id,
                    'name' => $this->verifier->name,
                    'email' => $this->verifier->email,
                ];
            }),
            'data' => $this->data,
            'is_verified' => $this->is_verified,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at,
            'verification_notes' => $this->verification_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}