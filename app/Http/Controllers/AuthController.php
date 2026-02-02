<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use App\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // User registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'role_id' => Role::where('slug', 'user')->first()->id,
        ]);

        $user->profile()->save(new UserProfile());

        $token = JWTAuth::fromUser($user);

        $cookie = cookie(
            'accessToken',      // Nom du cookie
            $token,           // Valeur du cookie (le token JWT)
            60,               // Durée en minutes
            '/',              // Chemin
            null,             // Domaine (null pour accepter par défaut)
            true,             // Secure (HTTPS uniquement)
            true,             // HTTP-only
            false,            // SameSite=None
            'Strict'          // Politique SameSite
        );

        return response()->json(compact('user'), 201)->cookie($cookie);
    }

    // User login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            $token = JWTAuth::attempt($credentials);
            if (!$token ) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            // Get the authenticated user.
            $user = auth()->user();

            $cookie = cookie(
                'token',      // Nom du cookie
                $token,           // Valeur du cookie (le token JWT)
                60,               // Durée en minutes
                '/',              // Chemin
                null,             // Domaine (null pour accepter par défaut)
                true,             // Secure (HTTPS uniquement)
                true,             // HTTP-only
                false,            // SameSite=None
                'None'          // Politique SameSite
            );

            return ApiResponse::success(compact('user'))->cookie($cookie);
        } catch (JWTException $e) {
            return ApiResponse::error('Could not create token', 500, $e);
        }
    }

    // User logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
}
