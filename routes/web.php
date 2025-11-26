<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\LogbookTemplateController;
use Illuminate\Http\Request;

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\DashboardController;

Route::get('/', [HomeController::class, 'index'])->name('home');

        // Test routes
Route::get('/test-login', function() {
    return view('test-login');
});
Route::get('/csrf-debug', function() {
    return view('csrf-debug');
});
Route::get('/bearer-test', function() {
    return view('bearer-test');
});// Test direct login API
Route::post('/test-direct-login', function(Illuminate\Http\Request $request) {
    Log::info('Direct login test called', $request->all());
    
    return response()->json([
        'success' => true,
        'message' => 'Test endpoint reached',
        'data' => $request->all()
    ]);
});

// Admin Authentication Routes (No Auth Required - View Only)
Route::prefix('admin')->group(function () {
    // Access gate - public landing page
    Route::get('/access', function() { return view('admin.access-gate'); })->name('admin.access');
    
    // Single unified login page with Bearer token authentication
    Route::get('/login', function() { return view('admin.login'); })->name('admin.login');
    
    // Dashboard with sidebar layout - checks Bearer token via JavaScript
    Route::get('/', function() { return view('admin.dashboard'); })->name('admin.dashboard');
    Route::get('/dashboard', function() { return view('admin.dashboard'); })->name('admin.dashboard.main');
    
    // Admin pages
    Route::get('/user-management', function() { return view('admin.user-management'); })->name('admin.user-management');
    Route::get('/institution-management', function() { return view('admin.institution-management'); })->name('admin.institution-management');
    Route::get('/logbook-management', function() { return view('admin.logbook-management'); })->name('admin.logbook-management');
    Route::get('/content-management', function() { return view('admin.content-management'); })->name('admin.content-management');
    Route::get('/transactions', function() { return view('admin.transactions'); })->name('admin.transactions');
    Route::get('/logbook/{id}', function() { return view('admin.logbook-detail'); })->name('admin.logbook-detail');
});

// Note: All admin API endpoints (login, logout, stats, etc.) are now in routes/api.php
// This prevents CSRF token issues when using Bearer token authentication from external clients like Postman

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
