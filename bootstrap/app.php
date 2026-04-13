<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'license'     => \App\Http\Middleware\CheckLicense::class,
            'owner'       => \App\Http\Middleware\IsOwner::class,
            'super_admin' => \App\Http\Middleware\IsSuperAdmin::class,
        ]);
        $middleware->append(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
