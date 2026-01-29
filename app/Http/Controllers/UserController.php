<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request) {
        return ApiResponse::success($request->user());
    }

    public function update(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $request->user()->name = $request->get('name');

        $request->user()->save();

        return ApiResponse::updated($request->user());
    }
}
