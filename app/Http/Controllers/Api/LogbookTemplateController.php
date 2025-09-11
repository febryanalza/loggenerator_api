<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogbookTemplate;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LogbookTemplateController extends Controller
{
    /**
     * Store a newly created template in storage.
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
            // Create the template
            $template = new LogbookTemplate();
            $template->name = $request->name;
            $template->description = $request->description;
            $template->save();
            
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

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template
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