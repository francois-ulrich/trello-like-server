<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use App\Http\ApiResponse;
use App\Http\Resources\UserResource;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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

        event(new Registered($user));

        $token = JWTAuth::fromUser($user);

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

        $data = ["user" => new UserResource($user)];

        return ApiResponse::created($data)->cookie($cookie);
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

            $data = ["user" => new UserResource($user)];

            if($user->banned_at !== null)
                return ApiResponse::success($data, 'User is banned');

            return ApiResponse::success($data, 'Successfully logged in !')->cookie($cookie);
        } catch (JWTException $e) {
            return ApiResponse::error('Could not create token', 500, $e);
        }
    }

    // User logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return ApiResponse::success(null, "Successfully logged out");
    }

    public function sendVerificationEmail(Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return ApiResponse::success(null, "Email has been sent !");
    }

    public function sendPasswordResetEmail(Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::ResetLinkSent
            ? ApiResponse::success(['status' => __($status)], "Email has been sent !")
            : ApiResponse::error("An error occured.");

        return ApiResponse::success(null, "Email has been sent !");
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? ApiResponse::success(null, "The password has been successfully reset !")
            : ApiResponse::error(null, "An error has occured");
    }
}
