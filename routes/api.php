<?php

use App\Http\ApiResponse;
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
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

Route::middleware(JwtMiddleware::class)->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('login', [AuthController::class, 'login'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('logout', [AuthController::class, 'logout'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('forgot-password', [AuthController::class, 'sendPasswordResetEmail'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('verification-notification', [AuthController::class, 'sendVerificationEmail'])->middleware(['throttle:2,1'])->name('verification.send');
        Route::post('forgot-password', [AuthController::class, 'sendPasswordResetEmail'])->withoutMiddleware([JwtMiddleware::class]);
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->withoutMiddleware([JwtMiddleware::class]);

        Route::get('me', [UserController::class, 'show'])->scopeBindings();
        Route::patch('me', [UserController::class, 'update'])->scopeBindings();
        Route::delete('me', [UserController::class, 'destroy'])->scopeBindings();
    });

    Route::middleware([EnsureEmailIsVerified::class, EnsureUserIsNotBanned::class])->group(function () {
        // Admin
        Route::middleware([AdminMiddleware::class])->prefix('admin')->group(function () {
            Route::apiResource('users', UserAdminController::class)->only(['index', 'show']);
            Route::get('users/{user}/boards', [UserAdminController::class, 'getUserBoards'] )->scopeBindings();
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
    });

    Route::prefix('email')->group(function () {
        Route::get('/verify', function () {
            return view('auth.verify-email');
        })->name('verification.notice');

        Route::get('/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
            $request->fulfill();

            return redirect(config('app.frontend_url') . '/email-verified');
        })->middleware(['signed'])->name('verification.verify');
    });
});


