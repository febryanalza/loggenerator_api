<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogbookDataRequest;
use App\Http\Resources\LogbookDataResource;
use App\Http\Resources\LogbookDataSimpleResource;
use App\Http\Resources\LogbookEntryMinimalResource;
use App\Models\LogbookData;
use App\Models\LogbookTemplate;
use App\Models\AuditLog;
use App\Models\UserLogbookAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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
            $logbookData->writer_id = Auth::id();
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
            
            // Update the data with the image URL
            $processedData[$fieldName] = url('/api/images/logbook/' . $filename);
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

    /**
     * Display a listing of logbook entries.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = LogbookData::with(['template', 'writer', 'verifier']);
            
            // Filter by template if provided
            if ($request->has('template_id')) {
                $query->where('template_id', $request->template_id);
            }
            
            // Filter by writer if provided
            if ($request->has('writer_id')) {
                $query->where('writer_id', $request->writer_id);
            }
            
            // Filter by current user entries if requested
            if ($request->has('my_entries') && $request->my_entries == true) {
                $query->where('writer_id', Auth::id());
            }
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $logbookEntries = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => LogbookDataResource::collection($logbookEntries->items()),
                'pagination' => [
                    'current_page' => $logbookEntries->currentPage(),
                    'total_pages' => $logbookEntries->lastPage(),
                    'per_page' => $logbookEntries->perPage(),
                    'total' => $logbookEntries->total(),
                    'has_more' => $logbookEntries->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch logbook entries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified logbook entry.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $logbookData = LogbookData::with(['template.fields', 'writer', 'verifier'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => new LogbookDataResource($logbookData)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logbook entry not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Fetch logbook entries by template ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchByTemplate(Request $request, $templateId)
    {
        try {
            // Verify template exists and get minimal template info
            $template = LogbookTemplate::select('id', 'name', 'description')->findOrFail($templateId);
            
            // Check if user wants minimal response (for listing/browsing)
            $minimal = $request->get('minimal', false);
            
            // Build optimized query
            $query = LogbookData::select('id', 'writer_id', 'verified_by', 'data', 'is_verified', 'verified_at', 'verification_notes', 'created_at', 'updated_at')
                ->with(['writer:id,name,email', 'verifier:id,name,email'])
                ->where('template_id', $templateId);
            
            // Filter by writer if provided
            if ($request->has('writer_id')) {
                $query->where('writer_id', $request->writer_id);
            }
            
            // Filter by current user entries if requested
            if ($request->has('my_entries') && $request->my_entries == true) {
                $query->where('writer_id', Auth::id());
            }
            
            // Date range filter
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['created_at', 'updated_at']) && in_array($sortOrder, ['asc', 'desc'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $logbookEntries = $query->paginate($perPage);
            
            // Choose appropriate resource based on minimal flag
            $entriesData = $minimal 
                ? LogbookEntryMinimalResource::collection($logbookEntries->items())
                : LogbookDataSimpleResource::collection($logbookEntries->items());

            return response()->json([
                'success' => true,
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description
                ],
                'entries' => $entriesData,
                'pagination' => [
                    'current_page' => $logbookEntries->currentPage(),
                    'total_pages' => $logbookEntries->lastPage(),
                    'per_page' => $logbookEntries->perPage(),
                    'total' => $logbookEntries->total(),
                    'has_more' => $logbookEntries->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found',
                    'error' => 'The specified template does not exist'
                ], 404);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch logbook entries for template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get template summary with statistics.
     *
     * @param  string  $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplateSummary($templateId)
    {
        try {
            $template = LogbookTemplate::select('id', 'name', 'description', 'created_at')
                ->withCount('data')
                ->findOrFail($templateId);

            // Get basic statistics
            $stats = [
                'total_entries' => $template->data_count,
                'total_writers' => LogbookData::where('template_id', $templateId)
                    ->distinct('writer_id')
                    ->count('writer_id'),
                'latest_entry' => LogbookData::where('template_id', $templateId)
                    ->latest()
                    ->value('created_at'),
                'oldest_entry' => LogbookData::where('template_id', $templateId)
                    ->oldest()
                    ->value('created_at'),
            ];

            // Get top writers
            $topWriters = LogbookData::select('writer_id')
                ->selectRaw('COUNT(*) as entry_count')
                ->with(['writer:id,name'])
                ->where('template_id', $templateId)
                ->groupBy('writer_id')
                ->orderByDesc('entry_count')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'writer_name' => $item->writer->name ?? 'Unknown',
                        'entry_count' => $item->entry_count
                    ];
                });

            return response()->json([
                'success' => true,
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'created_at' => $template->created_at
                ],
                'statistics' => $stats,
                'top_writers' => $topWriters
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Template not found', [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Template not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch template summary', [
                'template_id' => $templateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch template summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified logbook entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'data' => 'required|array',
        ]);

        try {
            $logbookData = LogbookData::with(['template.fields', 'writer', 'verifier'])->findOrFail($id);
            
            // Check if user can update this entry (Owner or Editor only)
            $user = Auth::user();
            
            // Check if user has Owner or Editor role for this template
            $userAccess = UserLogbookAccess::where('user_id', $user->id)
                ->where('logbook_template_id', $logbookData->template_id)
                ->with('logbookRole')
                ->first();
            
            $canUpdate = false;
            
            // Allow only if user has Owner or Editor role for this template
            if ($userAccess && in_array($userAccess->logbookRole->name, ['Owner', 'Editor'])) {
                $canUpdate = true;
            }
            
            if (!$canUpdate) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this entry. Only users with Owner or Editor role can update logbook entries.'
                ], 403);
            }
            
            // Validate that provided fields exist in template (optional validation)
            $templateFields = $logbookData->template->fields->pluck('name')->toArray();
            $providedFields = array_keys($request->data);
            
            // Check if any provided field is not in template (optional security check)
            $invalidFields = array_diff($providedFields, $templateFields);
            if (count($invalidFields) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid fields provided',
                    'invalid_fields' => $invalidFields
                ], 422);
            }
            
            // Handle image uploads if any for the provided fields
            $newData = $this->processImageUploads($request->data, $logbookData->template);
            
            // Merge new data with existing data (partial update)
            $existingData = $logbookData->data ?? [];
            $mergedData = array_merge($existingData, $newData);
            
            // Update the logbook data
            $logbookData->data = $mergedData;
            $logbookData->save();
            
            // Determine update context for audit log
            $updateContext = '';
            if ($userAccess && $userAccess->logbookRole->name === 'Owner') {
                $updateContext = ' (as Owner)';
            } elseif ($userAccess && $userAccess->logbookRole->name === 'Editor') {
                $updateContext = ' (as Editor)';
            }
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_LOGBOOK_ENTRY',
                'description' => "Updated logbook entry for {$logbookData->template->name}{$updateContext}. Original writer: {$logbookData->writer->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logbook entry updated successfully',
                'data' => new LogbookDataResource($logbookData)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update logbook entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified logbook entry from storage.
     * Authorization: Editor, Supervisor, Owner roles can delete entries
     * Administrative override: Super Admin, Admin, Manager, Institution Admin
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $logbookData = LogbookData::with(['template', 'writer', 'verifier'])->findOrFail($id);
            $user = Auth::user();
            
            // Check if user has administrative roles that can override
            if ($this->hasAdministrativeOverride($user)) {
                // Admin users can delete any entry
            } else {
                // For regular users, check if they have Editor+ role for this template
                $userAccess = UserLogbookAccess::where('user_id', $user->id)
                    ->where('logbook_template_id', $logbookData->template_id)
                    ->with('logbookRole')
                    ->first();
                
                if (!$userAccess || !in_array($userAccess->logbookRole->name, ['Editor', 'Supervisor', 'Owner'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to delete entries for this template. Required: Editor, Supervisor, or Owner role.',
                        'required_access' => 'Editor, Supervisor, or Owner role for template: ' . $logbookData->template->name
                    ], 403);
                }
            }
            
            $templateName = $logbookData->template->name;
            
            // Delete associated images if any
            $this->deleteImageFiles($logbookData);
            
            // Delete the entry
            $logbookData->delete();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_LOGBOOK_ENTRY',
                'description' => 'Deleted logbook entry for ' . $templateName,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logbook entry deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete logbook entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete image files associated with a logbook entry.
     *
     * @param  \App\Models\LogbookData  $logbookData
     * @return void
     */
    private function deleteImageFiles(LogbookData $logbookData)
    {
        try {
            // Get image type fields
            $imageFields = $logbookData->template->fields->filter(function ($field) {
                return json_decode($field->data_type) === 'gambar';
            })->pluck('name')->toArray();
            
            if (empty($imageFields)) {
                return;
            }
            
            // Delete image files
            foreach ($imageFields as $fieldName) {
                if (isset($logbookData->data[$fieldName])) {
                    $imageUrl = $logbookData->data[$fieldName];
                    
                    // Extract filename from URL
                    if (strpos($imageUrl, '/api/images/logbook/') !== false) {
                        $filename = basename($imageUrl);
                        $path = 'logbook_images/' . $filename;
                        
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the deletion
            Log::error('Failed to delete image files: ' . $e->getMessage());
        }
    }

    /**
     * Check if user has administrative roles that can override logbook permissions
     *
     * @param  User  $user
     * @return bool
     */
    private function hasAdministrativeOverride($user): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('roles.name', ['Super Admin', 'Admin', 'Manager', 'Institution Admin'])
            ->exists();
    }
}