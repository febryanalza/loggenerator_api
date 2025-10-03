<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVerificationStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'template_id' => 'required|uuid|exists:logbook_template,id',
            'user_id' => 'required|uuid|exists:users,id',
            'has_been_verified' => 'required|boolean'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'template_id.required' => 'Template ID is required.',
            'template_id.uuid' => 'Template ID must be a valid UUID.',
            'template_id.exists' => 'Template not found.',
            'user_id.required' => 'User ID is required.',
            'user_id.uuid' => 'User ID must be a valid UUID.',
            'user_id.exists' => 'User not found.',
            'has_been_verified.required' => 'Verification status is required.',
            'has_been_verified.boolean' => 'Verification status must be true or false.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'template_id' => 'template ID',
            'user_id' => 'user ID',
            'has_been_verified' => 'verification status'
        ];
    }
}