<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Http\ApiResponse;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    /**
     * Returns a listing of the resource.
     */
    public function index()
    {
        $boards = Board::where('user_id', auth()->user()->id)->get();
        return ApiResponse::success($boards);
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board)
    {
        $this->authorize("view", $board);
        return ApiResponse::success($board);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $board = Board::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return ApiResponse::created($board);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Board $board, Request $request)
    {
        $this->authorize('update', $board);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $board->name = $request->get('name');

        $board->save();

        return ApiResponse::updated($board);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board)
    {
        $this->authorize('delete', $board);
        $board->delete();
        return ApiResponse::deleted($board);
    }
}
