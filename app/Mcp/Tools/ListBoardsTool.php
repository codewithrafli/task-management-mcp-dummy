<?php

namespace App\Mcp\Tools;

use App\Models\Board;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List every board, including empty ones, with their task counts.')]
#[IsReadOnly()]
class ListBoardsTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $boards = Board::query()
            ->withCount('tasks')
            ->latest()
            ->get();

        if ($boards->isEmpty()) {
            return Response::text('No boards found.');
        }

        $lines = $boards->map(fn(Board $board) => sprintf(
            '#%d %s [%s] — %d task(s)%s',
            $board->id,
            $board->name,
            $board->code,
            $board->tasks_count,
            $board->description ? ' — ' . $board->description : '',
        ))->implode("\n");

        return Response::text($boards->count() . ' board(s):' . "\n" . $lines);
    }
}
