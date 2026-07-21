<?php

namespace App\Mcp\Resources;

use App\Services\BoardService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Resource;

#[Description('The list of all boards with their task counts.')]
class BoardsResource extends Resource
{
    public function __construct(private readonly BoardService $boards) {}

    public function handle(Request $request): Response
    {
        $boards = $this->boards->all()->map(fn ($board) => [
            'id' => $board->id,
            'code' => $board->code,
            'name' => $board->name,
            'description' => $board->description,
            'tasks_count' => $board->tasks_count,
        ]);

        return Response::json($boards);
    }
}
