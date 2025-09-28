<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * 
     * Usage: Route::middleware('permission:view users,create users')->group(...)
     * Usage: Route::middleware('permission:manage templates')->get(...)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'required_access' => 'Must be logged in'
            ], 401);
        }

        // Check if user has any of the required permissions
        $hasPermission = $this->userHasAnyPermission($user, $permissions);
        
        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required permission: ' . implode(' or ', $permissions),
                'required_access' => 'One of: ' . implode(', ', $permissions),
                'user_permissions' => $this->getUserPermissions($user)
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has any of the specified permissions
     * (either directly or through roles)
     *
     * @param  User  $user
     * @param  array  $permissions
     * @return bool
     */
    private function userHasAnyPermission(User $user, array $permissions): bool
    {
        // Check direct permissions
        $directPermissions = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $user->id)
            ->where('model_has_permissions.model_type', User::class)
            ->whereIn('permissions.name', $permissions)
            ->exists();

        if ($directPermissions) {
            return true;
        }

        // Check permissions through roles
        $rolePermissions = DB::table('model_has_roles')
            ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('permissions.name', $permissions)
            ->exists();

        return $rolePermissions;
    }

    /**
     * Get user's current permissions for debugging
     *
     * @param  User  $user
     * @return array
     */
    private function getUserPermissions(User $user): array
    {
        // Get direct permissions
        $directPermissions = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $user->id)
            ->where('model_has_permissions.model_type', User::class)
            ->pluck('permissions.name')
            ->toArray();

        // Get permissions through roles
        $rolePermissions = DB::table('model_has_roles')
            ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', User::class)
            ->pluck('permissions.name')
            ->toArray();

        return array_unique(array_merge($directPermissions, $rolePermissions));
    }
}