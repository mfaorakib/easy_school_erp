<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Every web request resolves the active UI language.
        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Route-level role/permission gating (e.g. the Guardian Portal is
        // role:parent-only; the new Student/Staff Profile pages are the first
        // routes in the app to use 'permission' — see PermissionRegistry's
        // own docblock, which had explicitly deferred real enforcement).
        $middleware->alias([
            'role'       => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
