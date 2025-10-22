<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InstitutionController extends Controller
{
    /**
     * Display a listing of institutions (name and id only) - Public access for all authenticated users.
     * Used for frontend dropdowns and selection components.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $institutions = Institution::select('id', 'name')->orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Institutions retrieved successfully',
                'data' => $institutions,
                'count' => $institutions->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch institutions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of all institution details - Admin only.
     * Includes full information (id, name, description, timestamps).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllDetails()
    {
        try {
            $institutions = Institution::orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Institution details retrieved successfully',
                'data' => $institutions,
                'count' => $institutions->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch institution details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified institution.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $institution = Institution::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Institution retrieved successfully',
                'data' => $institution
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Institution not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store a newly created institution.
     * Only Super Admin, Admin, and Manager can create institutions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:institutions,name',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $institution = Institution::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_INSTITUTION',
                'description' => 'Created new institution: ' . $institution->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Institution created successfully',
                'data' => $institution
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create institution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified institution (partial update support).
     * Only Super Admin, Admin, and Manager can update institutions.
     * Supports partial updates - can update name only, description only, or both.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request data (both fields are optional for partial updates)
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:institutions,name,' . $id,
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $institution = Institution::findOrFail($id);
            $originalData = $institution->toArray();
            
            // Partial update - only update fields that are provided
            if ($request->has('name')) {
                $institution->name = $request->name;
            }
            
            if ($request->has('description')) {
                $institution->description = $request->description;
            }
            
            $institution->save();
            
            // Create audit log with changes
            $changes = [];
            if ($request->has('name') && $originalData['name'] !== $institution->name) {
                $changes[] = "name: '{$originalData['name']}' → '{$institution->name}'";
            }
            if ($request->has('description') && $originalData['description'] !== $institution->description) {
                $changes[] = "description updated";
            }
            
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_INSTITUTION',
                'description' => 'Updated institution "' . $institution->name . '"' . 
                               (count($changes) > 0 ? ' (' . implode(', ', $changes) . ')' : ''),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Institution updated successfully',
                'data' => $institution
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update institution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified institution from storage.
     * Only Super Admin, Admin, and Manager can delete institutions.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $institution = Institution::findOrFail($id);
            $institutionName = $institution->name;
            
            // Check if institution is being used by users or templates
            $usersCount = $institution->users()->count();
            $templatesCount = $institution->logbookTemplates()->count();
            
            if ($usersCount > 0 || $templatesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete institution. It is currently being used.',
                    'details' => [
                        'users_count' => $usersCount,
                        'templates_count' => $templatesCount
                    ]
                ], 400);
            }
            
            $institution->delete();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_INSTITUTION',
                'description' => 'Deleted institution "' . $institutionName . '"',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Institution deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete institution',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}