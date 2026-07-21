<?php

namespace App\Services;

use App\Models\Board;
use Illuminate\Database\Eloquent\Collection;

class BoardService
{
    public function all(): Collection
    {
        return Board::withCount('tasks')->latest()->get();
    }

    public function create(array $data): Board
    {
        return Board::create($data);
    }

    public function update(Board $board, array $data): Board
    {
        $board->update($data);

        return $board->refresh();
    }

    public function delete(Board $board): void
    {
        $board->delete();
    }
}
