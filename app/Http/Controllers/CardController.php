<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Returns a listing of the resource.
     */
    public function index(Board $board, Column $column)
    {
        $this->authorize('view', $column);
        $cards = $column->cards;
        return response()->json($cards, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board, Column $column, Card $card)
    {
        $this->authorize('view', $card);
        return response()->json($card, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Board $board, Column $column, Request $request)
    {
        $this->authorize('view', $column);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $lastPosition = $column->cards->max('position') ?? -1;

        $card = Card::create([
            "column_id"=>$column->id,
            'position' => $lastPosition + 1,
            ...$validated,
        ]);

        return response()->json($card, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Board $board, Column $column, Card $card, Request $request)
    {
        $this->authorize('update', $card);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $card->update(['name' => $request->get('name')]);
        $card->update(['description' => $request->get('description')]);

        return response()->json($card, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board, Column $column, Card $card)
    {
        if(!$card)
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'Resource not found',
                ]
            ], 404);

        $this->authorize('delete', $card);

        $card->delete();

        return response()->json(['message' => 'Resource deleted successfully'], 200);
    }
}
