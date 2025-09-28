<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Display a listing of all permissions.
     * Authorization is handled by 'role:Super Admin' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Permission::query();

            // Search by name if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            // Filter by permission type if provided
            if ($request->has('type')) {
                $type = $request->type;
                $query->where('name', 'like', "%{$type}%");
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if (in_array($sortBy, ['name', 'description', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $permissions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Permissions retrieved successfully',
                'data' => PermissionResource::collection($permissions->items()),
                'pagination' => [
                    'current_page' => $permissions->currentPage(),
                    'per_page' => $permissions->perPage(),
                    'total' => $permissions->total(),
                    'last_page' => $permissions->lastPage(),
                    'has_more' => $permissions->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified permission.
     * Authorization is handled by 'role:Super Admin' middleware at route level.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $permission = Permission::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Permission retrieved successfully',
                'data' => new PermissionResource($permission)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store a newly created permission in storage.
     * Authorization is handled by 'role:Super Admin' middleware at route level.
     *
     * @param  \App\Http\Requests\StorePermissionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePermissionRequest $request)
    {
        try {
            // Create the permission
            $permission = new Permission();
            $permission->name = $request->name;
            $permission->description = $request->description;
            $permission->save();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_PERMISSION',
                'description' => 'Created new permission: ' . $permission->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => new PermissionResource($permission)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store multiple permissions at once.
     * Authorization is handled by 'role:Super Admin' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBatch(Request $request)
    {
        // Validate the request
        $validator = validator($request->all(), [
            'permissions' => 'required|array|min:1',
            'permissions.*.name' => 'required|string|max:255|distinct|unique:permissions,name',
            'permissions.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Authorization is handled by middleware

        try {
            $createdPermissions = [];
            
            foreach ($request->permissions as $permissionData) {
                $permission = new Permission();
                $permission->name = $permissionData['name'];
                $permission->description = $permissionData['description'] ?? null;
                $permission->save();
                
                $createdPermissions[] = $permission;
            }
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_PERMISSIONS_BATCH',
                'description' => 'Created ' . count($createdPermissions) . ' permissions',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => count($createdPermissions) . ' permissions created successfully',
                'data' => PermissionResource::collection($createdPermissions)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to a role.
     * Authorization is handled by 'role:Super Admin' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignToRole(Request $request)
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
            $role = \Spatie\Permission\Models\Role::findOrFail($request->role_id);
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
     * Authorization is handled by 'role:Super Admin' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeFromRole(Request $request)
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
            $role = \Spatie\Permission\Models\Role::findOrFail($request->role_id);
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
}