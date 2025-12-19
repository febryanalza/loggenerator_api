<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 15);
            
            $notifications = $user->notifications()
                ->when($request->get('unread_only'), function($query) {
                    return $query->unread();
                })
                ->when($request->get('read_only'), function($query) {
                    return $query->read();
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => [
                    'notifications' => $notifications->items(),
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                        'unread_count' => $user->unreadNotifications()->count()
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a notification to a specific user or multiple users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        // Validate the request
        $validator = validator($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'action_text' => 'nullable|string|max:100',
            'action_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check authorization (only admins can send notifications to users)
        if (!$request->user()->can('notifications.send')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to send notifications'
            ], 403);
        }

        try {
            $users = User::whereIn('id', $request->user_ids)->get();
            
            $notification = new GeneralNotification(
                $request->title,
                $request->message,
                $request->action_text,
                $request->action_url
            );
            
            NotificationFacade::send($users, $notification);
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'SEND_NOTIFICATIONS',
                'description' => 'Sent notification "' . $request->title . '" to ' . count($request->user_ids) . ' users',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifications sent to ' . count($users) . ' users successfully',
                'data' => [
                    'notification_count' => count($users),
                    'title' => $request->title
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
            'action_text' => 'nullable|string|max:100',
            'action_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check authorization (only admins can send notifications to role groups)
        if (!$request->user()->can('notifications.send.to-role')) {
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
            
            $notification = new GeneralNotification(
                $request->title,
                $request->message,
                $request->action_text,
                $request->action_url
            );
            
            NotificationFacade::send($users, $notification);
            
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
                'message' => 'Notifications sent to ' . count($users) . ' users with role ' . $request->role_name,
                'data' => [
                    'notification_count' => count($users),
                    'role' => $request->role_name,
                    'title' => $request->title
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

    /**
     * Mark a notification as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->where('id', $id)->first();
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }
            
            $notification->markAsRead();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'MARK_NOTIFICATION_READ',
                'description' => 'Marked notification as read: ' . ($notification->data['title'] ?? 'Unknown'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read successfully',
                'data' => $notification->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            $unreadCount = $user->unreadNotifications()->count();
            
            $user->unreadNotifications->markAsRead();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'MARK_ALL_NOTIFICATIONS_READ',
                'description' => 'Marked ' . $unreadCount . ' notifications as read',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => $unreadCount . ' notifications marked as read successfully',
                'data' => [
                    'marked_count' => $unreadCount
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->where('id', $id)->first();
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }
            
            $title = $notification->data['title'] ?? 'Unknown';
            $notification->delete();
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_NOTIFICATION',
                'description' => 'Deleted notification: ' . $title,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        try {
            $user = $request->user();
            
            $totalCount = $user->notifications()->count();
            $unreadCount = $user->unreadNotifications()->count();
            $readCount = $totalCount - $unreadCount;
            
            return response()->json([
                'success' => true,
                'message' => 'Notification statistics retrieved successfully',
                'data' => [
                    'total' => $totalCount,
                    'unread' => $unreadCount,
                    'read' => $readCount,
                    'unread_percentage' => $totalCount > 0 ? round(($unreadCount / $totalCount) * 100, 2) : 0
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notification statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}