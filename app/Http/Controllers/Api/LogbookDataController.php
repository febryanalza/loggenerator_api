<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogbookDataRequest;
use App\Http\Resources\LogbookDataResource;
use App\Models\LogbookData;
use App\Models\LogbookTemplate;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogbookDataController extends Controller
{
    /**
     * Store a newly created logbook entry in storage.
     *
     * @param  \App\Http\Requests\StoreLogbookDataRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreLogbookDataRequest $request)
    {
        try {
            // Get the template
            $template = LogbookTemplate::with('fields')->findOrFail($request->template_id);
            
            // Verify all required fields are present
            $templateFields = $template->fields->pluck('name')->toArray();
            $providedFields = array_keys($request->data);
            
            $missingFields = array_diff($templateFields, $providedFields);
            if (count($missingFields) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields',
                    'missing_fields' => $missingFields
                ], 422);
            }
            
            // Handle image uploads if any
            $data = $this->processImageUploads($request->data, $template);
            
            // Create the logbook data entry
            $logbookData = new LogbookData();
            $logbookData->template_id = $request->template_id;
            $logbookData->data = $data;
            $logbookData->save();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_LOGBOOK_ENTRY',
                'description' => 'Created new logbook entry for ' . $template->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logbook entry created successfully',
                'data' => new LogbookDataResource($logbookData)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create logbook entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process any image uploads in the data.
     *
     * @param array $data
     * @param \App\Models\LogbookTemplate $template
     * @return array
     */
    private function processImageUploads(array $data, LogbookTemplate $template)
    {
        // Get image type fields
        $imageFields = $template->fields->filter(function ($field) {
            return json_decode($field->data_type) === 'gambar';
        })->pluck('name')->toArray();
        
        // Skip if no image fields
        if (empty($imageFields)) {
            return $data;
        }
        
        $processedData = $data;
        
        // Process each image field
        foreach ($imageFields as $fieldName) {
            // Skip if field not provided or not a valid base64 image
            if (!isset($data[$fieldName]) || !$this->isBase64Image($data[$fieldName])) {
                continue;
            }
            
            // Decode base64 image
            $base64Image = $data[$fieldName];
            $imageData = explode(',', $base64Image);
            $imageData = isset($imageData[1]) ? $imageData[1] : $imageData[0];
            
            // Generate a unique filename
            $filename = 'logbook_' . time() . '_' . uniqid() . '.jpg';
            
            // Store the image
            Storage::disk('public')->put('logbook_images/' . $filename, base64_decode($imageData));
            
            // Update the data with the image filename
            $processedData[$fieldName] = $filename;
        }
        
        return $processedData;
    }
    
    /**
     * Check if a string is a valid base64 image.
     *
     * @param string $string
     * @return bool
     */
    private function isBase64Image($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        // Check if it looks like a base64 data URI
        if (strpos($string, 'data:image') === 0) {
            return true;
        }
        
        // Check if it's a plain base64 string
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            return false;
        }
        
        // Additional validation could be done here
        return true;
    }
}