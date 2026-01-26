<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                $response = [
                    'error' => [
                        'code' => 404,
                        'message' => "User not found"
                    ]
                ];

                return response()->json($response, 404);
            }
        } catch (JWTException $e) {
            $response = [
                'error' => [
                    'code' => 400,
                    'message' => "Invalid token"
                ]
            ];

            return response()->json($response, 400);
        }

        return $next($request);
    }
}
