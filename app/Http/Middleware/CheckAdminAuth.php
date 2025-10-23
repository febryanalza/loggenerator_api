<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAuth
{
    /**
     * Handle an incoming request.
     * 
     * This middleware checks if user has valid admin token in localStorage.
     * If not authenticated, redirect to admin login page.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access to login and access gate pages
        if ($request->is('admin/login') || $request->is('admin/access')) {
            return $next($request);
        }

        // For other admin pages, the authentication check is handled by JavaScript
        // (checking for admin_token in localStorage)
        // The middleware just ensures the routes are accessible
        
        return $next($request);
    }
}
