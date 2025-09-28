<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LogbookTemplateController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Test route untuk generate token
Route::get('/test-token', function () {
    $user = \App\Models\User::first();
    if (!$user) {
        return response()->json(['error' => 'No users found']);
    }
    
    $token = $user->createToken('postman-test')->plainTextToken;
    
    return response()->json([
        'user' => $user->email,
        'token' => $token,
        'note' => 'Copy token ini ke Postman Authorization header'
    ]);
});

// Test route untuk create template dan verify auto access creation
Route::get('/test-template-creation', function () {
    $user = \App\Models\User::first();
    if (!$user) {
        return response()->json(['error' => 'No users found']);
    }
    
    // Login as user
    \Illuminate\Support\Facades\Auth::login($user);
    
    // Create template
    $template = \App\Models\LogbookTemplate::create([
        'name' => 'Test Template ' . now()->format('H:i:s'),
        'description' => 'Auto-generated test template'
    ]);
    
    // Check if user access was created
    $access = \App\Models\UserLogbookAccess::where('logbook_template_id', $template->id)
                                          ->where('user_id', $user->id)
                                          ->first();
    
    return response()->json([
        'template_created' => $template,
        'user_access_created' => $access ? true : false,
        'access_details' => $access,
        'message' => $access ? 'SUCCESS: Auto access creation working!' : 'FAILED: No access created'
    ]);
});

// Test route untuk verify user access API
Route::get('/test-user-access-api', function () {
    $user = \App\Models\User::first();
    if (!$user) {
        return response()->json(['error' => 'No users found']);
    }
    
    // Get all user access
    $allAccess = \App\Models\UserLogbookAccess::with(['user', 'logbookTemplate', 'logbookRole'])->get();
    
    // Get specific user access
    $userAccess = \App\Models\UserLogbookAccess::where('user_id', $user->id)
                                              ->with(['logbookTemplate', 'logbookRole'])
                                              ->get();
    
    return response()->json([
        'total_access_records' => $allAccess->count(),
        'user_access_count' => $userAccess->count(),
        'user_email' => $user->email,
        'user_templates' => $userAccess->map(function($access) {
            return [
                'template_name' => $access->logbookTemplate->name,
                'role_name' => $access->logbookRole->name,
                'granted_at' => $access->created_at
            ];
        }),
        'api_routes_available' => [
            'GET /api/user-access',
            'POST /api/user-access', 
            'PUT /api/user-access/{id}',
            'DELETE /api/user-access/{id}',
            'POST /api/user-access/bulk'
        ]
    ]);
});
