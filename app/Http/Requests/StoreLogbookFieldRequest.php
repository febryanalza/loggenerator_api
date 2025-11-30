<?php

namespace App\Http\Requests;

use App\Models\AvailableDataType;
use Illuminate\Foundation\Http\FormRequest;

class StoreLogbookFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Get active data types from database
        $validDataTypes = AvailableDataType::active()->pluck('name')->toArray();
        $validDataTypesString = implode(',', $validDataTypes);

        return [
            'name' => 'required|string|max:100',
            'data_type' => 'required|string|in:' . $validDataTypesString,
            'template_id' => 'required|exists:logbook_template,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        // Get active data types for error message
        $validDataTypes = AvailableDataType::active()->pluck('name')->toArray();
        
        return [
            'data_type.in' => 'Tipe data tidak valid. Tipe yang tersedia: ' . implode(', ', $validDataTypes),
        ];
    }
}