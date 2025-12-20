<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * FCM Token Controller
 * 
 * Manages FCM device tokens for push notifications
 */
class FcmTokenController extends Controller
{
    /**
     * Store a new FCM token or update existing one
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:255',
            'device_type' => 'required|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $userId = Auth::id();
            $token = $request->input('token');

            // Check if token already exists
            $fcmToken = FcmToken::where('token', $token)->first();

            if ($fcmToken) {
                // Update existing token
                $fcmToken->update([
                    'user_id' => $userId,
                    'device_type' => $request->input('device_type'),
                    'device_name' => $request->input('device_name'),
                    'app_version' => $request->input('app_version'),
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'FCM token updated successfully',
                    'data' => $fcmToken,
                ]);
            }

            // Create new token
            $fcmToken = FcmToken::create([
                'user_id' => $userId,
                'token' => $token,
                'device_type' => $request->input('device_type'),
                'device_name' => $request->input('device_name'),
                'app_version' => $request->input('app_version'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token registered successfully',
                'data' => $fcmToken,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register FCM token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all active FCM tokens for the authenticated user
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $tokens = FcmToken::forUser(Auth::id())
                ->active()
                ->orderBy('last_used_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tokens,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve FCM tokens',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing FCM token
     *
     * @param Request $request
     * @param string $token
     * @return JsonResponse
     */
    public function update(Request $request, string $token): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $fcmToken = FcmToken::where('token', $token)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $fcmToken->update($request->only([
                'device_name',
                'app_version',
                'is_active',
            ]));

            if ($request->has('is_active') && $request->boolean('is_active')) {
                $fcmToken->markAsUsed();
            }

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully',
                'data' => $fcmToken,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'FCM token not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update FCM token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate an FCM token
     *
     * @param string $token
     * @return JsonResponse
     */
    public function destroy(string $token): JsonResponse
    {
        try {
            $fcmToken = FcmToken::where('token', $token)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $fcmToken->deactivate();

            return response()->json([
                'success' => true,
                'message' => 'FCM token deactivated successfully',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'FCM token not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate FCM token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete all inactive tokens for the authenticated user
     *
     * @return JsonResponse
     */
    public function clean(): JsonResponse
    {
        try {
            $deleted = FcmToken::forUser(Auth::id())
                ->where('is_active', false)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Deleted {$deleted} inactive tokens",
                'deleted_count' => $deleted,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clean inactive tokens',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
