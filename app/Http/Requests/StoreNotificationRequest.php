<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow admins to create notifications for any user
        // Regular users can only create notifications for themselves
        if ($this->user()->hasRole('Admin')) {
            return true;
        }
        
        return $this->user_id === null || 
               $this->user_id == $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'is_read' => 'sometimes|boolean',
        ];
    }
}