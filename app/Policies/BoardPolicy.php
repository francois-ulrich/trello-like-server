<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;

class BoardPolicy
{
    public function view(User $user, Board $board): bool
    {
        return $board->user_id === $user->id;
    }
}
