<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

/**
 * Check Permission Middleware (Enhanced)
 * 
 * Supports granular permission checking using Spatie Permission package.
 * Now uses Spatie's built-in methods for better performance with caching.
 * 
 * Usage:
 * - Single: Route::middleware('permission:users.view.all')
 * - Multiple (OR): Route::middleware('permission:users.create,users.update')
 * - Multiple (AND): Route::middleware(['permission:users.create', 'permission:users.update'])
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorizedResponse();
        }

        // Parse permissions (support comma-separated)
        $permissionList = $this->parsePermissions($permissions);
        
        // Check if user has ANY of the required permissions (OR logic)
        // Using Spatie's built-in method with caching
        $hasPermission = $user->hasAnyPermission($permissionList);
        
        if (!$hasPermission) {
            // Log for debugging (optional, remove in production)
            if (config('app.debug')) {
                Log::channel('permission')->warning('Permission denied', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'required_permissions' => $permissionList,
                    'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    'user_roles' => $user->getRoleNames()->toArray(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);
            }
            
            return $this->forbiddenResponse($user, $permissionList);
        }

        return $next($request);
    }

    /**
     * Parse permission strings, handle comma-separated values
     *
     * @param array $permissions
     * @return array
     */
    private function parsePermissions(array $permissions): array
    {
        $parsed = [];
        
        foreach ($permissions as $permission) {
            // Split by comma for multiple permissions in one string
            if (str_contains($permission, ',')) {
                $split = array_map('trim', explode(',', $permission));
                $parsed = array_merge($parsed, $split);
            } else {
                $parsed[] = trim($permission);
            }
        }
        
        return array_unique(array_filter($parsed));
    }

    /**
     * Unauthorized response (401)
     */
    private function unauthorizedResponse(): Response
    {
        return response()->json([
            'success' => false,
            'message' => 'Authentication required',
            'error_code' => 'UNAUTHORIZED',
            'required_access' => 'Must be logged in'
        ], 401);
    }

    /**
     * Forbidden response (403)
     * 
     * @param User $user
     * @param array $requiredPermissions
     * @return Response
     */
    private function forbiddenResponse(User $user, array $requiredPermissions): Response
    {
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        $userRoles = $user->getRoleNames()->toArray();
        
        // Generate helpful message
        $message = 'Insufficient permissions. You need one of: ' . implode(' OR ', $requiredPermissions);
        
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'FORBIDDEN',
            'required' => [
                'permissions' => $requiredPermissions,
                'logic' => 'ANY' // User needs ANY of these permissions
            ],
            'current' => [
                'roles' => $userRoles,
                'permissions' => $userPermissions
            ],
            'hint' => $this->generateHint($requiredPermissions, $userPermissions, $userRoles)
        ], 403);
    }

    /**
     * Generate helpful hint for users
     */
    private function generateHint(array $required, array $userPerms, array $userRoles): string
    {
        if (empty($userRoles)) {
            return 'You have no roles assigned. Contact your administrator.';
        }
        
        $missing = array_diff($required, $userPerms);
        
        if (empty($missing)) {
            return 'Access check failed. Please try again or contact support.';
        }
        
        return 'Missing permission: ' . implode(' or ', $missing) . '. Contact your administrator to grant access.';
    }
}