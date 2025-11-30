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
                    case 'text':
                    case 'textarea':
                        $rules["data.{$fieldName}"] = 'sometimes|string';
                        break;
                    case 'number':
                        $rules["data.{$fieldName}"] = 'sometimes|numeric';
                        break;
                    case 'image':
                        $rules["data.{$fieldName}"] = 'sometimes|string'; // Assuming we store filenames
                        break;
                    case 'date':
                        $rules["data.{$fieldName}"] = 'sometimes|date_format:Y-m-d';
                        break;
                    case 'time':
                        $rules["data.{$fieldName}"] = 'sometimes|date_format:H:i';
                        break;
                    case 'datetime':
                        $rules["data.{$fieldName}"] = 'sometimes|date_format:Y-m-d H:i:s';
                        break;
                    case 'url':
                        $rules["data.{$fieldName}"] = 'sometimes|url';
                        break;
                    case 'phone':
                        $rules["data.{$fieldName}"] = 'sometimes|string';
                        break;
                    case 'currency':
                    case 'percentage':
                        $rules["data.{$fieldName}"] = 'sometimes|numeric';
                        break;
                    case 'location':
                        $rules["data.{$fieldName}"] = 'sometimes|string';
                        break;
                }
            }
        }
        
        return $rules;
    }
}