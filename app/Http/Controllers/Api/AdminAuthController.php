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

        // Create token
        $deviceName = $request->device_name ?? ($request->userAgent() ?? 'Admin Dashboard');
        $token = $user->createToken($deviceName)->plainTextToken;

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
                'token' => $token
            ]
        ]);
    }



    /**
     * Check if user has admin roles
     */
    private function isAdminUser(User $user): bool
    {
        $adminRoles = ['Admin', 'Super Admin', 'Manager', 'Institution Admin'];
        
        foreach ($adminRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
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