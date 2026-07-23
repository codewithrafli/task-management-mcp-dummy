<?php

namespace App\Mcp\Resources;

use App\Models\Board;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Description('The list of all boards with their task counts.')]
#[Uri('tasks://boards')]
#[MimeType('application/json')]
class BoardsResource extends Resource
{
    public function handle(Request $request): Response
    {
        $boards = Board::query()
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
