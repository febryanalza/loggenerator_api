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
            'user_id' => 'sometimes|exists:users,id',
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
            
            // If user_id is provided in request, use it (for admin purposes)
            // Otherwise use the authenticated user's ID
            $template->user_id = $request->user_id ?? Auth::id();
            
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
}