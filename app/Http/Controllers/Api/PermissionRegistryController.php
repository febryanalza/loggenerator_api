<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Permission Registry Controller
 * 
 * Provides API endpoints for dynamic permission management UI
 */
class PermissionRegistryController extends Controller
{
    /**
     * Get all permissions grouped by module
     * For admin UI to display available permissions
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Cache for 1 hour since this doesn't change often
            $permissions = Cache::remember('permission_registry', 3600, function () {
                return PermissionRegistry::getAllPermissions();
            });

            return response()->json([
                'success' => true,
                'message' => 'Permission registry retrieved successfully',
                'data' => $permissions,
                'meta' => [
                    'total_modules' => count($permissions),
                    'total_permissions' => count(PermissionRegistry::getPermissionNames())
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permission registry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permissions by risk level
     * 
     * @param string $riskLevel (low, medium, high, critical)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByRiskLevel(string $riskLevel)
    {
        try {
            $permissions = PermissionRegistry::getPermissionsByRiskLevel($riskLevel);

            return response()->json([
                'success' => true,
                'message' => "Permissions with risk level '{$riskLevel}' retrieved",
                'data' => $permissions,
                'meta' => [
                    'risk_level' => $riskLevel,
                    'count' => count($permissions)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions by risk level',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current database permissions vs registry
     * Shows sync status between code and database
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncStatus()
    {
        try {
            $registryPermissions = PermissionRegistry::getPermissionNames();
            $dbPermissions = Permission::pluck('name')->toArray();
            
            $missing = array_diff($registryPermissions, $dbPermissions);
            $extra = array_diff($dbPermissions, $registryPermissions);
            
            $inSync = empty($missing) && empty($extra);

            return response()->json([
                'success' => true,
                'message' => 'Sync status retrieved',
                'data' => [
                    'in_sync' => $inSync,
                    'registry_count' => count($registryPermissions),
                    'database_count' => count($dbPermissions),
                    'missing_in_db' => array_values($missing),
                    'extra_in_db' => array_values($extra),
                ],
                'action' => !$inSync ? 'Run: php artisan migrate to sync' : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check sync status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role-permission matrix
     * Shows which roles have which permissions
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function rolePermissionMatrix()
    {
        try {
            $roles = Role::with('permissions')->get();
            
            $matrix = [];
            foreach ($roles as $role) {
                $matrix[$role->name] = [
                    'id' => $role->id,
                    'is_system' => in_array($role->name, ['Super Admin', 'Admin', 'Manager', 'Institution Admin', 'User']),
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                    'permissions_count' => $role->permissions->count(),
                    'created_at' => $role->created_at,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Role-permission matrix retrieved',
                'data' => $matrix,
                'meta' => [
                    'total_roles' => count($matrix),
                    'total_permissions' => Permission::count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role-permission matrix',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's effective permissions
     * Shows all permissions a user has (direct + via roles)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myPermissions(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $directPermissions = $user->permissions->pluck('name')->toArray();
            $rolePermissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
            $allPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            
            return response()->json([
                'success' => true,
                'message' => 'User permissions retrieved',
                'data' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                    'direct_permissions' => $directPermissions,
                    'role_permissions' => $rolePermissions,
                    'all_permissions' => $allPermissions,
                ],
                'meta' => [
                    'direct_count' => count($directPermissions),
                    'via_roles_count' => count($rolePermissions),
                    'total_count' => count($allPermissions)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear permission cache
     * Use after modifying roles or permissions
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        try {
            // Clear Spatie permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            // Clear our registry cache
            Cache::forget('permission_registry');
            
            return response()->json([
                'success' => true,
                'message' => 'Permission cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
