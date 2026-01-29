<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\ApiResponse;

class ColumnController extends Controller
{
    /**
     * Returns a listing of the resource.
     */
    public function index(Board $board)
    {
        $this->authorize('view', $board);
        $columns = $board->columns()->orderBy('position')->get();
        return ApiResponse::success($columns);
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board, Column $column)
    {
        $this->authorize('view', $column);
        return ApiResponse::success($column);
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

        return ApiResponse::created($column);
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

        return ApiResponse::updated($column);
    }

    public function move(Board $board, Column $column, Request $request)
    {
        $this->authorize('update', $column);

        $data = $request->validate([
            'targetPosition' => 'required|integer|min:0',
        ]);

        $targetPosition = $data['targetPosition'];

        if ($targetPosition === $column->position) {
            return ApiResponse::success($column);
        }

        $maxPosition = $board->columns()->max('position');

        if ($targetPosition > $maxPosition) {
            abort(422, 'Invalid target position');
        }

        DB::transaction(function () use ($board, $column, $targetPosition) {
            $previousPosition = $column->position;
            $columnToDisplace = $board->columns->where("position", $targetPosition)->firstOrFail();

            $column->update(['position' => $targetPosition]);
            $columnToDisplace->update(['position' => $previousPosition]);
        });

        return ApiResponse::updated($column);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board, Column $column)
    {
        $this->authorize('delete', $column);

        DB::transaction(function () use ($column) {
            $position = $column->position;
            $boardId  = $column->board_id;

            $column->delete();

            Column::where('board_id', $boardId)
                ->where('position', '>', $position)
                ->decrement('position');
        });

        $column->delete();

        return ApiResponse::deleted($column);
    }
}
