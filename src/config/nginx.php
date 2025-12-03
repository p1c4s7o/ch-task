<?php

return [
    'driver' => env('SYSTEM_DRIVER', 'systemd'),

    /**
     * @deprecated use_docker
     */
    'use_docker' => !is_null(env('NGINX_CONTAINER')),

    'use_redis' => !is_null(env('CITYHOST_USE_REDIS', true)),

    'middleware' => [
//        \App\Services\Nginx\Middleware\DockerPrefixMiddleware::class,
//        \App\Services\Nginx\Middleware\SupervisorPrefixMiddleware::class,
        \App\Services\Nginx\Middleware\ValidateMiddleware::class,
        \App\Services\Nginx\Middleware\LoggingMiddleware::class,
    ],

    'strategy' => [
        'systemd' => \App\Domain\Nginx\Strategies\SystemctlStrategy::class,
        'service' => \App\Domain\Nginx\Strategies\ServiceStrategy::class,
        'native' => \App\Domain\Nginx\Strategies\NativeNginxStrategy::class,
        'brew' => \App\Domain\Nginx\Strategies\BrewServiceStrategy::class,
    ],

    'docker' => [
        'container' => env('NGINX_CONTAINER'),
        'interactive' => env('DOCKER_CALL_IT', false),
    ],

    'stubs_prefix' => [
        'host' => 'nginx_vhost_',
        'store' => 'index_'
    ]
];
