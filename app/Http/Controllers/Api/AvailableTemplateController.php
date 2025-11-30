<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvailableTemplate;
use App\Models\AvailableDataType;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AvailableTemplateController extends Controller
{
    /**
     * Check if user has a specific role.
     *
     * @param User $user
     * @param string $roleName
     * @return bool
     */
    private function userHasRole(User $user, string $roleName): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', User::class)
            ->where('roles.name', $roleName)
            ->exists();
    }

    /**
     * Display a listing of all available templates.
     * All authenticated users can view.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = AvailableTemplate::with(['institution:id,name', 'creator:id,name,email']);

            // Filter by active status if provided
            if ($request->has('is_active')) {
                $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_active', $isActive);
            }

            // Filter by institution
            if ($request->has('institution_id') && !empty($request->institution_id)) {
                $query->where('institution_id', $request->institution_id);
            }

            // Search by name
            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'ilike', '%' . $request->search . '%');
            }

            $templates = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Templates retrieved successfully',
                'data' => $templates,
                'count' => $templates->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of active templates only.
     * Used for frontend dropdowns and selection components.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activeList(Request $request)
    {
        try {
            $query = AvailableTemplate::active()
                ->with(['institution:id,name'])
                ->select('id', 'name', 'description', 'institution_id', 'required_columns');

            // Filter by institution
            if ($request->has('institution_id') && !empty($request->institution_id)) {
                $query->where('institution_id', $request->institution_id);
            }

            $templates = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Active templates retrieved successfully',
                'data' => $templates,
                'count' => $templates->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified template.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $template = AvailableTemplate::with(['institution:id,name', 'creator:id,name,email'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Template retrieved successfully',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store a newly created template.
     * Super Admin, Admin, Manager, and Institution Admin can create.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Get available data types for validation
        $validDataTypes = AvailableDataType::active()->pluck('name')->toArray();
        $validDataTypesString = implode(',', $validDataTypes);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'institution_id' => 'required|uuid|exists:institutions,id',
            'required_columns' => 'required|array|min:1',
            'required_columns.*.name' => 'required|string|max:255',
            'required_columns.*.data_type' => 'required|string|in:' . $validDataTypesString,
            'required_columns.*.description' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ], [
            'required_columns.*.data_type.in' => 'The selected data type is invalid. Valid types are: ' . $validDataTypesString,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is Institution Admin, they can only create for their own institution
        if ($this->userHasRole($user, 'Institution Admin')) {
            if ($user->institution_id !== $request->institution_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create templates for your own institution'
                ], 403);
            }
        }

        try {
            $template = AvailableTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'institution_id' => $request->institution_id,
                'created_by' => $user->id,
                'required_columns' => $request->required_columns,
                'is_active' => $request->is_active ?? true,
            ]);

            // Load relationships
            $template->load(['institution:id,name', 'creator:id,name,email']);

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'CREATE_AVAILABLE_TEMPLATE',
                'description' => 'Created new available template: ' . $template->name . 
                               ' for institution: ' . $template->institution->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified template.
     * Super Admin, Admin, Manager, and Institution Admin can update.
     * Institution Admin can only update templates from their own institution.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Get available data types for validation
        $validDataTypes = AvailableDataType::active()->pluck('name')->toArray();
        $validDataTypesString = implode(',', $validDataTypes);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'institution_id' => 'sometimes|required|uuid|exists:institutions,id',
            'required_columns' => 'sometimes|required|array|min:1',
            'required_columns.*.name' => 'required_with:required_columns|string|max:255',
            'required_columns.*.data_type' => 'required_with:required_columns|string|in:' . $validDataTypesString,
            'required_columns.*.description' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ], [
            'required_columns.*.data_type.in' => 'The selected data type is invalid. Valid types are: ' . $validDataTypesString,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $template = AvailableTemplate::findOrFail($id);

            // Check if user is Institution Admin, they can only update their own institution's templates
            if ($this->userHasRole($user, 'Institution Admin')) {
                if ($template->institution_id !== $user->institution_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only update templates from your own institution'
                    ], 403);
                }
                // Also prevent changing the institution_id
                if ($request->has('institution_id') && $request->institution_id !== $user->institution_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot change the institution of a template'
                    ], 403);
                }
            }

            $originalData = $template->toArray();

            // Partial update
            if ($request->has('name')) {
                $template->name = $request->name;
            }
            if ($request->has('description')) {
                $template->description = $request->description;
            }
            if ($request->has('institution_id')) {
                $template->institution_id = $request->institution_id;
            }
            if ($request->has('required_columns')) {
                $template->required_columns = $request->required_columns;
            }
            if ($request->has('is_active')) {
                $template->is_active = $request->is_active;
            }

            $template->save();
            $template->load(['institution:id,name', 'creator:id,name,email']);

            // Create audit log
            $changes = [];
            if ($request->has('name') && $originalData['name'] !== $template->name) {
                $changes[] = "name: '{$originalData['name']}' → '{$template->name}'";
            }
            if ($request->has('description') && $originalData['description'] !== $template->description) {
                $changes[] = "description updated";
            }
            if ($request->has('required_columns')) {
                $changes[] = "required_columns updated";
            }
            if ($request->has('is_active') && $originalData['is_active'] !== $template->is_active) {
                $changes[] = "is_active: " . ($template->is_active ? 'enabled' : 'disabled');
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'UPDATE_AVAILABLE_TEMPLATE',
                'description' => 'Updated available template "' . $template->name . '"' .
                               (count($changes) > 0 ? ' (' . implode(', ', $changes) . ')' : ''),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or failed to update',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Remove the specified template.
     * Super Admin, Admin, Manager can delete any template.
     * Institution Admin can only delete templates from their own institution.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        try {
            $template = AvailableTemplate::with('institution:id,name')->findOrFail($id);

            // Check if user is Institution Admin, they can only delete their own institution's templates
            if ($this->userHasRole($user, 'Institution Admin')) {
                if ($template->institution_id !== $user->institution_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only delete templates from your own institution'
                    ], 403);
                }
            }

            $name = $template->name;
            $institutionName = $template->institution->name ?? 'Unknown';

            $template->delete();

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'DELETE_AVAILABLE_TEMPLATE',
                'description' => 'Deleted available template: ' . $name . 
                               ' from institution: ' . $institutionName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or failed to delete',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Toggle the active status of a template.
     * Super Admin, Admin, Manager, and Institution Admin can toggle.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Request $request, $id)
    {
        $user = Auth::user();

        try {
            $template = AvailableTemplate::with('institution:id,name')->findOrFail($id);

            // Check if user is Institution Admin
            if ($this->userHasRole($user, 'Institution Admin')) {
                if ($template->institution_id !== $user->institution_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only toggle templates from your own institution'
                    ], 403);
                }
            }

            $template->is_active = !$template->is_active;
            $template->save();
            $template->load(['institution:id,name', 'creator:id,name,email']);

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'TOGGLE_AVAILABLE_TEMPLATE_STATUS',
                'description' => 'Toggled available template "' . $template->name . '" status to ' . 
                               ($template->is_active ? 'active' : 'inactive'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template status toggled successfully',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or failed to toggle status',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get active templates by institution.
     * All authenticated users can view.
     *
     * @param string $institutionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function byInstitution($institutionId)
    {
        try {
            $templates = AvailableTemplate::with(['creator:id,name,email'])
                ->where('institution_id', $institutionId)
                ->active()
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Templates retrieved successfully',
                'data' => $templates,
                'count' => $templates->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all templates (active and inactive) by institution.
     * Used by Institution Admin for template management.
     *
     * @param string $institutionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function allByInstitution($institutionId)
    {
        try {
            $templates = AvailableTemplate::with(['creator:id,name,email'])
                ->where('institution_id', $institutionId)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'All templates retrieved successfully',
                'data' => $templates,
                'count' => $templates->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
