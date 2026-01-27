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
        return response()->json($board, 200);
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
            'name' => 'string|max:255',
        ]);

        $board->name = $request->get('name');

        $board->save();

        return response()->json($board, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board)
    {
        $board->delete();
        return response()->json(['message' => 'Resource deleted successfully'], 200); //TODO: standardiser le format des response
    }
}
