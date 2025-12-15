<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleAuthService;
use App\Helpers\GoogleAuthHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthController extends Controller
{

    /**
     * Handle user registration request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email|max:150',
            'password' => 'required|min:8|confirmed',
            'phone_number' => 'nullable|string|max:20',
            'device_name' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'status' => 'active', // Default status
            'last_login' => now(),
        ]);

        // Ensure user has default 'User' role (fallback if trigger fails)
        if (!$user->hasRole('User')) {
            $user->assignRole('User');
        }

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'REGISTER',
            'description' => 'User registered successfully - Email verification sent',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Create token
        $deviceName = $request->device_name ?? ($request->userAgent() ?? 'unknown');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email to verify your account.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'email_verified' => false,
                ],
                'token' => $token,
                'verification_sent' => true
            ]
        ], 201);
    }

    /**
     * Handle user login request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Update last login timestamp
        $user->last_login = now();
        $user->save();
        
        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'LOGIN',
            'description' => 'User logged in successfully',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Create token
        $deviceName = $request->device_name ?? ($request->userAgent() ?? 'unknown');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                ],
                'token' => $token
            ]
        ]);
    }

    /**
     * Handle user logout request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        
        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'LOGOUT',
            'description' => 'User logged out successfully',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Handle Google authentication
     *
     * @param Request $request
     * @param GoogleAuthService $googleAuthService
     * @return \Illuminate\Http\JsonResponse
     */
    public function googleLogin(Request $request, GoogleAuthService $googleAuthService)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
            'device_name' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify Google ID token and get user data
            $googleUserData = $googleAuthService->verifyIdToken($request->id_token);
            
            if (!$googleUserData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google ID token'
                ], 401);
            }

            // Check if user already exists by Google ID
            $user = User::where('google_id', $googleUserData['google_id'])->first();
            
            if (!$user) {
                // Check if user exists by email
                $user = User::where('email', $googleUserData['email'])->first();
                
                if ($user) {
                    // Link existing user with Google account
                    $updateData = [
                        'google_id' => $googleUserData['google_id'],
                        'auth_provider' => 'google',
                        'google_verified_at' => now(),
                        'email_verified_at' => $googleUserData['email_verified'] ? now() : $user->email_verified_at,
                    ];
                    
                    // Only set Google avatar if user doesn't have a custom one
                    $hasCustomAvatar = $user->avatar_url && str_contains($user->avatar_url, 'storage/avatars/');
                    if (!$hasCustomAvatar) {
                        $updateData['avatar_url'] = $googleUserData['avatar_url'];
                    }
                    
                    // Set random password if user doesn't have one
                    if (GoogleAuthHelper::needsRandomPassword($user->password, 'google')) {
                        $updateData['password'] = Hash::make(GoogleAuthHelper::generateGoogleUserPassword());
                    }
                    
                    $user->update($updateData);
                    
                    $action = 'GOOGLE_LINK';
                    $description = 'Existing user linked with Google account';
                } else {
                    // Create new user with Google data
                    $user = User::create([
                        'name' => $googleUserData['name'],
                        'email' => $googleUserData['email'],
                        'password' => Hash::make(GoogleAuthHelper::generateGoogleUserPassword()),
                        'google_id' => $googleUserData['google_id'],
                        'avatar_url' => $googleUserData['avatar_url'],
                        'auth_provider' => 'google',
                        'google_verified_at' => now(),
                        'email_verified_at' => $googleUserData['email_verified'] ? now() : null,
                        'status' => 'active',
                        'last_login' => now(),
                    ]);

                    // Ensure user has default 'User' role
                    if (!$user->hasRole('User')) {
                        $user->assignRole('User');
                    }
                    
                    $action = 'GOOGLE_REGISTER';
                    $description = 'New user registered via Google authentication';
                }
            } else {
                // Update existing Google user data
                // Only update avatar_url if user doesn't have a custom avatar (stored in our server)
                $updateData = [
                    'last_login' => now(),
                ];
                
                // Only update avatar if user hasn't set a custom one
                // Custom avatars are stored in our storage (contain 'storage/avatars/')
                $hasCustomAvatar = $user->avatar_url && str_contains($user->avatar_url, 'storage/avatars/');
                if (!$hasCustomAvatar) {
                    $updateData['avatar_url'] = $googleUserData['avatar_url'];
                }
                
                $user->update($updateData);
                
                $action = 'GOOGLE_LOGIN';
                $description = 'User logged in via Google authentication';
            }

            // Create audit log with platform information
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'description' => $description . ' (Platform: ' . ($googleUserData['client_platform'] ?? 'unknown') . ')',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Create token
            $deviceName = $request->device_name ?? ($request->userAgent() ?? 'unknown');
            $token = $user->createToken($deviceName)->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google authentication successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'avatar_url' => $user->avatar_url,
                        'auth_provider' => $user->auth_provider,
                        'status' => $user->status,
                    ],
                    'token' => $token
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Google account unlinking
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlinkGoogle(Request $request)
    {
        $user = $request->user();
        
        // Check if user has password for fallback authentication
        if (!$user->password && $user->auth_provider === 'google') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot unlink Google account. Please set a password first for alternative login method.'
            ], 400);
        }

        // Remove Google authentication data
        $user->update([
            'google_id' => null,
            'google_verified_at' => null,
            'auth_provider' => $user->password ? 'email' : 'google', // Keep google if no password
        ]);

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'GOOGLE_UNLINK',
            'description' => 'User unlinked Google account',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Google account unlinked successfully'
        ]);
    }

    /**
     * Resend email verification notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerification(Request $request)
    {
        $user = $request->user();

        // Check if email already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        // Check if user registered via Google (skip verification)
        if ($user->auth_provider === 'google' && $user->google_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Google authenticated users do not need email verification'
            ], 400);
        }

        // Send verification email
        $user->sendEmailVerificationNotification();

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'EMAIL_VERIFICATION_RESEND',
            'description' => 'Email verification link resent',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification email has been resent'
        ]);
    }

    /**
     * Verify email address
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        // Verify hash matches
        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link'
            ], 400);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified',
                'data' => [
                    'verified_at' => $user->email_verified_at
                ]
            ]);
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'EMAIL_VERIFIED',
                'description' => 'Email address verified successfully',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => [
                    'verified_at' => $user->email_verified_at
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email verification failed'
        ], 500);
    }

    /**
     * Get current user verification status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificationStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $user->email,
                'email_verified' => !is_null($user->email_verified_at),
                'email_verified_at' => $user->email_verified_at,
                'auth_provider' => $user->auth_provider,
                'google_verified' => !is_null($user->google_verified_at),
            ]
        ]);
    }
}