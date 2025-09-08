<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogbookFieldRequest;
use App\Http\Resources\LogbookFieldResource;
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
            // Check if user has permission to modify the template
            $template = LogbookTemplate::findOrFail($request->template_id);
            
            // Only template owner or admin can add fields
            $user = Auth::user();
            $isAdmin = $user->hasRole('Admin');
            
            if (!$isAdmin && $template->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to modify this template'
                ], 403);
            }
            
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
        // Validate the request
        $validator = validator($request->all(), [
            'template_id' => 'required|exists:logbook_template,id',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:100',
            'fields.*.data_type' => 'required|in:"teks","angka","gambar","tanggal","jam"',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user has permission to modify the template
            $template = LogbookTemplate::findOrFail($request->template_id);
            
            // Only template owner or admin can add fields
            $user = Auth::user();
            $isAdmin = $user->hasRole('Admin');
            
            if (!$isAdmin && $template->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to modify this template'
                ], 403);
            }
            
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
}