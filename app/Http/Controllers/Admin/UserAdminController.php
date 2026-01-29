<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\ApiResponse;

class UserAdminController extends Controller
{
    /**
     * Returns a listing of the resource.
     */
    public function index()
    {
        $users = User::with('profile', 'role')->get();
        return ApiResponse::success($users);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('profile', 'role');
        return response()->json($user, 200);
    }

    public function ban(User $user)
    {
        if($user->isAdmin())
            return abort(403);

        $user->update([ 'banned_at' => now(), ]);
        return response()->json($user, 200);
    }

    public function unban(User $user)
    {
        if($user->isAdmin())
            return abort(403);

        $user->update([ 'banned_at' => null, ]);
        return response()->json($user, 200);
    }
}
