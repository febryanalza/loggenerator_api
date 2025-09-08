<?php
namespace App\Http\Requests;

use App\Models\LogbookTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreLogbookDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'template_id' => 'required|exists:logbook_template,id',
            'data' => 'required|array',
        ];

        // Get the template to validate field data
        $template = LogbookTemplate::with('fields')->find($this->template_id);
        
        if ($template && $template->fields->count() > 0) {
            // Add validation rules for each field based on data_type
            foreach ($template->fields as $field) {
                $fieldName = $field->name;
                $dataType = json_decode($field->data_type);
                
                switch ($dataType) {
                    case 'teks':
                        $rules["data.{$fieldName}"] = 'sometimes|string';
                        break;
                    case 'angka':
                        $rules["data.{$fieldName}"] = 'sometimes|numeric';
                        break;
                    case 'gambar':
                        $rules["data.{$fieldName}"] = 'sometimes|string'; // Assuming we store filenames
                        break;
                    case 'tanggal':
                        $rules["data.{$fieldName}"] = 'sometimes|date_format:Y-m-d';
                        break;
                    case 'jam':
                        $rules["data.{$fieldName}"] = 'sometimes|date_format:H:i';
                        break;
                }
            }
        }
        
        return $rules;
    }
}