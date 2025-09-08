<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Store a newly created permission in storage.
     *
     * @param  \App\Http\Requests\StorePermissionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePermissionRequest $request)
    {
        try {
            // Create the permission
            $permission = new Permission();
            $permission->name = $request->name;
            $permission->description = $request->description;
            $permission->save();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_PERMISSION',
                'description' => 'Created new permission: ' . $permission->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => new PermissionResource($permission)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store multiple permissions at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBatch(Request $request)
    {
        // Validate the request
        $validator = validator($request->all(), [
            'permissions' => 'required|array|min:1',
            'permissions.*.name' => 'required|string|max:255|distinct|unique:permissions,name',
            'permissions.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check authorization (only admins can create permissions)
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to create permissions'
            ], 403);
        }

        try {
            $createdPermissions = [];
            
            foreach ($request->permissions as $permissionData) {
                $permission = new Permission();
                $permission->name = $permissionData['name'];
                $permission->description = $permissionData['description'] ?? null;
                $permission->save();
                
                $createdPermissions[] = $permission;
            }
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_PERMISSIONS_BATCH',
                'description' => 'Created ' . count($createdPermissions) . ' permissions',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => count($createdPermissions) . ' permissions created successfully',
                'data' => PermissionResource::collection($createdPermissions)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}