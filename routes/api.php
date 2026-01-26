<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\CardController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\UserController;

Route::middleware(JwtMiddleware::class)->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('login', [AuthController::class, 'login'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Admin
    Route::middleware([AdminMiddleware::class])->prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class)->only(['index', 'show']); //TODO: add ->only([]) to other route declarations
        Route::patch('users/{user_id}/ban', [UserController::class, 'ban'] );
        Route::patch('users/{user_id}/unban', [UserController::class, 'unban'] );
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
    Route::get('user', [AuthController::class, 'getUser']);

    // User profile
    Route::get('user/profile', [UserProfileController::class, 'get']);
    Route::put('user/profile', [UserProfileController::class, 'update']);
});
