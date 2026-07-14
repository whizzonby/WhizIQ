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
        // Production runs behind AWS's load balancer, which terminates TLS and
        // forwards plain HTTP to this instance. Without trusting its
        // X-Forwarded-* headers, Laravel misjudges the request scheme/host,
        // which breaks secure-cookie detection and signed URL validation
        // (e.g. email verification links) since those are checked against
        // the full absolute URL including scheme.
        $middleware->trustProxies(at: '*', headers: Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB);

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
