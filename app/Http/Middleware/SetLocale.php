<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if language is passed in query string (e.g., ?lang=id)
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            
            // Validate locale
            if (in_array($locale, ['en', 'id'])) {
                Session::put('locale', $locale);
                App::setLocale($locale);
            }
        } 
        // Otherwise check session
        elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }
        // Fallback to config default
        else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
