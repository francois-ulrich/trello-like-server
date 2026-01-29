<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\CardController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Http\Controllers\Admin\UserAdminController;

Route::middleware(JwtMiddleware::class)->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('login', [AuthController::class, 'login'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // User
    Route::get('user', [AuthController::class, 'getUser']);

    Route::middleware([EnsureUserIsNotBanned::class])->group(function () {
        // Admin
        Route::middleware([AdminMiddleware::class])->prefix('admin')->group(function () {
            Route::apiResource('users', UserAdminController::class)->only(['index', 'show']); //TODO: add ->only([]) to other route declarations
            Route::patch('users/{user}/ban', [UserAdminController::class, 'ban'] )->scopeBindings();
            Route::patch('users/{user}/unban', [UserAdminController::class, 'unban'] )->scopeBindings();
        });

        // Cards
        Route::apiResource('boards.columns.cards', CardController::class)->scoped();
        Route::patch('boards/{board}/columns/{column}/cards/{card}/move', [CardController::class, 'move'] )->scopeBindings();

        // Columns
        Route::apiResource('boards.columns', ColumnController::class)->scoped();
        Route::patch('boards/{board}/columns/{column}/move', [ColumnController::class, 'move'] )->scopeBindings();

        // Boards
        Route::apiResource('boards', BoardController::class);

        // User
        Route::get('user', [UserController::class, 'show'])->scopeBindings();
        Route::patch('user', [UserController::class, 'update'])->scopeBindings();
        Route::delete('user', [UserController::class, 'destroy'])->scopeBindings();
    });
});
