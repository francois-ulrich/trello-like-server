<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;

class BoardPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Board $board): bool
    {
        return $board->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Board $board): bool
    {
        return $board->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Board $board): bool
    {
       return $board->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Board $board): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Board $board): bool
    {
        return false;
    }
}
