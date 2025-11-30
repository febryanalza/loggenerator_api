<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvailableDataType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AvailableDataTypeController extends Controller
{
    /**
     * Display a listing of all available data types.
     * All authenticated users can view.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = AvailableDataType::query();

            // Filter by active status if provided
            if ($request->has('is_active')) {
                $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_active', $isActive);
            }

            // Search by name
            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'ilike', '%' . $request->search . '%');
            }

            $dataTypes = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data types retrieved successfully',
                'data' => $dataTypes,
                'count' => $dataTypes->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of active data types only.
     * Used for frontend dropdowns and selection components.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activeList()
    {
        try {
            $dataTypes = AvailableDataType::active()
                ->select('id', 'name', 'description')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Active data types retrieved successfully',
                'data' => $dataTypes,
                'count' => $dataTypes->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active data types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified data type.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $dataType = AvailableDataType::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data type retrieved successfully',
                'data' => $dataType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data type not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store a newly created data type.
     * Only Super Admin and Admin can create.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:available_data_types,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $dataType = AvailableDataType::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_DATA_TYPE',
                'description' => 'Created new data type: ' . $dataType->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data type created successfully',
                'data' => $dataType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create data type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified data type.
     * Only Super Admin and Admin can update.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100|unique:available_data_types,name,' . $id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $dataType = AvailableDataType::findOrFail($id);
            $originalData = $dataType->toArray();

            // Partial update
            if ($request->has('name')) {
                $dataType->name = $request->name;
            }
            if ($request->has('description')) {
                $dataType->description = $request->description;
            }
            if ($request->has('is_active')) {
                $dataType->is_active = $request->is_active;
            }

            $dataType->save();

            // Create audit log
            $changes = [];
            if ($request->has('name') && $originalData['name'] !== $dataType->name) {
                $changes[] = "name: '{$originalData['name']}' → '{$dataType->name}'";
            }
            if ($request->has('description') && $originalData['description'] !== $dataType->description) {
                $changes[] = "description updated";
            }
            if ($request->has('is_active') && $originalData['is_active'] !== $dataType->is_active) {
                $changes[] = "is_active: " . ($dataType->is_active ? 'enabled' : 'disabled');
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_DATA_TYPE',
                'description' => 'Updated data type "' . $dataType->name . '"' .
                               (count($changes) > 0 ? ' (' . implode(', ', $changes) . ')' : ''),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data type updated successfully',
                'data' => $dataType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data type not found or failed to update',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Remove the specified data type.
     * Only Super Admin and Admin can delete.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $dataType = AvailableDataType::findOrFail($id);
            $name = $dataType->name;

            $dataType->delete();

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_DATA_TYPE',
                'description' => 'Deleted data type: ' . $name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data type deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data type not found or failed to delete',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Toggle the active status of a data type.
     * Only Super Admin and Admin can toggle.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Request $request, $id)
    {
        try {
            $dataType = AvailableDataType::findOrFail($id);
            $dataType->is_active = !$dataType->is_active;
            $dataType->save();

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'TOGGLE_DATA_TYPE_STATUS',
                'description' => 'Toggled data type "' . $dataType->name . '" status to ' . 
                               ($dataType->is_active ? 'active' : 'inactive'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data type status toggled successfully',
                'data' => $dataType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data type not found or failed to toggle status',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
