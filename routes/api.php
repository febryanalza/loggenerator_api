<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LogbookTemplateController;
use App\Http\Controllers\Api\LogbookFieldController;
use App\Http\Controllers\Api\LogbookDataController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\NotificationController;
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

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Logbook Template routes
    Route::post('/templates', [LogbookTemplateController::class, 'store']);
    
    // Logbook Field routes
    Route::post('/fields', [LogbookFieldController::class, 'store']);
    Route::post('/fields/batch', [LogbookFieldController::class, 'storeBatch']);
    
    // Logbook Data routes
    Route::post('/logbook-entries', [LogbookDataController::class, 'store']);
    
    // Permission routes
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::post('/permissions/batch', [PermissionController::class, 'storeBatch']);
    
    // Notification routes
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::post('/notifications/send-to-users', [NotificationController::class, 'sendToMultipleUsers']);
    Route::post('/notifications/send-to-role', [NotificationController::class, 'sendToRole']);
});