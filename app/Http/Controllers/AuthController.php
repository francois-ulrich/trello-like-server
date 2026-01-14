<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
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
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Get the authenticated user.
            $user = auth()->user();

            // (optional) Attach the role to the token.
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

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

            return response()->json(compact('user'))->cookie($cookie);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    // Get authenticated user
    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(compact('user'));
    }

    // User logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
}
