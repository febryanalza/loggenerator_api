<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserLogbookAccess;
use Symfony\Component\HttpFoundation\Response;

class CheckLogbookAccess
{
    /**
     * Handle an incoming request for logbook template access.
     * 
     * Usage: Route::middleware('logbook.access:template_id')->get(...)
     * Usage: Route::middleware('logbook.access:Owner,Supervisor')->group(...)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed ...$params - Can be:
     *   - [templateId] or
     *   - [role1, role2, ...] or
     *   - [templateId, role1, role2, ...]
     */
    public function handle(Request $request, Closure $next, ...$params): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'required_access' => 'Must be logged in'
            ], 401);
        }

        // Check if user is Super Admin or Admin (they have access to everything)
        if ($this->isSuperAdminOrAdmin($user)) {
            return $next($request);
        }

        // Normalize params
        $templateId = null;
        $roles = [];

        if (!empty($params)) {
            $first = $params[0];
            // Treat UUID (36 chars) or numeric as template ID
            if (is_string($first) && (strlen($first) === 36 || is_numeric($first))) {
                $templateId = $first;
                $roles = array_slice($params, 1); // remaining are roles
            } else {
                // All params are roles
                $roles = array_values($params);
            }
        }

        // If templateId not explicitly provided, try to resolve from request
        if (!$templateId) {
            $templateId = $this->getTemplateIdFromRequest($request);
        }

        if (!$templateId) {
            return response()->json([
                'success' => false,
                'message' => 'Template ID is required in request',
                'required_data' => 'template_id in request body or route parameter'
            ], 400);
        }

        // Determine access check: with or without role constraints
        if (!empty($roles)) {
            $hasAccess = $this->userHasAnyTemplateRole($user, $templateId, $roles);
        } else {
            $hasAccess = $this->userHasTemplateAccess($user, $templateId, null);
        }

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient logbook access. You do not have required access to this template.',
                'required_access' => !empty($roles)
                    ? ('One of roles [' . implode(', ', $roles) . "] for template $templateId")
                    : ("Access to template: $templateId"),
                'user_template_access' => $this->getUserTemplateAccess($user, $templateId ?? '')
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has access to specific template with optional role requirement
     *
     * @param  User  $user
     * @param  string  $templateId
     * @param  string|null  $requiredRole
     * @return bool
     */
    private function userHasTemplateAccess(User $user, string $templateId, ?string $requiredRole = null): bool
    {
        $query = UserLogbookAccess::where('user_id', $user->id)
            ->where('logbook_template_id', $templateId);

        if ($requiredRole) {
            $query->whereHas('logbookRole', function ($q) use ($requiredRole) {
                $q->where('name', $requiredRole);
            });
        }

        return $query->exists();
    }

    /**
     * Check if user has specific role for template
     *
     * @param  User  $user
     * @param  string  $templateId
     * @param  string  $roleName
     * @return bool
     */
    private function userHasTemplateRole(User $user, string $templateId, string $roleName): bool
    {
        return UserLogbookAccess::where('user_id', $user->id)
            ->where('logbook_template_id', $templateId)
            ->whereHas('logbookRole', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            })
            ->exists();
    }

    /**
     * Check if user has ANY of the given roles for template
     *
     * @param User $user
     * @param string $templateId
     * @param array $roleNames
     * @return bool
     */
    private function userHasAnyTemplateRole(User $user, string $templateId, array $roleNames): bool
    {
        $roleNames = array_filter(array_map('strval', $roleNames));
        if (empty($roleNames)) {
            return $this->userHasTemplateAccess($user, $templateId, null);
        }

        return UserLogbookAccess::where('user_id', $user->id)
            ->where('logbook_template_id', $templateId)
            ->whereHas('logbookRole', function ($q) use ($roleNames) {
                $q->whereIn('name', $roleNames);
            })
            ->exists();
    }

    /**
     * Check if user has administrative roles that can override logbook access
     *
     * @param  User  $user
     * @return bool
     */
    private function isSuperAdminOrAdmin(User $user): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('roles.name', ['Super Admin', 'Admin', 'Manager', 'Institution Admin'])
            ->exists();
    }

    /**
     * Get template ID from request (body, route parameter, or query)
     *
     * @param  Request  $request
     * @return string|null
     */
    private function getTemplateIdFromRequest(Request $request): ?string
    {
        // Try different common parameter names first
        $templateId = $request->input('template_id') 
            ?? $request->input('logbook_template_id')
            ?? $request->route('template_id')
            ?? $request->route('templateId');
            
        if ($templateId) {
            return $templateId;
        }
        
        // Special handling for logbook entry routes (e.g., PUT /logbook-entries/{id})
        $entryId = $request->route('id');
        if ($entryId && $this->isLogbookEntryRoute($request)) {
            return $this->getTemplateIdFromLogbookEntry($entryId);
        }
        
        return $entryId;
    }
    
    /**
     * Check if the current route is a logbook entry route
     *
     * @param  Request  $request
     * @return bool
     */
    private function isLogbookEntryRoute(Request $request): bool
    {
        $uri = $request->route()->uri ?? '';
        return str_contains($uri, 'logbook-entries/{id}');
    }
    
    /**
     * Get template ID from logbook entry ID
     *
     * @param  string  $entryId
     * @return string|null
     */
    private function getTemplateIdFromLogbookEntry(string $entryId): ?string
    {
        try {
            return DB::table('logbook_data')
                ->where('id', $entryId)
                ->value('template_id');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get user's template access for debugging
     *
     * @param  User  $user
     * @param  string  $templateId
     * @return array
     */
    private function getUserTemplateAccess(User $user, string $templateId): array
    {
        if (!$templateId) {
            return [];
        }

        return UserLogbookAccess::where('user_id', $user->id)
            ->where('logbook_template_id', $templateId)
            ->with('logbookRole')
            ->get()
            ->map(function ($access) {
                return [
                    'template_id' => $access->logbook_template_id,
                    'role' => $access->logbookRole->name ?? 'Unknown',
                    'granted_at' => $access->created_at
                ];
            })
            ->toArray();
    }
}