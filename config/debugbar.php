<?php

return [
    'enabled' => env('DEBUGBAR_ENABLED', env('APP_DEBUG', false)),
    'except' => [
        'api/*',
        'horizon*',
        'telescope*',
    ],
    'storage' => [
        'enabled' => true,
        'driver' => 'file',
        'path' => storage_path('debugbar'),
    ],
    'inject' => true,
    'route_prefix' => '_debugbar',
    'route_middleware' => [],
    'capture_ajax' => true,
    'error_handler' => false,
    'clockwork' => false,
    'collectors' => [
        'phpinfo' => true,
        'messages' => true,
        'time' => true,
        'memory' => true,
        'exceptions' => true,
        'log' => true,
        'db' => true,
        'views' => true,
        'route' => true,
        'auth' => false,
        'gate' => true,
        'session' => true,
        'request' => true,
        'mail' => true,
        'laravel' => true,
        'events' => false,
    ],
];
