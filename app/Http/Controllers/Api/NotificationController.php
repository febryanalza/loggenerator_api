<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Store a newly created notification in storage.
     *
     * @param  \App\Http\Requests\StoreNotificationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreNotificationRequest $request)
    {
        try {
            // Set user_id to authenticated user if not provided (for non-admin users)
            $userId = $request->user_id ?? Auth::id();
            
            // Create the notification
            $notification = new Notification();
            $notification->user_id = $userId;
            $notification->title = $request->title;
            $notification->message = $request->message;
            $notification->is_read = $request->is_read ?? false;
            $notification->save();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_NOTIFICATION',
                'description' => 'Created notification for user #' . $userId . ': ' . $notification->title,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => new NotificationResource($notification)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send notification to multiple users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToMultipleUsers(Request $request)
    {
        // Validate the request
        $validator = validator($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'is_read' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check authorization (only admins can send notifications to multiple users)
        if (!$request->user()->hasRole('Super Admin ,Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to send notifications to multiple users'
            ], 403);
        }

        try {
            $createdNotifications = [];
            
            foreach ($request->user_ids as $userId) {
                $notification = new Notification();
                $notification->user_id = $userId;
                $notification->title = $request->title;
                $notification->message = $request->message;
                $notification->is_read = $request->is_read ?? false;
                $notification->save();
                
                $createdNotifications[] = $notification;
            }
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'SEND_NOTIFICATIONS_MULTIPLE',
                'description' => 'Sent notification "' . $request->title . '" to ' . count($request->user_ids) . ' users',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifications sent to ' . count($createdNotifications) . ' users successfully',
                'data' => NotificationResource::collection($createdNotifications)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send notification to all users with a specific role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToRole(Request $request)
    {
        // Validate the request
        $validator = validator($request->all(), [
            'role_name' => 'required|exists:roles,name',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'is_read' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check authorization (only admins can send notifications to role groups)
        if (!$request->user()->hasRole('Super Admin, Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to send notifications to role groups'
            ], 403);
        }

        try {
            // Get all users with the specified role
            $users = User::whereHas('roles', function($query) use ($request) {
                $query->where('name', $request->role_name);
            })->get();
            
            $createdNotifications = [];
            
            foreach ($users as $user) {
                $notification = new Notification();
                $notification->user_id = $user->id;
                $notification->title = $request->title;
                $notification->message = $request->message;
                $notification->is_read = $request->is_read ?? false;
                $notification->save();
                
                $createdNotifications[] = $notification;
            }
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'SEND_NOTIFICATIONS_TO_ROLE',
                'description' => 'Sent notification "' . $request->title . '" to all users with role: ' . $request->role_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifications sent to ' . count($createdNotifications) . ' users with role ' . $request->role_name,
                'data' => [
                    'notification_count' => count($createdNotifications),
                    'role' => $request->role_name
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}