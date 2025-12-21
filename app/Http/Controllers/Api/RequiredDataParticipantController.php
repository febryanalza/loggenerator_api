<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RequiredDataParticipant;
use App\Models\Institution;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RequiredDataParticipantController extends Controller
{
    /**
     * Get all required data participants.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = RequiredDataParticipant::with('institution:id,name');

            // Filter by active status
            if ($request->has('is_active')) {
                $isActive = filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN);
                $query->where('is_active', $isActive);
            }

            // Filter by institution
            if ($request->has('institution_id')) {
                $query->forInstitution($request->get('institution_id'));
            }

            // Search by data name
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('data_name', 'LIKE', "%{$search}%");
            }

            // Order by created_at descending
            $query->latest();

            $requiredData = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Required data participants retrieved successfully',
                'data' => [
                    'required_data' => $requiredData->items(),
                    'pagination' => [
                        'current_page' => $requiredData->currentPage(),
                        'last_page' => $requiredData->lastPage(),
                        'per_page' => $requiredData->perPage(),
                        'total' => $requiredData->total()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve required data participants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve required data participants',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get required data participants by institution ID, ordered by created_at.
     *
     * @param  string  $institutionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByInstitution($institutionId)
    {
        try {
            // Check if institution exists
            $institution = Institution::find($institutionId);
            if (!$institution) {
                return response()->json([
                    'success' => false,
                    'message' => 'Institution not found'
                ], 404);
            }

            $requiredData = RequiredDataParticipant::forInstitution($institutionId)
                ->active()
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Required data participants retrieved successfully',
                'data' => [
                    'institution' => [
                        'id' => $institution->id,
                        'name' => $institution->name
                    ],
                    'required_data' => $requiredData,
                    'total' => $requiredData->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve required data by institution: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve required data by institution',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a single required data participant by ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $requiredData = RequiredDataParticipant::with('institution:id,name')->find($id);

            if (!$requiredData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required data participant not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Required data participant retrieved successfully',
                'data' => $requiredData
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve required data participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve required data participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create a new required data participant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'institution_id' => 'required|uuid|exists:institutions,id',
            'data_name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $requiredData = RequiredDataParticipant::create([
                'institution_id' => $request->institution_id,
                'data_name' => $request->data_name,
                'is_active' => $request->get('is_active', true),
            ]);

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_REQUIRED_DATA_PARTICIPANT',
                'description' => 'Created required data participant: ' . $request->data_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Required data participant created successfully',
                'data' => $requiredData->load('institution:id,name')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create required data participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create required data participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update an existing required data participant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'data_name' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $requiredData = RequiredDataParticipant::find($id);

            if (!$requiredData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required data participant not found'
                ], 404);
            }

            $requiredData->update($request->only(['data_name', 'is_active']));

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_REQUIRED_DATA_PARTICIPANT',
                'description' => 'Updated required data participant: ' . $requiredData->data_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Required data participant updated successfully',
                'data' => $requiredData->load('institution:id,name')
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update required data participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update required data participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Toggle the active status of a required data participant.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Request $request, $id)
    {
        try {
            $requiredData = RequiredDataParticipant::find($id);

            if (!$requiredData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required data participant not found'
                ], 404);
            }

            $requiredData->is_active = !$requiredData->is_active;
            $requiredData->save();

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'TOGGLE_REQUIRED_DATA_PARTICIPANT',
                'description' => 'Toggled required data participant: ' . $requiredData->data_name . ' to ' . ($requiredData->is_active ? 'active' : 'inactive'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Required data participant status toggled successfully',
                'data' => $requiredData->load('institution:id,name')
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to toggle required data participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle required data participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete a required data participant.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $requiredData = RequiredDataParticipant::find($id);

            if (!$requiredData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required data participant not found'
                ], 404);
            }

            $dataName = $requiredData->data_name;
            $requiredData->delete();

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_REQUIRED_DATA_PARTICIPANT',
                'description' => 'Deleted required data participant: ' . $dataName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Required data participant deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete required data participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete required data participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
