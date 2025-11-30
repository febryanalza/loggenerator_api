<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InstitutionController extends Controller
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
            $institutions = Institution::withCount(['logbookTemplates as templates_count', 'users as users_count'])
                ->orderBy('name')
                ->get();
            
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
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'company_type' => 'nullable|string|max:100',
            'company_email' => 'nullable|email|max:150',
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
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'company_type' => $request->company_type,
                'company_email' => $request->company_email,
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
     * Supports partial updates - can update any field individually or multiple fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request data (all fields are optional for partial updates)
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:institutions,name,' . $id,
            'description' => 'nullable|string|max:1000',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'company_type' => 'nullable|string|max:100',
            'company_email' => 'nullable|email|max:150',
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
            
            // List of updatable fields
            $updatableFields = ['name', 'description', 'phone_number', 'address', 'company_type', 'company_email'];
            
            // Partial update - only update fields that are provided
            foreach ($updatableFields as $field) {
                if ($request->has($field)) {
                    $institution->$field = $request->$field;
                }
            }
            
            $institution->save();
            
            // Create audit log with changes
            $changes = [];
            $updatableFieldLabels = [
                'name' => 'name',
                'description' => 'description',
                'phone_number' => 'phone number',
                'address' => 'address',
                'company_type' => 'company type',
                'company_email' => 'company email',
            ];
            
            foreach ($updatableFields as $field) {
                if ($request->has($field) && $originalData[$field] !== $institution->$field) {
                    if ($field === 'name') {
                        $changes[] = "{$updatableFieldLabels[$field]}: '{$originalData[$field]}' → '{$institution->$field}'";
                    } else {
                        $changes[] = "{$updatableFieldLabels[$field]} updated";
                    }
                }
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

    /**
     * Get all logbook templates affiliated with a specific institution.
     * Only Super Admin, Admin, and Manager can view templates by institution.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplatesByInstitution($id)
    {
        try {
            $institution = Institution::findOrFail($id);
            
            // Get templates with owner information and stats
            $templates = $institution->logbookTemplates()
                ->with(['owner:id,name,email'])
                ->withCount(['data', 'userAccess'])
                ->orderBy('name')
                ->get()
                ->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'owner' => $template->owner ? [
                            'id' => $template->owner->id,
                            'name' => $template->owner->name,
                            'email' => $template->owner->email
                        ] : null,
                        'entries_count' => $template->data_count,
                        'users_count' => $template->user_access_count,
                        'created_at' => $template->created_at,
                        'updated_at' => $template->updated_at,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'message' => 'Templates retrieved successfully',
                'data' => [
                    'institution' => [
                        'id' => $institution->id,
                        'name' => $institution->name,
                        'description' => $institution->description,
                    ],
                    'templates' => $templates,
                    'total_templates' => $templates->count(),
                ]
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
     * Get all users (members) belonging to a specific institution.
     * Accessible by Institution Admin for their own institution.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembersByInstitution($id, Request $request)
    {
        try {
            /** @var User $currentUser */
            $currentUser = Auth::user();
            
            // Institution Admin can only view members of their own institution
            if ($this->userHasRole($currentUser, 'Institution Admin')) {
                if ($currentUser->institution_id !== $id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only view members of your own institution'
                    ], 403);
                }
            }
            
            $institution = Institution::findOrFail($id);
            
            // Build query for users
            $query = $institution->users()->with('roles');
            
            // Search by name or email
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', '%' . $search . '%')
                      ->orWhere('email', 'ilike', '%' . $search . '%');
                });
            }
            
            // Filter by role
            if ($request->has('role') && !empty($request->role)) {
                $query->role($request->role);
            }
            
            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
            
            $users = $query->orderBy('name')->get();
            
            $userData = $users->map(function ($user) {
                // Get roles as array (consistent with UserManagementController)
                $rolesArray = $user->roles->pluck('name')->toArray();
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'status' => $user->status ?? 'active',
                    'roles' => $rolesArray,
                    'role' => $rolesArray[0] ?? 'User', // Primary role for display
                    'last_login' => $user->last_login,
                    'created_at' => $user->created_at,
                ];
            });
            
            // Calculate stats
            $stats = [
                'total' => $users->count(),
                'active' => $users->where('status', 'active')->count(),
                'inactive' => $users->where('status', '!=', 'active')->count(),
                'admins' => $users->filter(function($u) { 
                    return $u->roles->contains('name', 'Institution Admin'); 
                })->count(),
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Institution members retrieved successfully',
                'data' => [
                    'institution' => [
                        'id' => $institution->id,
                        'name' => $institution->name,
                    ],
                    'members' => $userData,
                    'stats' => $stats,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch institution members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's institution details.
     * Only Institution Admin can access their own institution.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyInstitution()
    {
        try {
            /** @var User $currentUser */
            $currentUser = Auth::user();
            
            // Check if user is Institution Admin
            if (!$this->userHasRole($currentUser, 'Institution Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Institution Admin can access this resource'
                ], 403);
            }
            
            // Check if user has an institution
            if (!$currentUser->institution_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to any institution'
                ], 404);
            }
            
            $institution = Institution::withCount(['logbookTemplates as templates_count', 'users as users_count'])
                ->findOrFail($currentUser->institution_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Institution retrieved successfully',
                'data' => $institution
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch institution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update current user's institution (for Institution Admin only).
     * Institution Admin can only update their own institution.
     * Supports partial updates - can update any field individually or multiple fields.
     * Note: Institution Admin cannot update the 'name' field.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMyInstitution(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        
        // Check if user is Institution Admin
        if (!$this->userHasRole($currentUser, 'Institution Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only Institution Admin can update institution'
            ], 403);
        }
        
        // Check if user has an institution
        if (!$currentUser->institution_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to any institution'
            ], 404);
        }
        
        // Validate the request data (all fields are optional for partial updates)
        // Note: Institution Admin cannot update 'name' field
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:1000',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'company_type' => 'nullable|string|max:100',
            'company_email' => 'nullable|email|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $institution = Institution::findOrFail($currentUser->institution_id);
            $originalData = $institution->toArray();
            
            // List of updatable fields (name is excluded for Institution Admin)
            $updatableFields = ['description', 'phone_number', 'address', 'company_type', 'company_email'];
            
            // Partial update - only update fields that are provided
            foreach ($updatableFields as $field) {
                if ($request->has($field)) {
                    $institution->$field = $request->$field;
                }
            }
            
            $institution->save();
            
            // Create audit log with changes
            $changes = [];
            $updatableFieldLabels = [
                'description' => 'description',
                'phone_number' => 'phone number',
                'address' => 'address',
                'company_type' => 'company type',
                'company_email' => 'company email',
            ];
            
            foreach ($updatableFields as $field) {
                if ($request->has($field) && $originalData[$field] !== $institution->$field) {
                    $changes[] = "{$updatableFieldLabels[$field]} updated";
                }
            }
            
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_INSTITUTION',
                'description' => 'Institution Admin updated institution "' . $institution->name . '"' . 
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
}