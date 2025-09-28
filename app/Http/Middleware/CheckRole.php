<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * Usage: Route::middleware('role:Super Admin,Admin')->group(...)
     * Usage: Route::middleware('role:Owner')->get(...)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'required_access' => 'Must be logged in'
            ], 401);
        }

        // Check if user has any of the required roles
        $hasRole = $this->userHasAnyRole($user, $roles);
        
        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required role: ' . implode(' or ', $roles),
                'required_access' => 'One of: ' . implode(', ', $roles),
                'user_roles' => $this->getUserRoles($user)
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has any of the specified roles
     *
     * @param  User  $user
     * @param  array  $roles
     * @return bool
     */
    private function userHasAnyRole(User $user, array $roles): bool
    {
        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('roles.name', $roles)
            ->exists();
            
        return $userRoles;
    }

    /**
     * Get user's current roles for debugging
     *
     * @param  User  $user
     * @return array
     */
    private function getUserRoles(User $user): array
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', User::class)
            ->pluck('roles.name')
            ->toArray();
    }
}