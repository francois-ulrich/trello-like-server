<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Http\Request;
use App\Http\ApiResponse;

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
            NotFoundHttpException  $e,
            Request $request
        ) {
            return ApiResponse::error("Not found", 404, $e);
        });

        $exceptions->render(function (
            ValidationException  $e,
            Request $request
        ) {
            return ApiResponse::error($e->getMessage() ?: \Symfony\Component\HttpFoundation\Response::$statusTexts[$e->getStatusCode()], 422, $e);
        });

        $exceptions->render(function (
            HttpExceptionInterface $e,
            Request $request
        ) {
            return ApiResponse::error($e->getMessage() ?: \Symfony\Component\HttpFoundation\Response::$statusTexts[$e->getStatusCode()], $e->getStatusCode(), $e);
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error( "A server error has occured", 500, $e );
            }
        });
    })
->create();
