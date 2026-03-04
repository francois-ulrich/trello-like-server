<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\ApiResponse;
use Illuminate\Database\Eloquent\Builder;

class CardController extends Controller
{
    /**
     * Returns a listing of the resource.
     */
    public function index(Board $board, Column $column)
    {
        $this->authorize('view', $column);
        $cards = $column->cards()->orderBy('position')->get();
        return ApiResponse::success($cards);
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board, Column $column, Card $card)
    {
        $this->authorize('view', $card);
        return ApiResponse::success($card);
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

        return ApiResponse::created($card);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Board $board, Column $column, Card $card, Request $request)
    {
        $this->authorize('update', $card);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $card->update($validated);

        return ApiResponse::updated($card);
    }

    public function move(Board $board, Column $column, Card $card, Request $request)
    {
        $this->authorize('update', $card);

        $data = $request->validate([
            'targetPosition' => 'required|integer|min:0',
            'targetColumnId' => 'required|integer|min:0',
        ]);

        $targetPosition = $data['targetPosition'];
        $targetColumnId = $data['targetColumnId'];

        if ($targetColumnId === $card->column_id && $targetPosition === $card->position) {
            return ApiResponse::success([
                "movedCard" => $card,
                "affectedCards" => []
            ]);
        }

        $targetColumn = $targetColumnId === $card->column_id ? $card->column : Column::find($targetColumnId);

        $maxPosition = $targetColumn->cards()->max('position') + 1;

        if ($targetPosition > $maxPosition) {
            abort(422, 'Invalid target position');
        }

        DB::transaction(function () use ($card, $targetColumn, $targetPosition) {
            if($targetColumn != $card->column){
                Card::where('column_id', $card->column_id)
                ->where('position', '>', $card->position)
                ->decrement('position');

                Card::where('column_id', $targetColumn->id)
                ->where('position', '>=', $targetPosition)
                ->increment('position');

                $card->update([
                    'column_id' => $targetColumn->id,
                    'position' => $targetPosition
                ]);
            }else{
                if ($targetPosition > $card->position) {
                    Card::where('column_id', $card->column_id)
                        ->whereBetween('position', [$card->position + 1, $targetPosition])
                        ->decrement('position');
                } elseif ($targetPosition < $card->position) {
                    Card::where('column_id', $card->column_id)
                        ->whereBetween('position', [$targetPosition, $card->position - 1])
                        ->increment('position');
                }

                $card->update(['position' => $targetPosition]);
            }
        });

        $data = [
            "movedCard" => $card
        ];

        return ApiResponse::updated($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board, Column $column, Card $card)
    {
        $this->authorize('delete', $card);

        DB::transaction(function () use ($card) {
            $position = $card->position;
            $columnId  = $card->column_id;

            $card->delete();

            Card::where('column_id', $columnId)
                ->where('position', '>', $position)
                ->decrement('position');
        });

        return ApiResponse::deleted($card);
    }
}
