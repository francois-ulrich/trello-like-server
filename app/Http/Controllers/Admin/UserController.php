<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Returns a listing of the resource.
     */
    public function index()
    {
        $users = User::with('profile', 'role')->get();
        return response()->json($users, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('profile', 'role');
        return response()->json($user, 200);
    }

    public function ban(string $user_id)
    {
        $user = User::findOrFail($user_id);
        $user->update([ 'banned_at' => now(), ]);
        return response()->json($user, 200);
    }

    public function unban(string $user_id)
    {
        $user = User::findOrFail($user_id);
        $user->update([ 'banned_at' => null, ]);
        return response()->json($user, 200);
    }
}
