<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller as BaseController;
use App\Models\UserLogbookAccess;
use App\Models\LogbookTemplate;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserLogbookAccessController extends BaseController
{
    /**
     * Initialize controller.
     * Authentication is handled by auth:sanctum middleware.
     * Authorization is handled by custom middleware at route level:
     * - 'template.owner' for modification operations (store, update, destroy, bulkStore)
     * - View operations have no additional authorization middleware (open to authenticated users)
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Note: Authorization helper methods removed as they are now handled by middleware

    /**
     * Display a listing of user logbook access.
     * Shows all access records with filters.
     * Authorization is handled by route-level middleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Authorization is now handled by middleware
            $query = UserLogbookAccess::with([
                'user:id,name,email',
                'logbookTemplate:id,name,description',
                'logbookRole:id,name,description'
            ]);

            // Filter by template if provided
            if ($request->has('template_id')) {
                $query->where('logbook_template_id', $request->template_id);
            }

            // Filter by user if provided
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by role if provided
            if ($request->has('role_id')) {
                $query->where('logbook_role_id', $request->role_id);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $access = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'User logbook access retrieved successfully',
                'data' => $access->items(),
                'pagination' => [
                    'current_page' => $access->currentPage(),
                    'per_page' => $access->perPage(),
                    'total' => $access->total(),
                    'last_page' => $access->lastPage(),
                    'has_more' => $access->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user logbook access',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user logbook access.
     * Authorization is handled by 'template.owner' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validation rules - accept either user_id or user_email
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required_without:user_email',
                'uuid',
                'exists:users,id'
            ],
            'user_email' => [
                'required_without:user_id',
                'email',
                'exists:users,email'
            ],
            'logbook_template_id' => [
                'required',
                'uuid',
                'exists:logbook_template,id'
            ],
            'logbook_role_id' => [
                'required',
                'integer',
                'exists:logbook_roles,id'
            ]
        ], [
            'user_id.required_without' => 'Either user_id or user_email is required',
            'user_email.required_without' => 'Either user_id or user_email is required',
            'user_email.exists' => 'User with this email does not exist'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Authorization is handled by middleware

        try {
            // Convert email to user_id if email is provided
            $userId = $request->user_id;
            $userInfo = null;
            
            if ($request->has('user_email') && !$request->has('user_id')) {
                $user = User::where('email', $request->user_email)->first();
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found with provided email',
                        'email' => $request->user_email
                    ], 404);
                }
                $userId = $user->id;
                $userInfo = ['email' => $user->email, 'name' => $user->name];
            } else if ($request->has('user_id')) {
                $user = User::find($request->user_id);
                $userInfo = ['email' => $user->email, 'name' => $user->name];
            }

            // Check if access already exists
            $existingAccess = UserLogbookAccess::where('user_id', $userId)
                                              ->where('logbook_template_id', $request->logbook_template_id)
                                              ->first();

            if ($existingAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has access to this template',
                    'user_info' => $userInfo,
                    'existing_access' => $existingAccess->load(['user', 'logbookTemplate', 'logbookRole'])
                ], 409);
            }

            // Create access within transaction
            $access = DB::transaction(function () use ($userId, $request, $userInfo) {
                $access = UserLogbookAccess::create([
                    'user_id' => $userId,
                    'logbook_template_id' => $request->logbook_template_id,
                    'logbook_role_id' => $request->logbook_role_id,
                ]);

                // Create audit log
                if (class_exists('\App\Models\AuditLog')) {
                    $description = $request->has('user_email') 
                        ? "Granted access to template {$request->logbook_template_id} for user with email {$request->user_email} (ID: {$userId})"
                        : "Granted access to template {$request->logbook_template_id} for user {$userId}";
                        
                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'GRANT_TEMPLATE_ACCESS',
                        'description' => $description,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                }

                return $access;
            });

            // Load relationships for response
            $access->load(['user', 'logbookTemplate', 'logbookRole']);

            // Fire event for realtime notification
            event(new \App\Events\LogbookAccessGranted(
                $access->user,           // User who received access
                Auth::user(),           // User who granted access
                $access->logbookTemplate, // Template that was shared
                $access->logbookRole->name // Role name
            ));

            return response()->json([
                'success' => true,
                'message' => 'User logbook access created successfully',
                'user_resolved' => $userInfo,
                'data' => $access
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user logbook access',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all user access records for a specific template.
     * Authorization is handled by route-level middleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByTemplate(Request $request, $templateId)
    {
        try {
            // Validate template exists
            $template = LogbookTemplate::findOrFail($templateId);
            
            // Authorization is handled by middleware

            $query = UserLogbookAccess::with([
                'user:id,name,email',
                'logbookRole:id,name,description'
            ])->where('logbook_template_id', $templateId);

            // Additional filters
            if ($request->has('role_id')) {
                $query->where('logbook_role_id', $request->role_id);
            }

            if ($request->has('user_email')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('email', 'like', '%' . $request->user_email . '%');
                });
            }

            if ($request->has('user_name')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->user_name . '%');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            } elseif ($sortBy === 'user_name') {
                $query->join('users', 'user_logbook_access.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortDirection)
                      ->select('user_logbook_access.*');
            } elseif ($sortBy === 'role_name') {
                $query->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
                      ->orderBy('logbook_roles.name', $sortDirection)
                      ->select('user_logbook_access.*');
            }

            // Pagination or get all
            if ($request->get('paginate', true)) {
                $perPage = $request->get('per_page', 15);
                $access = $query->paginate($perPage);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Template user access retrieved successfully',
                    'template' => [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description
                    ],
                    'data' => $access->items(),
                    'pagination' => [
                        'current_page' => $access->currentPage(),
                        'per_page' => $access->perPage(),
                        'total' => $access->total(),
                        'last_page' => $access->lastPage(),
                        'has_more' => $access->hasMorePages()
                    ]
                ]);
            } else {
                $access = $query->get();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Template user access retrieved successfully',
                    'template' => [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description
                    ],
                    'data' => $access,
                    'total_count' => $access->count()
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve template user access',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Get template access statistics and summary.
     *
     * @param  string  $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplateStats($templateId)
    {
        try {
            // Validate template exists
            $template = LogbookTemplate::findOrFail($templateId);

            // Get access statistics
            $totalUsers = UserLogbookAccess::where('logbook_template_id', $templateId)->count();
            
            $roleStats = UserLogbookAccess::where('logbook_template_id', $templateId)
                ->join('logbook_roles', 'user_logbook_access.logbook_role_id', '=', 'logbook_roles.id')
                ->groupBy('logbook_roles.id', 'logbook_roles.name')
                ->selectRaw('logbook_roles.id, logbook_roles.name, COUNT(*) as user_count')
                ->get();

            // Recent access grants (last 10)
            $recentAccess = UserLogbookAccess::with(['user:id,name,email', 'logbookRole:id,name'])
                ->where('logbook_template_id', $templateId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Template access statistics retrieved successfully',
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description
                ],
                'statistics' => [
                    'total_users' => $totalUsers,
                    'role_distribution' => $roleStats,
                    'recent_access' => $recentAccess
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve template statistics',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Display the specified user logbook access.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $access = UserLogbookAccess::with([
                'user:id,name,email',
                'logbookTemplate:id,name,description',
                'logbookRole:id,name,description'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'User logbook access retrieved successfully',
                'data' => $access
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User logbook access not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified user logbook access.
     * Authorization is handled by 'template.owner' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $access = UserLogbookAccess::findOrFail($id);
            
            // Authorization is handled by middleware

            // Validation rules - accept either user_id or user_email
            $validator = Validator::make($request->all(), [
                'user_id' => [
                    'sometimes',
                    'uuid',
                    'exists:users,id',
                    Rule::unique('user_logbook_access', 'user_id')
                        ->ignore($access->id)
                        ->where('logbook_template_id', $request->get('logbook_template_id', $access->logbook_template_id))
                ],
                'user_email' => [
                    'sometimes',
                    'email',
                    'exists:users,email'
                ],
                'logbook_template_id' => [
                    'sometimes',
                    'uuid',
                    'exists:logbook_template,id'
                ],
                'logbook_role_id' => [
                    'sometimes',
                    'integer',
                    'exists:logbook_roles,id'
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Convert email to user_id if email is provided
            $resolvedUserId = null;
            $userInfo = null;
            
            if ($request->has('user_email')) {
                $user = User::where('email', $request->user_email)->first();
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found with provided email',
                        'email' => $request->user_email
                    ], 404);
                }
                $resolvedUserId = $user->id;
                $userInfo = ['email' => $user->email, 'name' => $user->name];
                
                // Check for duplicate access with resolved user_id
                $duplicateCheck = UserLogbookAccess::where('user_id', $resolvedUserId)
                    ->where('logbook_template_id', $request->get('logbook_template_id', $access->logbook_template_id))
                    ->where('id', '!=', $access->id)
                    ->first();
                    
                if ($duplicateCheck) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User already has access to this template',
                        'user_info' => $userInfo
                    ], 409);
                }
            } else if ($request->has('user_id')) {
                $resolvedUserId = $request->user_id;
                $user = User::find($resolvedUserId);
                $userInfo = ['email' => $user->email, 'name' => $user->name];
            }

            // Update within transaction
            $updatedAccess = DB::transaction(function () use ($request, $access, $resolvedUserId, $userInfo) {
                $oldData = $access->toArray();

                // Update only provided fields
                if ($resolvedUserId) {
                    $access->user_id = $resolvedUserId;
                }
                if ($request->has('logbook_template_id')) {
                    $access->logbook_template_id = $request->logbook_template_id;
                }
                if ($request->has('logbook_role_id')) {
                    $access->logbook_role_id = $request->logbook_role_id;
                }

                $access->save();

                // Create audit log
                if (class_exists('\App\Models\AuditLog')) {
                    $changes = [];
                    
                    if ($resolvedUserId && $oldData['user_id'] !== $resolvedUserId) {
                        $oldUser = User::find($oldData['user_id']);
                        $newUserEmail = $userInfo ? $userInfo['email'] : 'Unknown';
                        $oldUserEmail = $oldUser ? $oldUser->email : 'Unknown';
                        $changes[] = "user: {$oldUserEmail} → {$newUserEmail}";
                    }
                    
                    foreach (['logbook_template_id', 'logbook_role_id'] as $field) {
                        if ($request->has($field) && $oldData[$field] !== $request->$field) {
                            $changes[] = "{$field}: {$oldData[$field]} → {$request->$field}";
                        }
                    }

                    if (!empty($changes)) {
                        AuditLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'UPDATE_TEMPLATE_ACCESS',
                            'description' => "Updated template access {$access->id}: " . implode(', ', $changes),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent()
                        ]);
                    }
                }

                return $access;
            });

            // Load relationships for response
            $updatedAccess->load(['user', 'logbookTemplate', 'logbookRole']);

            return response()->json([
                'success' => true,
                'message' => 'User logbook access updated successfully',
                'user_resolved' => $userInfo,
                'data' => $updatedAccess
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user logbook access',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user logbook access.
     * Authorization is handled by 'template.owner' middleware at route level.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $access = UserLogbookAccess::with(['user', 'logbookTemplate', 'logbookRole'])->findOrFail($id);
            
            // Authorization is handled by middleware
            
            $currentUser = Auth::user();
            
            // Prevent owner from deleting their own access
            if ($access->user_id === $currentUser->id && $access->logbookRole->name === 'Owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot remove your own owner access from the template',
                    'error_code' => 'CANNOT_REMOVE_SELF_OWNER'
                ], 403);
            }
            
            // Store info for audit log before deletion
            $userEmail = $access->user->email;
            $templateName = $access->logbookTemplate->name;
            $roleName = $access->logbookRole->name;

            DB::transaction(function () use ($access, $userEmail, $templateName, $roleName) {
                // Create audit log before deletion
                if (class_exists('\App\Models\AuditLog')) {
                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'REVOKE_TEMPLATE_ACCESS',
                        'description' => "Revoked '{$roleName}' access to template '{$templateName}' for user '{$userEmail}'",
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                }

                $access->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'User logbook access deleted successfully',
                'deleted_access' => [
                    'id' => $access->id,
                    'user' => $userEmail,
                    'template' => $templateName,
                    'role' => $roleName,
                    'revoked_by' => $currentUser->email
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user logbook access',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign access to multiple users for a template.
     * Authorization is handled by 'template.owner' middleware at route level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logbook_template_id' => [
                'required',
                'uuid',
                'exists:logbook_template,id'
            ],
            'users' => 'required|array|min:1',
            'users.*.user_id' => [
                'required_without:users.*.user_email',
                'uuid',
                'exists:users,id'
            ],
            'users.*.user_email' => [
                'required_without:users.*.user_id',
                'email',
                'exists:users,email'
            ],
            'users.*.logbook_role_id' => [
                'required',
                'integer',
                'exists:logbook_roles,id'
            ]
        ], [
            'users.*.user_id.required_without' => 'Each user must have either user_id or user_email',
            'users.*.user_email.required_without' => 'Each user must have either user_id or user_email',
            'users.*.user_email.exists' => 'One or more users not found with provided email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Authorization is handled by middleware

        try {
            $results = DB::transaction(function () use ($request) {
                $created = [];
                $skipped = [];
                $errors = [];

                foreach ($request->users as $index => $userData) {
                    // Resolve user_id from email if provided
                    $userId = null;
                    $userInfo = null;
                    
                    if (isset($userData['user_email']) && !isset($userData['user_id'])) {
                        $user = User::where('email', $userData['user_email'])->first();
                        if (!$user) {
                            $errors[] = [
                                'index' => $index,
                                'user_email' => $userData['user_email'],
                                'reason' => 'User not found with provided email'
                            ];
                            continue;
                        }
                        $userId = $user->id;
                        $userInfo = ['email' => $user->email, 'name' => $user->name];
                    } else if (isset($userData['user_id'])) {
                        $userId = $userData['user_id'];
                        $user = User::find($userId);
                        $userInfo = $user ? ['email' => $user->email, 'name' => $user->name] : null;
                    }

                    if (!$userId) {
                        $errors[] = [
                            'index' => $index,
                            'reason' => 'Unable to resolve user_id'
                        ];
                        continue;
                    }

                    // Check if access already exists
                    $existingAccess = UserLogbookAccess::where('user_id', $userId)
                                                      ->where('logbook_template_id', $request->logbook_template_id)
                                                      ->first();

                    if ($existingAccess) {
                        $skipped[] = [
                            'user_info' => $userInfo,
                            'user_id' => $userId,
                            'reason' => 'Access already exists'
                        ];
                        continue;
                    }

                    // Create new access
                    $access = UserLogbookAccess::create([
                        'user_id' => $userId,
                        'logbook_template_id' => $request->logbook_template_id,
                        'logbook_role_id' => $userData['logbook_role_id'],
                    ]);

                    $createdItem = $access->load(['user', 'logbookRole', 'logbookTemplate']);
                    $createdItem->user_resolved = $userInfo;
                    $created[] = $createdItem;

                    // Fire event for realtime notification
                    event(new \App\Events\LogbookAccessGranted(
                        $access->user,           // User who received access
                        Auth::user(),           // User who granted access
                        $access->logbookTemplate, // Template that was shared
                        $access->logbookRole->name // Role name
                    ));
                }

                // Create audit log
                if (class_exists('\App\Models\AuditLog') && !empty($created)) {
                    $emailList = collect($created)->pluck('user.email')->implode(', ');
                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'BULK_GRANT_TEMPLATE_ACCESS',
                        'description' => "Bulk granted access to template {$request->logbook_template_id} for " . count($created) . " users: {$emailList}",
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                }

                return compact('created', 'skipped', 'errors');
            });

            $statusCode = 201;
            $message = 'Bulk user logbook access operation completed';

            // If there are errors, change status and message
            if (!empty($results['errors'])) {
                $statusCode = 422;
                $message = 'Bulk operation completed with some errors';
            }

            return response()->json([
                'success' => empty($results['errors']),
                'message' => $message,
                'created_count' => count($results['created']),
                'skipped_count' => count($results['skipped']),
                'error_count' => count($results['errors']),
                'created' => $results['created'],
                'skipped' => $results['skipped'],
                'errors' => $results['errors']
            ], $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bulk user logbook access',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}