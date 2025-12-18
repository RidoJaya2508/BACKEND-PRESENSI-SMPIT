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
        // Session-based authentication is handled via 'web' middleware group
        // which is already applied to API routes in routes/api.php
        
        // Exclude all API routes from CSRF protection as the frontend is a stateful SPA
        // that relies on session-based auth but doesn't handle CSRF tokens.
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'telegram/webhook', // Explicitly exclude webhook
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
