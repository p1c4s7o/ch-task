<?php

use App\Exceptions\NginxRedisException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') && !$request->is('api/doc*'))
                return response()->json([
                    'error' => 'Route not found'
                ], 404);
        });
        $exceptions->render(function (NginxRedisException $e, Request $request) {
            $status = $e->getStatus();
            return response()->json([
                'status' => $status,
                'error' => $e->getMessage()
            ], $status ? 200 : 400);
        });
    })->create();
