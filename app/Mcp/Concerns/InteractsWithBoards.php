<?php

namespace App\Mcp\Concerns;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scopes MCP data access to the authenticated user.
 *
 * When a request is authenticated (OAuth), only boards the user owns or is a
 * member of are visible/mutable. Over stdio (no user) access is unrestricted,
 * since that transport runs locally as a trusted process.
 */
trait InteractsWithBoards
{
    /**
     * @return Builder<Board>
     */
    protected function boardsQuery(?Authenticatable $user): Builder
    {
        $query = Board::query();

        if ($user instanceof \App\Models\User) {
            $query->accessibleBy($user);
        }

        return $query;
    }

    /**
     * @return Builder<Task>
     */
    protected function tasksQuery(?Authenticatable $user): Builder
    {
        $query = Task::query();

        if ($user instanceof \App\Models\User) {
            $query->whereIn('board_id', $this->boardsQuery($user)->select('id'));
        }

        return $query;
    }

    protected function resolveBoard(int|string $ref, ?Authenticatable $user): ?Board
    {
        $board = Board::resolveRef($ref);

        if ($board === null) {
            return null;
        }

        if ($user instanceof \App\Models\User && ! $board->hasAccess($user)) {
            return null;
        }

        return $board;
    }

    protected function resolveTask(int|string $ref, ?Authenticatable $user): ?Task
    {
        $task = Task::resolveRef($ref);

        if ($task === null) {
            return null;
        }

        if ($user instanceof \App\Models\User && ! $this->tasksQuery($user)->whereKey($task->id)->exists()) {
            return null;
        }

        return $task;
    }
}
