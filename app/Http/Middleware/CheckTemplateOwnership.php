<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserLogbookAccess;
use Symfony\Component\HttpFoundation\Response;

class CheckTemplateOwnership
{
    /**
     * Handle an incoming request to check template ownership.
     * Only allows template owners or users with administrative roles to proceed.
     * Administrative roles: Super Admin, Admin, Manager, Institution Admin
     * 
     * Usage: Route::middleware('template.owner')->group(...)
     * Usage: Route::middleware('template.owner:template_id')->get(...)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $templateIdParam - Optional template ID parameter name
     */
    public function handle(Request $request, Closure $next, ?string $templateIdParam = null): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'required_access' => 'Must be logged in'
            ], 401);
        }

        // Check if user has administrative roles that can override ownership
        if ($this->isSuperAdminOrAdmin($user)) {
            return $next($request);
        }

        // Get template ID from request
        $templateId = $this->getTemplateIdFromRequest($request, $templateIdParam);
        
        if (!$templateId) {
            return response()->json([
                'success' => false,
                'message' => 'Template ID is required',
                'required_data' => 'template_id in request body, route parameter, or query string'
            ], 400);
        }

        // Check if user is owner of the template
        $isOwner = $this->isOwnerOfTemplate($user, $templateId);
        
        if (!$isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Only template owners or users with administrative roles (Super Admin, Admin, Manager, Institution Admin) can perform this action.',
                'required_access' => 'Owner role for this template or Super Admin/Admin/Manager/Institution Admin role',
                'template_id' => $templateId,
                'user_access' => $this->getUserTemplateRole($user, $templateId)
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user is owner of the specified template
     *
     * @param  User  $user
     * @param  string  $templateId
     * @return bool
     */
    private function isOwnerOfTemplate(User $user, string $templateId): bool
    {
        // Determine Owner role id dynamically for safety
        $ownerRoleId = DB::table('logbook_roles')->where('name', 'Owner')->value('id');
        if (!$ownerRoleId) {
            return false;
        }

        return UserLogbookAccess::where('user_id', $user->id)
            ->where('logbook_template_id', $templateId)
            ->where('logbook_role_id', $ownerRoleId)
            ->exists();
    }

    /**
     * Check if user has administrative roles that can override template ownership
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
     * Get template ID from request based on parameter name or common locations
     *
     * @param  Request  $request
     * @param  string|null  $paramName
     * @return string|null
     */
    private function getTemplateIdFromRequest(Request $request, ?string $paramName = null): ?string
    {
        // If specific parameter name is provided
        if ($paramName) {
            return $request->input($paramName) ?? $request->route($paramName);
        }

        // Try common parameter names
        return $request->input('template_id') 
            ?? $request->input('logbook_template_id')
            ?? $request->route('template_id')
            ?? $request->route('templateId')
            ?? $request->route('id')
            ?? $request->query('template_id');
    }

    /**
     * Get user's role for specific template (for debugging)
     *
     * @param  User  $user
     * @param  string  $templateId
     * @return string|null
     */
    private function getUserTemplateRole(User $user, string $templateId): ?string
    {
        $access = UserLogbookAccess::where('user_id', $user->id)
            ->where('logbook_template_id', $templateId)
            ->with('logbookRole')
            ->first();

        return $access ? $access->logbookRole->name : null;
    }
}