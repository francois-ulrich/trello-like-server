<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColumnController extends Controller
{
    /**
     * Returns a listing of the resource.
     */
    public function index(Board $board)
    {
        $this->authorize('view', $board);
        $columns = $board->columns;
        return response()->json($columns, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board, Column $column)
    {
        $this->authorize('view', $column);
        return response()->json($column, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Board $board, Request $request)
    {
        $this->authorize('view', $board);

        $lastPosition = $board->columns->max('position') ?? -1;

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $column = Column::create([
            "board_id"=>$board->id,
            'position' => $lastPosition + 1,
            ...$data,
        ]);

        return response()->json($column, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Board $board, Column $column, Request $request)
    {
        $this->authorize('update', $column);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $column->update(['name' => $request->get('name')]);

        return response()->json($column, 200);
    }

    public function move(Board $board, Column $column, Request $request)
    {
        $this->authorize('update', $column);

        $data = $request->validate([
            'targetPosition' => 'required|integer|min:0',
        ]);

        $targetPosition = $data['targetPosition'];

        if ($targetPosition === $column->position) {
            return response()->json( $board->columns()->orderBy('position')->get(), 200 );
        }

        $maxPosition = $board->columns()->max('position');

        if ($targetPosition > $maxPosition) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Invalid target position',
                ]
            ], 422);
        }

        DB::transaction(function () use ($board, $column, $targetPosition) {
            $previousPosition = $column->position;
            $columnToDisplace = $board->columns->where("position", $targetPosition)->firstOrFail();

            $column->update(['position' => $targetPosition]);
            $columnToDisplace->update(['position' => $previousPosition]);
        });

        return response()->json( $board->columns()->orderBy('position')->get(), 200 );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board, Column $column)
    {
        if(!$column)
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'Resource not found',
                ]
            ], 404);

        $this->authorize('delete', $column);

        $column->delete();

        return response()->json(['message' => 'Resource deleted successfully'], 200);
    }
}
