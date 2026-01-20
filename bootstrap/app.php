<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Http\Request;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (
            HttpExceptionInterface $e,
            Request $request
        ) {
            if ($request->expectsJson()) {
                $response = [
                    'error' => [
                        'code' => $e->getStatusCode(),
                        'message' => $e->getMessage() ?: \Symfony\Component\HttpFoundation\Response::$statusTexts[$e->getStatusCode()],
                    ]
                ];

                if (!app()->isProduction()) {
                    $response['error']['debug'] = [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->take(10), // limite le bruit
                    ];
                }

                return response()->json($response, $e->getStatusCode());
            }
        });
    })
->create();
