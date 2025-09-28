<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogbookTemplate;
use App\Models\UserLogbookAccess;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LogbookTemplateController extends Controller
{
    /**
     * Store a newly created template in storage.
     * Uses enterprise-level transaction handling with automatic user access creation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Use database transaction for data consistency
            $result = DB::transaction(function () use ($request) {
                // Create the template
                $template = LogbookTemplate::create([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);

                // Persist ownership for traceability (created_by)
                if (Auth::check()) {
                    $template->created_by = Auth::id();
                    $template->save();
                }
                
                // Create audit log
                if (class_exists('\App\Models\AuditLog')) {
                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'CREATE_TEMPLATE',
                        'description' => 'Created new template: ' . $template->name,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                }

                // The model event will automatically create user_logbook_access entry
                // Load the template with its user access for response
                $template->load('userAccess.user', 'userAccess.logbookRole');

                return $template;
            });

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully with user access',
                'data' => $result,
                'user_access_created' => true
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the templates.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $templates = LogbookTemplate::with('fields')->get();
            
            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display templates accessible by the authenticated user.
     * Joins logbook_template with user_logbook_access to get user's accessible templates.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserTemplates()
    {
        try {
            $userId = Auth::id();
            
            $templates = LogbookTemplate::select([
                    'logbook_template.id',
                    'logbook_template.name',
                    'logbook_template.description',
                    'logbook_template.created_at',
                    'logbook_template.updated_at',
                    'logbook_roles.name as role_name',
                    'logbook_roles.description as role_description',
                    'user_logbook_access.created_at as access_granted_at'
                ])
                ->join('user_logbook_access', 'logbook_template.id', '=', 'user_logbook_access.logbook_template_id')
                ->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
                ->where('user_logbook_access.user_id', $userId)
                ->with(['fields'])
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'User templates retrieved successfully',
                'data' => $templates,
                'count' => $templates->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display templates with detailed permissions for the authenticated user.
     * Includes user's role and all permissions for each accessible template.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserTemplatesWithPermissions()
    {
        try {
            $userId = Auth::id();
            
            $templates = LogbookTemplate::select([
                    'logbook_template.id',
                    'logbook_template.name',
                    'logbook_template.description',
                    'logbook_template.created_at',
                    'logbook_template.updated_at',
                    'logbook_roles.id as role_id',
                    'logbook_roles.name as role_name',
                    'logbook_roles.description as role_description',
                    'user_logbook_access.created_at as access_granted_at'
                ])
                ->join('user_logbook_access', 'logbook_template.id', '=', 'user_logbook_access.logbook_template_id')
                ->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
                ->where('user_logbook_access.user_id', $userId)
                ->with(['fields'])
                ->get();

            // Add permissions for each template
            $templatesWithPermissions = $templates->map(function ($template) {
                $permissions = DB::table('logbook_role_permissions')
                    ->join('logbook_permissions', 'logbook_role_permissions.logbook_permission_id', '=', 'logbook_permissions.id')
                    ->where('logbook_role_permissions.logbook_role_id', $template->role_id)
                    ->select(['logbook_permissions.name', 'logbook_permissions.description'])
                    ->get();
                
                $template->permissions = $permissions;
                return $template;
            });
            
            return response()->json([
                'success' => true,
                'message' => 'User templates with permissions retrieved successfully',
                'data' => $templatesWithPermissions,
                'count' => $templatesWithPermissions->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user templates with permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific template with user's role and permissions.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserTemplate($id)
    {
        try {
            $userId = Auth::id();
            
            $template = LogbookTemplate::select([
                    'logbook_template.id',
                    'logbook_template.name',
                    'logbook_template.description',
                    'logbook_template.created_at',
                    'logbook_template.updated_at',
                    'logbook_roles.id as role_id',
                    'logbook_roles.name as role_name',
                    'logbook_roles.description as role_description',
                    'user_logbook_access.created_at as access_granted_at'
                ])
                ->join('user_logbook_access', 'logbook_template.id', '=', 'user_logbook_access.logbook_template_id')
                ->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
                ->where('user_logbook_access.user_id', $userId)
                ->where('logbook_template.id', $id)
                ->with(['fields'])
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found or access denied'
                ], 404);
            }

            // Get permissions for this template
            $permissions = DB::table('logbook_role_permissions')
                ->join('logbook_permissions', 'logbook_role_permissions.logbook_permission_id', '=', 'logbook_permissions.id')
                ->where('logbook_role_permissions.logbook_role_id', $template->role_id)
                ->select(['logbook_permissions.name', 'logbook_permissions.description'])
                ->get();
            
            $template->permissions = $permissions;
            
            return response()->json([
                'success' => true,
                'message' => 'Template retrieved successfully',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified template.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $template = LogbookTemplate::with('fields')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $template = LogbookTemplate::findOrFail($id);
            
            $template->name = $request->name;
            $template->description = $request->description;
            $template->save();
            
            // Create audit log
            if (class_exists('\App\Models\AuditLog')) {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'UPDATE_TEMPLATE',
                    'description' => 'Updated template "' . $template->name . '"',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified template from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $template = LogbookTemplate::findOrFail($id);
            $templateName = $template->name;
            
            $template->delete();
            
            // Create audit log
            if (class_exists('\App\Models\AuditLog')) {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'DELETE_TEMPLATE',
                    'description' => 'Deleted template "' . $templateName . '"',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}