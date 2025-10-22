<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LogbookTemplateController;
use App\Http\Controllers\Api\LogbookFieldController;
use App\Http\Controllers\Api\LogbookDataController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\UserLogbookAccessController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\LogbookVerificationController;
use App\Http\Controllers\Api\InstitutionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// Public file access
Route::get('/images/logbook/{filename}', [FileController::class, 'getLogbookImage']);
Route::get('/images/avatar/{filename}', [FileController::class, 'getAvatarImage']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Broadcasting authentication
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/google/unlink', [AuthController::class, 'unlinkGoogle']);
    
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // User profile management
    Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::delete('/profile/picture', [\App\Http\Controllers\Api\ProfileController::class, 'deleteProfilePicture']);
    
    // Institution routes - Public access for selection (name and id only)
    Route::get('/institutions', [\App\Http\Controllers\Api\InstitutionController::class, 'index']);
    
    // Institution routes - Admin management (full CRUD operations)
    Route::middleware('role:Super Admin,Admin,Manager')->group(function () {
        Route::get('/institutions/details', [\App\Http\Controllers\Api\InstitutionController::class, 'getAllDetails']);
        Route::get('/institutions/{id}', [\App\Http\Controllers\Api\InstitutionController::class, 'show']);
        Route::post('/institutions', [\App\Http\Controllers\Api\InstitutionController::class, 'store']);
        Route::put('/institutions/{id}', [\App\Http\Controllers\Api\InstitutionController::class, 'update']);
        Route::delete('/institutions/{id}', [\App\Http\Controllers\Api\InstitutionController::class, 'destroy']);
    });
    
    // Logbook Template routes - Basic access for authenticated users
    Route::get('/templates', [LogbookTemplateController::class, 'index']);
    Route::get('/templates/user', [LogbookTemplateController::class, 'getUserTemplates']);
    Route::get('/templates/user/permissions', [LogbookTemplateController::class, 'getUserTemplatesWithPermissions']);
    Route::get('/templates/user/{id}', [LogbookTemplateController::class, 'getUserTemplate']);
    Route::get('/templates/{id}', [LogbookTemplateController::class, 'show']);
    Route::get('/templates/{templateId}/fields', [LogbookFieldController::class, 'getFieldsByTemplate']);
    
    // Template creation - All authenticated users can create templates
    Route::post('/templates', [LogbookTemplateController::class, 'store']);
    
    // Template modification - Owner only (template owners, admins, or super admins)
    Route::middleware('template.owner')->group(function () {
        Route::put('/templates/{id}', [LogbookTemplateController::class, 'update']);
        Route::delete('/templates/{id}', [LogbookTemplateController::class, 'destroy']);
    });
    
    // User Logbook Access routes - View operations
    Route::get('/user-access', [UserLogbookAccessController::class, 'index']);
    Route::get('/user-access/template/{templateId}', [UserLogbookAccessController::class, 'getByTemplate']);
    Route::get('/user-access/template/{templateId}/stats', [UserLogbookAccessController::class, 'getTemplateStats']);
    Route::get('/user-access/{id}', [UserLogbookAccessController::class, 'show']);
    
    // User Logbook Access routes - Modification operations (Template Owner only)
    Route::middleware('logbook.access:Owner')->group(function () {
        Route::post('/user-access', [UserLogbookAccessController::class, 'store']);
        Route::post('/user-access/bulk', [UserLogbookAccessController::class, 'bulkStore']);
        Route::put('/user-access/{id}', [UserLogbookAccessController::class, 'update']);
        Route::delete('/user-access/{id}', [UserLogbookAccessController::class, 'destroy']);
    });

    // Logbook Verification routes
    Route::put('/logbook/verification', [LogbookVerificationController::class, 'updateVerificationStatus']);
    Route::get('/logbook/verification/{templateId}', [LogbookVerificationController::class, 'getVerificationStatus']);
    
    // Logbook Assessment routes (Institution Admin only)
    Route::middleware('role:Institution Admin')->group(function () {
        Route::put('/logbook/assessment', [LogbookVerificationController::class, 'updateAssessmentStatus']);
    });
    
    // Logbook Field routes - Template management
    Route::middleware('permission:manage templates')->group(function () {
        Route::post('/fields', [LogbookFieldController::class, 'store']);
        Route::post('/fields/batch', [LogbookFieldController::class, 'storeBatch']);
        Route::put('/fields/{id}', [LogbookFieldController::class, 'update']);
        Route::delete('/fields/{id}', [LogbookFieldController::class, 'destroy']);
    });
    
    // Logbook Data routes - View operations (requires template access)
    Route::get('/logbook-entries', [LogbookDataController::class, 'index']);
    Route::get('/logbook-entries/template/{templateId}', [LogbookDataController::class, 'fetchByTemplate']);
    Route::get('/logbook-entries/template/{templateId}/summary', [LogbookDataController::class, 'getTemplateSummary']);
    Route::get('/logbook-entries/{id}', [LogbookDataController::class, 'show']);
    
    // Logbook Data routes - Modification operations (requires Editor+ role)
    Route::middleware('logbook.access:Editor,Supervisor,Owner')->group(function () {
        Route::post('/logbook-entries', [LogbookDataController::class, 'store']);
        Route::put('/logbook-entries/{id}', [LogbookDataController::class, 'update']);
    });
    
    // Logbook Data routes - Deletion (requires Editor+ role)
    Route::middleware('logbook.access:Editor,Supervisor,Owner')->group(function () {
        Route::delete('/logbook-entries/{id}', [LogbookDataController::class, 'destroy']);
    });
    
    // Permission routes - View access for Admin+, Create operations Super Admin only
    Route::middleware('role:Super Admin, Admin')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    });
    
    // Permission creation routes - Super Admin only (critical system operations)
    Route::middleware('role:Super Admin')->group(function () {
        Route::post('/permissions', [PermissionController::class, 'store']);
        Route::post('/permissions/batch', [PermissionController::class, 'storeBatch']);
    });
    
    // Role-Permission assignment routes - Admin+ can manage role permissions
    Route::middleware('role:Super Admin,Admin')->group(function () {
        Route::post('/permissions/assign-to-role', [PermissionController::class, 'assignToRole']);
        Route::post('/permissions/revoke-from-role', [PermissionController::class, 'revokeFromRole']);
    });
    
    // Role management routes - Admin+ can view and manage roles (but not create new roles)
    Route::middleware('role:Super Admin,Admin')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::get('/roles/{id}/users', [RoleController::class, 'getRoleUsers']);
        Route::post('/roles/assign-permissions', [RoleController::class, 'assignPermissions']);
        Route::post('/roles/revoke-permissions', [RoleController::class, 'revokePermissions']);
    });
    
    // Notification routes - All authenticated users can view their notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/stats', [NotificationController::class, 'stats']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Notification management - Admin+ role only
    Route::middleware('role:Super Admin,Admin,Manager')->group(function () {
        Route::post('/notifications/send', [NotificationController::class, 'send']);
        Route::post('/notifications/send-to-role', [NotificationController::class, 'sendToRole']);
    });
    
    // File upload routes
    Route::post('/upload/image', [FileController::class, 'uploadImage']);
    
    // Admin only routes
    Route::middleware('role:Super Admin,Admin')->group(function () {
        // User management - accessible by Super Admin and Admin
        Route::post('/admin/users', [UserManagementController::class, 'createUser']);
        Route::get('/admin/users', [UserManagementController::class, 'getUsers']);
        Route::put('/admin/users/{userId}/role', [UserManagementController::class, 'updateUserRole']);
        
        // System management routes
        // Route::get('/admin/system-info', [SystemController::class, 'info']);
        // Route::post('/admin/maintenance', [SystemController::class, 'maintenance']);
    });
    
    // Super Admin only routes
    Route::middleware('role:Super Admin')->group(function () {
        // Critical system operations only for Super Admin
        // Route::delete('/admin/purge-data', [SystemController::class, 'purgeData']);
        // Route::post('/admin/reset-permissions', [SystemController::class, 'resetPermissions']);
    });
});