<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Illuminate\Foundation\Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // DNS for whizziq.com resolves directly to this server (no ALB or
        // reverse proxy in front) and Apache terminates TLS itself, so
        // Laravel already sees the real scheme/host without any help.
        // Trusting X-Forwarded-* headers here would let a client spoof them
        // directly, misleading secure-cookie detection and breaking session
        // persistence, CSRF, and OAuth state checks - do not re-add this
        // without confirming there is an actual proxy terminating TLS in
        // front of this instance.

        $middleware->appendToGroup('web', [
            App\Http\Middleware\BlockedUser::class,
            App\Http\Middleware\UpdateUserLastSeenAt::class,
            App\Http\Middleware\SecureHeadersMiddleware::class,
        ]);

        $middleware->alias([
            'sitemapped' => \App\Http\Middleware\Sitemapped::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {})->create();
