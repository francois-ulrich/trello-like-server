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

        $position = $column->cards->count();

        $card = Card::create([
            "column_id"=>$column->id,
            'position' => $position,
            ...$validated,
        ]);

        return response()->json($card, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Card $card)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Card $card)
    {
        //
    }
}
