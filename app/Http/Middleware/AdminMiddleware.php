<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via Sanctum Bearer Token
        if (!Auth::guard('sanctum')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please provide valid Bearer token.'
                ], 401);
            }
            
            return redirect()->route('admin.login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::guard('sanctum')->user();
        
        // Check if user has admin role using explicit isAdmin() method
        // This method is defined in User model for better IDE support
        if (!$user->isAdmin()) {
            // Log unauthorized access attempt
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'ADMIN_ACCESS_DENIED',
                'description' => 'User without admin privileges attempted to access admin area',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.'
                ], 403);
            }
            
            // Redirect regular users to home page
            return redirect()->route('home')->with('error', 'Access denied. You do not have admin privileges.');
        }

        return $next($request);
    }
}