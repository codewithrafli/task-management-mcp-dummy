<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function view(User $user, Board $board): bool
    {
        return $board->hasAccess($user);
    }

    public function update(User $user, Board $board): bool
    {
        return $board->hasAccess($user);
    }

    public function delete(User $user, Board $board): bool
    {
        return $board->user_id === $user->id;
    }

    /**
     * Only the owner can invite or remove members.
     */
    public function manageMembers(User $user, Board $board): bool
    {
        return $board->user_id === $user->id;
    }

    /**
     * Only the owner can change board settings (name, description).
     */
    public function manageSettings(User $user, Board $board): bool
    {
        return $board->user_id === $user->id;
    }
}
