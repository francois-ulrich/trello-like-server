<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function show(Request $request) {
        return ApiResponse::success($request->user(), "Logged in !");
    }

    public function update(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $request->user()->name = $request->get('name');

        $request->user()->save();

        return ApiResponse::updated($request->user());
    }

    public function destroy(Request $request) {

        $credentials = [
            "email" => $request->user()->email,
            "password" => $request->get('password')
        ];

        if (!auth()->validate($credentials)) {
            abort(422, "Wrong password");
        }

        JWTAuth::invalidate(JWTAuth::getToken());

        $request->user()->delete();

        return ApiResponse::deleted($request->user());
    }
}
