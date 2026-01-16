<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    protected $fillable = [
        'name',
        'board_id',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
