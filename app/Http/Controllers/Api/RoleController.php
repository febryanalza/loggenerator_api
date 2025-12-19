<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Display a listing of all roles with permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Role::with('permissions');

            // Search by name if provided
            if ($request->has('search') && $request->search) {
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

            // Transform roles with permissions
            $rolesData = $roles->getCollection()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'is_system' => in_array($role->name, ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User']),
                    'permissions_count' => $role->permissions->count(),
                    'permissions' => $role->permissions->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name
                        ];
                    }),
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => $rolesData,
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
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);
            $usersCount = $role->users()->count();

            return response()->json([
                'success' => true,
                'message' => 'Role retrieved successfully',
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'is_system' => in_array($role->name, ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User']),
                    'users_count' => $usersCount,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name
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
     * Create a new custom role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create the role
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web'
            ]);

            // Assign permissions if provided
            if ($request->has('permissions') && !empty($request->permissions)) {
                $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
                $role->syncPermissions($permissions);
            }

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_ROLE',
                'description' => "Created new role: {$role->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Role '{$role->name}' created successfully",
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'permissions' => $role->permissions->pluck('name')
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a custom role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            // Prevent editing system roles names
            $systemRoles = ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User'];
            if (in_array($role->name, $systemRoles) && $request->has('name') && $request->name !== $role->name) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot rename system roles'
                ], 403);
            }

            $validator = validator($request->all(), [
                'name' => 'sometimes|string|max:255|unique:roles,name,' . $id,
                'permissions' => 'nullable|array',
                'permissions.*' => 'integer|exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $oldName = $role->name;
            
            // Update role name if provided
            if ($request->has('name')) {
                $role->name = $request->name;
                $role->save();
            }

            // Update permissions if provided
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
                $role->syncPermissions($permissions);
            }

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_ROLE',
                'description' => "Updated role: {$oldName}" . ($oldName !== $role->name ? " → {$role->name}" : ""),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Role updated successfully",
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a custom role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Prevent deleting system roles
            $systemRoles = ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User'];
            if (in_array($role->name, $systemRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete system roles'
                ], 403);
            }

            // Check if role has users
            $usersCount = $role->users()->count();
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete role '{$role->name}' because it has {$usersCount} users assigned"
                ], 400);
            }

            $roleName = $role->name;

            // Create audit log before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_ROLE',
                'description' => "Deleted role: {$roleName}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => "Role '{$roleName}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync permissions to a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncPermissions(Request $request)
    {
        $validator = validator($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
            'permission_ids' => 'required|array',
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
            $permissions = Permission::whereIn('id', $request->permission_ids)->pluck('name');

            $role->syncPermissions($permissions);

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'SYNC_ROLE_PERMISSIONS',
                'description' => "Synced " . count($permissions) . " permissions to role '{$role->name}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Permissions synced to role '{$role->name}' successfully",
                'data' => [
                    'role' => $role->name,
                    'permissions' => $permissions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to a role (additive).
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
                'data' => [
                    'role' => $role->name,
                    'assigned_permissions' => $permissions->pluck('name')
                ]
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
                'data' => [
                    'role' => $role->name,
                    'revoked_permissions' => $permissions->pluck('name')
                ]
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
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleUsers($id)
    {
        try {
            $role = Role::findOrFail($id);
            $users = $role->users()->select('id', 'name', 'email', 'status', 'created_at')->get();

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
     * Get permission matrix (all roles vs all permissions).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Get permission matrix for role-permission management UI.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissionMatrix()
    {
        try {
            // Load all roles with their permissions
            $roles = Role::with('permissions')->orderBy('name')->get();
            
            // Load all permissions
            $permissions = Permission::orderBy('name')->get();

            Log::info('Permission Matrix loaded', [
                'total_roles' => $roles->count(),
                'total_permissions' => $permissions->count()
            ]);

            // Build matrix - map each role with all permissions and their status
            $matrix = [];
            foreach ($roles as $role) {
                $rolePermissionIds = $role->permissions->pluck('id')->toArray();
                
                $permissionsData = [];
                foreach ($permissions as $perm) {
                    $permissionsData[] = [
                        'permission_id' => $perm->id,
                        'permission_name' => $perm->name,
                        'has_permission' => in_array($perm->id, $rolePermissionIds)
                    ];
                }
                
                $matrix[] = [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                    'is_system' => in_array($role->name, ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User']),
                    'permissions' => $permissionsData
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Permission matrix loaded successfully',
                'data' => [
                    'roles' => $roles->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'name' => $r->name,
                            'is_system' => in_array($r->name, ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User']),
                            'permissions_count' => $r->permissions->count()
                        ];
                    })->values(),
                    'permissions' => $permissions->map(function ($p) {
                        $parts = explode('.', $p->name);
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'category' => $parts[0] ?? 'other',
                            'scope' => $parts[1] ?? null
                        ];
                    })->values(),
                    'matrix' => $matrix
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Failed to get permission matrix', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load permission matrix',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get role assignment history from audit logs (Alias for backward compatibility).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleAssignmentHistory(Request $request)
    {
        return $this->getAssignmentHistory($request);
    }

    /**
     * Get role assignment history from audit logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignmentHistory(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            
            // Get role-related audit logs
            $roleActions = [
                'CREATE_ROLE',
                'UPDATE_ROLE',
                'DELETE_ROLE',
                'ASSIGN_PERMISSIONS_TO_ROLE',
                'REVOKE_PERMISSIONS_FROM_ROLE',
                'SYNC_ROLE_PERMISSIONS',
                'ASSIGN_USER_ROLE',
                'CHANGE_USER_ROLE',
                'USER_ROLE_CHANGED'
            ];

            $query = AuditLog::with('user:id,name,email')
                ->whereIn('action', $roleActions)
                ->orderBy('created_at', 'desc');

            // Filter by action type
            if ($request->has('action_type') && $request->action_type) {
                $query->where('action', $request->action_type);
            }

            // Filter by date range
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $logs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'last_page' => $logs->lastPage(),
                    'has_more' => $logs->hasMorePages()
                ],
                'available_actions' => $roleActions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get assignment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update role permissions from matrix (Alias for syncPermissions).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRolePermissions(Request $request)
    {
        return $this->syncPermissions($request);
    }

    /**
     * Get all permissions list.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPermissions()
    {
        try {
            $permissions = Permission::orderBy('name')->get();

            // Group by category
            $grouped = $permissions->groupBy(function ($perm) {
                $parts = explode('.', $perm->name);
                return $parts[0] ?? 'other';
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'permissions' => $permissions,
                    'grouped' => $grouped,
                    'total' => $permissions->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        try {
            $roles = Role::all();
            $permissions = Permission::count();

            // System role names
            $systemRoleNames = ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User'];

            // Count users for each role
            $rolesWithUserCount = [];
            foreach ($roles as $role) {
                $usersCount = DB::table('model_has_roles')
                    ->where('role_id', $role->id)
                    ->count();
                    
                $rolesWithUserCount[] = [
                    'name' => $role->name,
                    'users_count' => $usersCount
                ];
            }

            $stats = [
                'total_roles' => $roles->count(),
                'system_roles' => $roles->whereIn('name', $systemRoleNames)->count(),
                'custom_roles' => $roles->whereNotIn('name', $systemRoleNames)->count(),
                'total_permissions' => $permissions,
                'roles_by_users' => collect($rolesWithUserCount)
                    ->sortByDesc('users_count')
                    ->values()
                    ->toArray()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get role statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get assignable roles for Institution Admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignableRolesForInstitutionAdmin()
    {
        try {
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