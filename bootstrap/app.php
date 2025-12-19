<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register SetLocale middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        // Register custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class, // DEPRECATED: Use 'permission' instead
            'permission' => \App\Http\Middleware\CheckPermission::class, // ENHANCED: Now uses Spatie built-in methods
            'logbook.access' => \App\Http\Middleware\CheckLogbookAccess::class, // Unchanged: Data-level access
            'template.owner' => \App\Http\Middleware\CheckTemplateOwnership::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'token.expiration' => \App\Http\Middleware\CheckTokenExpiration::class,
            'throttle.sensitive' => \App\Http\Middleware\SensitiveEndpointThrottle::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
