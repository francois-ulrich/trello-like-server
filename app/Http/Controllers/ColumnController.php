<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'board_id' => 'required|exists:boards,id',
            'name' => 'required|string|max:255',
        ]);

        Board::where('user_id', auth()->user()->id)->findOrFail($request->board_id);

        $column = Column::create([
            ...$data,
        ]);

        return response()->json($column, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
