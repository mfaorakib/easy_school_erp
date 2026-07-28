<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (like Heroku/Fly) terminates TLS at its own edge and
        // forwards plain HTTP to this container - the container is never
        // reachable except through that edge, so trusting every proxy is
        // the standard, safe setting here. Without this, TrustProxies
        // ignores Render's X-Forwarded-Proto: https header, Laravel thinks
        // every request is plain HTTP, and every url()/form action it
        // generates comes out as http:// even though the browser loaded
        // the page over https:// - which is exactly what trips Chrome's
        // "not secure" form-submission warning.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

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
