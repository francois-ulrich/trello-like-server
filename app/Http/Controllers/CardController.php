<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function move(Board $board, Column $column, Card $card, Request $request)
    {
        $this->authorize('update', $card);

        $data = $request->validate([
            'targetPosition' => 'required|integer|min:0',
        ]);

        $targetPosition = $data['targetPosition'];

        if ($targetPosition === $card->position) {
            return response()->json( $column->cards()->orderBy('position')->get(), 200 );
        }

        $maxPosition = $column->cards()->max('position');

        if ($targetPosition > $maxPosition) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Invalid target position',
                ]
            ], 422);
        }

        DB::transaction(function () use ($column, $card, $targetPosition) {
            $previousPosition = $card->position;
            $cardToDisplace = $column->cards->where("position", $targetPosition)->firstOrFail();

            $card->update(['position' => $targetPosition]);
            $cardToDisplace->update(['position' => $previousPosition]);
        });

        return response()->json( $column->cards()->orderBy('position')->get(), 200 );
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
