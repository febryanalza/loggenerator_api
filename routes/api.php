<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LogbookTemplateController;
use App\Http\Controllers\Api\LogbookFieldController;
use App\Http\Controllers\Api\LogbookDataController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Public file access
Route::get('/images/logbook/{filename}', [FileController::class, 'getLogbookImage']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Logbook Template routes
    Route::get('/templates', [LogbookTemplateController::class, 'index']);
    Route::post('/templates', [LogbookTemplateController::class, 'store']);
    Route::get('/templates/{id}', [LogbookTemplateController::class, 'show']);
    Route::put('/templates/{id}', [LogbookTemplateController::class, 'update']);
    Route::delete('/templates/{id}', [LogbookTemplateController::class, 'destroy']);
    Route::get('/templates/{templateId}/fields', [LogbookFieldController::class, 'getFieldsByTemplate']);
    
    // Logbook Field routes
    Route::post('/fields', [LogbookFieldController::class, 'store']);
    Route::post('/fields/batch', [LogbookFieldController::class, 'storeBatch']);
    Route::put('/fields/{id}', [LogbookFieldController::class, 'update']);
    Route::delete('/fields/{id}', [LogbookFieldController::class, 'destroy']);
    
    // Logbook Data routes
    Route::get('/logbook-entries', [LogbookDataController::class, 'index']);
    Route::post('/logbook-entries', [LogbookDataController::class, 'store']);
    Route::get('/logbook-entries/{id}', [LogbookDataController::class, 'show']);
    Route::put('/logbook-entries/{id}', [LogbookDataController::class, 'update']);
    Route::delete('/logbook-entries/{id}', [LogbookDataController::class, 'destroy']);
    
    // Permission routes
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::post('/permissions/batch', [PermissionController::class, 'storeBatch']);
    
    // Notification routes
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::post('/notifications/send-to-users', [NotificationController::class, 'sendToMultipleUsers']);
    Route::post('/notifications/send-to-role', [NotificationController::class, 'sendToRole']);
    
    // File upload routes
    Route::post('/upload/image', [FileController::class, 'uploadImage']);
});