<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * Check if the current token has expired and return appropriate response.
     * This middleware should be applied after auth:sanctum middleware.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Get current access token
        $token = $user->currentAccessToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found',
                'error_code' => 'TOKEN_NOT_FOUND'
            ], 401);
        }

        // Check if token has expiration and is expired
        if ($token->expires_at && $token->expires_at->isPast()) {
            // Store expired_at before deleting token
            $expiredAt = $token->expires_at->toIso8601String();
            
            // Delete the expired token
            $token->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Your session has expired. Please login again.',
                'error_code' => 'TOKEN_EXPIRED',
                'expired_at' => $expiredAt
            ], 401);
        }

        return $next($request);
    }
}
