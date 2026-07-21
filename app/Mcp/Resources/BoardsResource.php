<?php

namespace App\Mcp\Resources;

use App\Mcp\Concerns\InteractsWithBoards;
use App\Models\Board;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Resource;

#[Description('The list of boards the current user can access, with their task counts.')]
class BoardsResource extends Resource
{
    use InteractsWithBoards;

    public function handle(Request $request): Response
    {
        $boards = $this->boardsQuery($request->user())
            ->withCount('tasks')
            ->latest()
            ->get()
            ->map(fn (Board $board) => [
                'id' => $board->id,
                'code' => $board->code,
                'name' => $board->name,
                'description' => $board->description,
                'tasks_count' => $board->tasks_count,
            ]);

        return Response::json($boards);
    }
}
