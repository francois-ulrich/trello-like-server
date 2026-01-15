<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    /**
     * Returns a listing of the resource.
     */
    public function index()
    {
        $boards = Board::where('user_id', auth()->user()->id)->get();
        return response()->json($boards, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $board = Board::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($board, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $board = Board::where('user_id', auth()->user()->id)->findOrFail($id);
        return response()->json($board, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $board = Board::where('user_id', auth()->user()->id)->findOrFail($id);

        if(!$board)
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'Resource not found',
                ]
            ], 404);

        $request->validate([
            'title' => 'string|max:255',
        ]);

        $board->title = $request->get('title');

        $board->save();

        return response()->json($board, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $board = Board::where('user_id', auth()->user()->id)->find($id);

        if(!$board)
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'Resource not found',
                ]
            ], 404);

        $board->delete();

        return response()->json(['message' => 'Resource deleted successfully'], 200);
    }
}
