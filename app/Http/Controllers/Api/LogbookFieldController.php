<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogbookFieldRequest;
use App\Http\Resources\LogbookFieldResource;
use App\Models\AvailableDataType;
use App\Models\LogbookField;
use App\Models\LogbookTemplate;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookFieldController extends Controller
{
    /**
     * Store a newly created field in storage.
     *
     * @param  \App\Http\Requests\StoreLogbookFieldRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreLogbookFieldRequest $request)
    {
        try {
            // Check if template exists
            $template = LogbookTemplate::findOrFail($request->template_id);
            
            // Create the field
            $field = new LogbookField();
            $field->name = $request->name;
            $field->data_type = $request->data_type;
            $field->template_id = $request->template_id;
            $field->save();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_FIELD',
                'description' => 'Added field "' . $field->name . '" to template "' . $template->name . '"',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Field created successfully',
                'data' => new LogbookFieldResource($field)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create field',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store multiple fields at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBatch(Request $request)
    {
        // Get active data types from database
        $validDataTypes = AvailableDataType::active()->pluck('name')->toArray();
        $validDataTypesString = implode(',', $validDataTypes);

        // Validate the request
        $validator = validator($request->all(), [
            'template_id' => 'required|exists:logbook_template,id',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:100',
            'fields.*.data_type' => 'required|string|in:' . $validDataTypesString,
        ], [
            'fields.*.data_type.in' => 'Tipe data tidak valid. Tipe yang tersedia: ' . implode(', ', $validDataTypes),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if template exists
            $template = LogbookTemplate::findOrFail($request->template_id);
            
            // Create fields
            $createdFields = [];
            foreach ($request->fields as $fieldData) {
                $field = new LogbookField();
                $field->name = $fieldData['name'];
                $field->data_type = $fieldData['data_type'];
                $field->template_id = $request->template_id;
                $field->save();
                
                $createdFields[] = $field;
            }
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_FIELDS_BATCH',
                'description' => 'Added ' . count($createdFields) . ' fields to template "' . $template->name . '"',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => count($createdFields) . ' fields created successfully',
                'data' => LogbookFieldResource::collection($createdFields)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fields for a specific template.
     *
     * @param  string  $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFieldsByTemplate($templateId)
    {
        try {
            $template = LogbookTemplate::with('fields')->findOrFail($templateId);
            
            return response()->json([
                'success' => true,
                'data' => LogbookFieldResource::collection($template->fields)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified field.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Get active data types from database
        $validDataTypes = AvailableDataType::active()->pluck('name')->toArray();
        $validDataTypesString = implode(',', $validDataTypes);

        // Validate the request
        $validator = validator($request->all(), [
            'name' => 'required|string|max:100',
            'data_type' => 'required|string|in:' . $validDataTypesString,
        ], [
            'data_type.in' => 'Tipe data tidak valid. Tipe yang tersedia: ' . implode(', ', $validDataTypes),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $field = LogbookField::findOrFail($id);
            
            $field->name = $request->name;
            $field->data_type = $request->data_type;
            $field->save();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_FIELD',
                'description' => 'Updated field "' . $field->name . '"',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully',
                'data' => new LogbookFieldResource($field)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified field.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $field = LogbookField::findOrFail($id);
            $fieldName = $field->name;
            
            $field->delete();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_FIELD',
                'description' => 'Deleted field "' . $fieldName . '"',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Field deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}