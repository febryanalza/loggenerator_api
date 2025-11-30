<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of all roles.
     * Authorization is handled by 'role:Super Admin,Admin' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Role::query();

            // Search by name if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if (in_array($sortBy, ['name', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $roles = $query->paginate($perPage);

            // Add permissions count to each role
            $roles->getCollection()->transform(function ($role) {
                $role->permissions_count = $role->permissions()->count();
                return $role;
            });

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => $roles->items(),
                'pagination' => [
                    'current_page' => $roles->currentPage(),
                    'per_page' => $roles->perPage(),
                    'total' => $roles->total(),
                    'last_page' => $roles->lastPage(),
                    'has_more' => $roles->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role with its permissions.
     * Authorization is handled by 'role:Super Admin,Admin' middleware at route level.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Role retrieved successfully',
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'description' => $permission->description ?? ''
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Assign permissions to a role.
     * Authorization is handled by 'role:Super Admin,Admin' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermissions(Request $request)
    {
        $validator = validator($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'integer|exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::findOrFail($request->role_id);
            $permissions = Permission::whereIn('id', $request->permission_ids)->get();

            // Assign permissions to role
            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission->name);
            }

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'ASSIGN_PERMISSIONS_TO_ROLE',
                'description' => "Assigned " . count($permissions) . " permissions to role '{$role->name}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => count($permissions) . " permissions assigned to role '{$role->name}' successfully",
                'role' => $role->name,
                'assigned_permissions' => $permissions->pluck('name')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions to role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke permissions from a role.
     * Authorization is handled by 'role:Super Admin,Admin' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokePermissions(Request $request)
    {
        $validator = validator($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'integer|exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::findOrFail($request->role_id);
            $permissions = Permission::whereIn('id', $request->permission_ids)->get();

            // Revoke permissions from role
            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission->name);
            }

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'REVOKE_PERMISSIONS_FROM_ROLE',
                'description' => "Revoked " . count($permissions) . " permissions from role '{$role->name}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => count($permissions) . " permissions revoked from role '{$role->name}' successfully",
                'role' => $role->name,
                'revoked_permissions' => $permissions->pluck('name')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke permissions from role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users assigned to a specific role.
     * Authorization is handled by 'role:Super Admin,Admin' middleware at route level.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleUsers($id)
    {
        try {
            $role = Role::findOrFail($id);
            $users = $role->users()->select('id', 'name', 'email', 'created_at')->get();

            return response()->json([
                'success' => true,
                'message' => "Users with role '{$role->name}' retrieved successfully",
                'data' => [
                    'role' => $role->name,
                    'users_count' => $users->count(),
                    'users' => $users
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get assignable roles for Institution Admin.
     * Institution Admin can only assign 'Institution Admin' or 'User' roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignableRolesForInstitutionAdmin()
    {
        try {
            // Institution Admin can only assign these roles
            $allowedRoles = ['Institution Admin', 'User'];
            
            $roles = Role::whereIn('name', $allowedRoles)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'guard_name', 'created_at']);

            return response()->json([
                'success' => true,
                'message' => 'Assignable roles retrieved successfully',
                'data' => $roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'label' => $this->getRoleLabel($role->name),
                        'guard_name' => $role->guard_name
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assignable roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all roles list (for display purposes).
     * Accessible by Institution Admin and above.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRolesList()
    {
        try {
            $roles = Role::orderBy('name', 'asc')
                ->get(['id', 'name', 'guard_name', 'created_at']);

            return response()->json([
                'success' => true,
                'message' => 'All roles retrieved successfully',
                'data' => $roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'label' => $this->getRoleLabel($role->name),
                        'guard_name' => $role->guard_name
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get human-readable label for role name.
     *
     * @param string $roleName
     * @return string
     */
    private function getRoleLabel($roleName)
    {
        $labels = [
            'Super Admin' => 'Super Admin',
            'Admin' => 'Admin',
            'Manager' => 'Manager',
            'Institution Admin' => 'Admin Institusi',
            'User' => 'Anggota'
        ];

        return $labels[$roleName] ?? $roleName;
    }
}