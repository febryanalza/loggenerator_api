<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\AuditLog;

class AdminAuthController extends Controller
{
    /**
     * Get token expiration hours from config
     *
     * @return int
     */
    private function getTokenExpirationHours(): int
    {
        return (int) config('admin.token_expiration_hours', 4);
    }

    /**
     * Get admin roles from config
     *
     * @return array
     */
    private function getAdminRoles(): array
    {
        return config('admin.roles', ['Admin', 'Super Admin', 'Manager', 'Institution Admin']);
    }

    /**
     * Handle admin login request (Bearer Token)
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

        // Check if user has admin privileges
        if (!$this->isAdminUser($user)) {
            // Create audit log for unauthorized access attempt
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'ADMIN_LOGIN_DENIED',
                'description' => 'User attempted to access admin dashboard without proper permissions',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have admin privileges.'
            ], 403);
        }

        // Update last login timestamp
        $user->last_login = now();
        $user->save();
        
        // Create audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'ADMIN_LOGIN',
            'description' => 'Admin user logged in successfully',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Create token with expiration for admin dashboard
        // Mobile app tokens (created via AuthController) have no expiration
        $deviceName = $request->device_name ?? ($request->userAgent() ?? 'Admin Dashboard');
        $tokenExpirationHours = $this->getTokenExpirationHours();
        $expiresAt = now()->addHours($tokenExpirationHours);
        $token = $user->createToken($deviceName, ['admin-dashboard'], $expiresAt)->plainTextToken;

        // Load institution relationship if exists
        $institution = null;
        if ($user->institution_id) {
            $user->load('institution');
            $institution = $user->institution ? [
                'id' => $user->institution->id,
                'name' => $user->institution->name,
            ] : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'roles' => $user->getRoleNames(),
                    'institution_id' => $user->institution_id,
                    'institution' => $institution,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toIso8601String(),
                'expires_in_seconds' => $tokenExpirationHours * 60 * 60,
            ]
        ]);
    }

    /**
     * Refresh admin token - extends session for another 4 hours
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        
        // Delete current token
        $user->currentAccessToken()->delete();
        
        // Create new token with fresh expiration
        $deviceName = $request->device_name ?? ($request->userAgent() ?? 'Admin Dashboard');
        $tokenExpirationHours = $this->getTokenExpirationHours();
        $expiresAt = now()->addHours($tokenExpirationHours);
        $token = $user->createToken($deviceName, ['admin-dashboard'], $expiresAt)->plainTextToken;
        
        // Create audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'ADMIN_TOKEN_REFRESH',
            'description' => 'Admin token refreshed',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toIso8601String(),
                'expires_in_seconds' => $tokenExpirationHours * 60 * 60,
            ]
        ]);
    }

    /**
     * Check if user has admin permissions
     */
    private function isAdminUser(User $user): bool
    {
        // Check if user has any admin-level permission
        // Super Admin & Admin permissions
        if ($user->can('system.admin') || $user->can('users.manage')) {
            return true;
        }
        
        // Institution Admin permissions
        if ($user->can('institution.manage-members') 
            || $user->can('institution.view-members')
            || $user->can('institution.update-settings')) {
            return true;
        }
        
        // Manager permissions
        if ($user->can('logbooks.export.manage') 
            || $user->can('templates.manage')
            || $user->can('users.view.all')) {
            return true;
        }
        
        return false;
    }

    /**
     * Handle admin logout request
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        
        // Create audit log
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'ADMIN_LOGOUT',
            'description' => 'Admin user logged out successfully',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current admin user info
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        if (!$user || !$this->isAdminUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Load institution relationship if exists
        $institution = null;
        if ($user->institution_id) {
            $user->load('institution');
            $institution = $user->institution ? [
                'id' => $user->institution->id,
                'name' => $user->institution->name,
            ] : null;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'roles' => $user->getRoleNames(),
                    'last_login' => $user->last_login,
                    'avatar_url' => $user->avatar_url,
                    'institution_id' => $user->institution_id,
                    'institution' => $institution,
                ]
            ]
        ]);
    }
}