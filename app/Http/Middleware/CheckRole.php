<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

/**
 * Check Role Middleware (Enhanced)
 * 
 * DEPRECATED: This middleware is kept for backward compatibility only.
 * 
 * Recommendation: Use 'permission' middleware instead for granular access control.
 * - Old: Route::middleware('role:Super Admin,Admin')
 * - New: Route::middleware('permission:users.view.all')
 * 
 * Uses Spatie's built-in role checking with caching for better performance.
 * 
 * Usage:
 * - Single: Route::middleware('role:Admin')
 * - Multiple (OR): Route::middleware('role:Admin,Manager')
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorizedResponse();
        }

        // Parse roles (support comma-separated and pipe-separated)
        $roleList = $this->parseRoles($roles);
        
        // Check if user has ANY of the required roles (OR logic)
        // Using Spatie's built-in method with caching
        $hasRole = $user->hasAnyRole($roleList);
        
        if (!$hasRole) {
            // Log for migration tracking (optional)
            if (config('app.debug')) {
                Log::channel('permission')->notice('Role check (DEPRECATED)', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'required_roles' => $roleList,
                    'user_roles' => $user->getRoleNames()->toArray(),
                    'url' => $request->fullUrl(),
                    'suggestion' => 'Consider migrating to permission-based middleware'
                ]);
            }
            
            return $this->forbiddenResponse($user, $roleList);
        }

        return $next($request);
    }

    /**
     * Parse role strings, handle comma and pipe separated values
     *
     * @param array $roles
     * @return array
     */
    private function parseRoles(array $roles): array
    {
        $parsed = [];
        
        foreach ($roles as $role) {
            // Split by comma or pipe for multiple roles
            if (str_contains($role, ',')) {
                $split = array_map('trim', explode(',', $role));
                $parsed = array_merge($parsed, $split);
            } elseif (str_contains($role, '|')) {
                $split = array_map('trim', explode('|', $role));
                $parsed = array_merge($parsed, $split);
            } else {
                $parsed[] = trim($role);
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
     * @param array $requiredRoles
     * @return Response
     */
    private function forbiddenResponse(User $user, array $requiredRoles): Response
    {
        $userRoles = $user->getRoleNames()->toArray();
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        return response()->json([
            'success' => false,
            'message' => 'Insufficient permissions. Required role: ' . implode(' OR ', $requiredRoles),
            'error_code' => 'FORBIDDEN',
            'required' => [
                'roles' => $requiredRoles,
                'logic' => 'ANY' // User needs ANY of these roles
            ],
            'current' => [
                'roles' => $userRoles,
                'permissions_count' => count($userPermissions)
            ],
            'hint' => $this->generateHint($requiredRoles, $userRoles)
        ], 403);
    }

    /**
     * Generate helpful hint for users
     */
    private function generateHint(array $required, array $userRoles): string
    {
        if (empty($userRoles)) {
            return 'You have no roles assigned. Contact your administrator.';
        }
        
        $missing = array_diff($required, $userRoles);
        
        if (empty($missing)) {
            return 'Access check failed. Please try again or contact support.';
        }
        
        return 'You need one of these roles: ' . implode(' or ', $missing) . '. Current role: ' . implode(', ', $userRoles) . '.';
    }
}