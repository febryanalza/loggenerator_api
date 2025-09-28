<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Create a new user with specified role
     * Only accessible by Super Admin
     *
     * @param CreateUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(CreateUserRequest $request)
    {
        // Verify that the authenticated user is Super Admin
        if (!$request->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can create users with roles.'
            ], 403);
        }

        try {
            // Validate role exists
            $roleName = $request->role;
            $allowedRoles = ['Admin', 'Manager', 'User'];
            
            if (!in_array($roleName, $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid role. Allowed roles: Admin, Manager, User'
                ], 422);
            }

            // Check if role exists in database
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => "Role '{$roleName}' not found in database"
                ], 422);
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'status' => 'active',
                'last_login' => null,
            ]);

            // Assign role to user
            $user->assignRole($roleName);

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'CREATE_USER',
                'description' => "Super Admin created user '{$user->name}' with role '{$roleName}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Also create audit log for the new user
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'USER_CREATED',
                'description' => "User account created by Super Admin with role '{$roleName}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'status' => $user->status,
                        'role' => $roleName,
                        'created_at' => $user->created_at
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create user: ' . $e->getMessage(), [
                'admin_user_id' => $request->user()->id,
                'request_data' => $request->except(['password'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ], 500);
        }
    }

    /**
     * Get list of users with their roles
     * Only accessible by Super Admin
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers(Request $request)
    {
        // Verify that the authenticated user is Super Admin
        if (!$request->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can view users.'
            ], 403);
        }

        try {
            $perPage = $request->get('per_page', 15);
            $users = User::with('roles')
                ->paginate($perPage);

            $userData = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'status' => $user->status,
                    'roles' => $user->roles->pluck('name'),
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => [
                    'users' => $userData,
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                        'last_page' => $users->lastPage()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users.'
            ], 500);
        }
    }

    /**
     * Update user role
     * Only accessible by Super Admin
     *
     * @param Request $request
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserRole(Request $request, string $userId)
    {
        // Verify that the authenticated user is Super Admin
        if (!$request->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can update user roles.'
            ], 403);
        }

        $request->validate([
            'role' => 'required|string|in:Admin,Manager,User'
        ]);

        try {
            $user = User::findOrFail($userId);
            $newRoleName = $request->role;
            $oldRoles = $user->roles->pluck('name')->toArray();

            // Check if role exists
            $role = Role::where('name', $newRoleName)->where('guard_name', 'web')->first();
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => "Role '{$newRoleName}' not found in database"
                ], 422);
            }

            // Prevent Super Admin from changing their own role
            if ($user->id === $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change your own role.'
                ], 422);
            }

            // Remove all current roles and assign new one
            $user->syncRoles([$newRoleName]);

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'UPDATE_USER_ROLE',
                'description' => "Super Admin changed user '{$user->name}' role from [" . implode(', ', $oldRoles) . "] to '{$newRoleName}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User role updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'old_roles' => $oldRoles,
                        'new_role' => $newRoleName
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update user role: ' . $e->getMessage(), [
                'admin_user_id' => $request->user()->id,
                'target_user_id' => $userId,
                'new_role' => $request->role
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user role.'
            ], 500);
        }
    }
}