<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Create a new user with specified role
     * Accessible by Super Admin and Admin
     *
     * @param CreateUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(CreateUserRequest $request)
    {
        $currentUser = $request->user();
        
        // Verify that the authenticated user has permission
        if (!$currentUser->hasAnyRole(['Super Admin', 'Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin and Admin can create users with roles.'
            ], 403);
        }

        try {
            // Validate role exists
            $roleName = $request->role;
            
            // Define allowed roles based on current user's role
            if ($currentUser->hasRole('Super Admin')) {
                $allowedRoles = ['Admin', 'Manager', 'User', 'Institution Admin'];
            } else if ($currentUser->hasRole('Admin')) {
                $allowedRoles = ['Manager', 'User', 'Institution Admin'];
            }
            
            if (!in_array($roleName, $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid role. Allowed roles: ' . implode(', ', $allowedRoles)
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

            // Prepare user data
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'status' => 'active',
                'last_login' => null,
            ];

            // Handle institution_id for Institution Admin role
            if ($roleName === 'Institution Admin') {
                // Validation is now handled by CreateUserRequest
                $userData['institution_id'] = $request->institution_id;
            }

            // Create user
            $user = User::create($userData);

            // Assign role to user
            $user->assignRole($roleName);

            // Get current user role for audit log
            $currentUserRole = $currentUser->getRoleNames()->first();
            
            // Get institution name if Institution Admin
            $institutionText = '';
            if ($roleName === 'Institution Admin' && $request->institution_id) {
                $institution = \App\Models\Institution::find($request->institution_id);
                $institutionText = $institution ? " for institution {$institution->name}" : '';
            }

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'CREATE_USER',
                'description' => "{$currentUserRole} created user '{$user->name}' with role '{$roleName}'{$institutionText}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Also create audit log for the new user
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'USER_CREATED',
                'description' => "User account created by {$currentUserRole} with role '{$roleName}'{$institutionText}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Prepare response data
            $responseData = [
                'id' => $user->id,  
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'status' => $user->status,
                'role' => $roleName,
                'created_at' => $user->created_at
            ];

            // Add institution information if applicable
            if ($roleName === 'Institution Admin' && isset($institution)) {
                $responseData['institution'] = [
                    'id' => $institution->id,
                    'name' => $institution->name,
                    'description' => $institution->description
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => $responseData
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
        $currentUser = $request->user();
        
        // Verify that the authenticated user has permission
        if (!$currentUser->hasAnyRole(['Super Admin', 'Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin and Admin can view users.'
            ], 403);
        }

        try {
            // Allow fetching all users by setting per_page=all or per_page=0
            // Default pagination is 50 users per page (increased from 15)
            $perPage = $request->get('per_page', 50);
            
            if ($perPage === 'all' || $perPage == 0) {
                // Get all users without pagination
                $allUsers = User::with(['roles', 'institution'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $userData = $allUsers->map(function ($user) {
                    $userData = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'status' => $user->status,
                        'roles' => $user->roles->pluck('name')->toArray(),
                        'created_at' => $user->created_at,
                        'last_login' => $user->last_login
                    ];

                    // Add institution information if user belongs to one
                    if ($user->institution) {
                        $userData['institution'] = [
                            'id' => $user->institution->id,
                            'name' => $user->institution->name,
                            'description' => $user->institution->description
                        ];
                    }

                    return $userData;
                });

                return response()->json([
                    'success' => true,
                    'message' => 'All users retrieved successfully',
                    'data' => [
                        'users' => $userData,
                        'total' => $allUsers->count()
                    ]
                ]);
            }
            
            // Paginated results
            $users = User::with(['roles', 'institution'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $userData = $users->map(function ($user) {
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'status' => $user->status,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login
                ];

                // Add institution information if user belongs to one
                if ($user->institution) {
                    $userData['institution'] = [
                        'id' => $user->institution->id,
                        'name' => $user->institution->name,
                        'description' => $user->institution->description
                    ];
                }

                return $userData;
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

    /**
     * Delete a user
     * Only accessible by Super Admin and Admin
     *
     * @param Request $request
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(Request $request, string $userId)
    {
        $currentUser = $request->user();
        
        // Verify that the authenticated user has permission
        if (!$currentUser->hasAnyRole(['Super Admin', 'Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin and Admin can delete users.'
            ], 403);
        }

        try {
            $userToDelete = User::findOrFail($userId);

            // Prevent user from deleting themselves
            if ($userToDelete->id === $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 422);
            }

            // Admin cannot delete Super Admin
            if ($currentUser->hasRole('Admin') && $userToDelete->hasRole('Super Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin cannot delete Super Admin users.'
                ], 403);
            }

            // Admin cannot delete other Admins
            if ($currentUser->hasRole('Admin') && $userToDelete->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin cannot delete other Admin users.'
                ], 403);
            }

            $userName = $userToDelete->name;
            $userEmail = $userToDelete->email;
            $userRoles = $userToDelete->roles->pluck('name')->toArray();

            // Delete user (this will also cascade delete related data based on foreign key constraints)
            $userToDelete->delete();

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'DELETE_USER',
                'description' => "{$currentUser->getRoleNames()->first()} deleted user '{$userName}' ({$userEmail}) with role(s): " . implode(', ', $userRoles),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode([
                    'deleted_user_id' => $userId,
                    'deleted_user_name' => $userName,
                    'deleted_user_email' => $userEmail,
                    'deleted_user_roles' => $userRoles
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
                'data' => [
                    'deleted_user' => [
                        'id' => $userId,
                        'name' => $userName,
                        'email' => $userEmail,
                        'roles' => $userRoles
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage(), [
                'admin_user_id' => $request->user()->id,
                'target_user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user. Please try again.'
            ], 500);
        }
    }

    /**
     * Search users by email or name
     * Accessible by Super Admin, Admin, Manager, and Institution Admin
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $currentUser = $request->user();
        
        // Verify that the authenticated user has permission
        if (!$currentUser->hasAnyRole(['Super Admin', 'Admin', 'Manager', 'Institution Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        try {
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);
            
            $query = User::with(['roles']);
            
            // Search by email or name
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('email', 'ILIKE', "%{$search}%")
                      ->orWhere('name', 'ILIKE', "%{$search}%");
                });
            }
            
            // For Institution Admin, optionally filter to same institution
            if ($currentUser->hasRole('Institution Admin') && $currentUser->institution_id) {
                // Can search all users but prioritize same institution
                $query->orderByRaw("CASE WHEN institution_id = ? THEN 0 ELSE 1 END", [$currentUser->institution_id]);
            }
            
            $users = $query->orderBy('name')
                          ->limit($perPage)
                          ->get();

            $userData = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Users found',
                'data' => $userData
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to search users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search users.'
            ], 500);
        }
    }

    /**
     * Add a member to institution (Institution Admin only)
     * Institution Admin can only add members to their own institution
     * Can create new user or assign existing user to institution
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addInstitutionMember(Request $request)
    {
        $currentUser = $request->user();
        
        // Verify that the authenticated user is Institution Admin
        if (!$currentUser->hasRole('Institution Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Institution Admin can add members.'
            ], 403);
        }

        // Ensure Institution Admin has an institution
        if (!$currentUser->institution_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to any institution.'
            ], 403);
        }

        // Validate request
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:8',
            'phone_number' => 'nullable|string|max:20',
            'role' => 'required|string|in:Institution Admin,User',
        ], [
            'role.in' => 'You can only assign Institution Admin or User role.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $roleName = $request->role;
            
            // Check if email already exists
            $existingUser = User::where('email', $request->email)->first();
            
            if ($existingUser) {
                // If user exists, check if they already belong to an institution
                if ($existingUser->institution_id) {
                    if ($existingUser->institution_id === $currentUser->institution_id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'User is already a member of your institution.'
                        ], 422);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'User already belongs to another institution.'
                        ], 422);
                    }
                }
                
                // Assign existing user to this institution
                $existingUser->institution_id = $currentUser->institution_id;
                $existingUser->save();
                
                // Assign role if needed
                if (!$existingUser->hasRole($roleName)) {
                    $existingUser->assignRole($roleName);
                }
                
                // Get institution name
                $institution = Institution::find($currentUser->institution_id);
                
                // Create audit log
                \App\Models\AuditLog::create([
                    'user_id' => $currentUser->id,
                    'action' => 'ADD_INSTITUTION_MEMBER',
                    'description' => "Institution Admin added existing user '{$existingUser->name}' to institution '{$institution->name}' with role '{$roleName}'",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Existing user added to your institution successfully',
                    'data' => [
                        'user' => [
                            'id' => $existingUser->id,
                            'name' => $existingUser->name,
                            'email' => $existingUser->email,
                            'phone_number' => $existingUser->phone_number,
                            'status' => $existingUser->status,
                            'role' => $roleName,
                            'institution_id' => $existingUser->institution_id,
                        ]
                    ]
                ]);
            }
            
            // Create new user
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => "Role '{$roleName}' not found in database"
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'institution_id' => $currentUser->institution_id,
                'status' => 'active',
                'last_login' => null,
            ]);

            // Assign role to user
            $user->assignRole($roleName);

            // Get institution name
            $institution = Institution::find($currentUser->institution_id);

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'ADD_INSTITUTION_MEMBER',
                'description' => "Institution Admin created new user '{$user->name}' for institution '{$institution->name}' with role '{$roleName}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Also create audit log for the new user
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'USER_CREATED',
                'description' => "User account created by Institution Admin for institution '{$institution->name}' with role '{$roleName}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'New member added to your institution successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'status' => $user->status,
                        'role' => $roleName,
                        'institution_id' => $user->institution_id,
                        'institution' => [
                            'id' => $institution->id,
                            'name' => $institution->name,
                        ],
                        'created_at' => $user->created_at
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to add institution member: ' . $e->getMessage(), [
                'admin_user_id' => $currentUser->id,
                'institution_id' => $currentUser->institution_id,
                'request_data' => $request->except(['password'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add member. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}