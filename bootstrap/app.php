<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $err, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'meta' => [
                        'message' => trans('api.response.validation_error'),
                        'errors'  => $err->errors(),
                    ],
                    'data' => [],
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $err, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'meta' => [
                        'message' => __('api.unauthenticated'),
                        'errors'  => ['auth' => [__('api.unauthenticated')]],
                    ],
                    'data' => [],
                ], 401);
            }
        });
    })->create();
