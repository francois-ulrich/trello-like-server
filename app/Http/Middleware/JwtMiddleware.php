<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\ApiResponse;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return ApiResponse::error('User not found', 401);
            }
        } catch (JWTException $e) {
            return ApiResponse::error('Invalid token', 401, $e);
        }

        return $next($request);
    }
}
