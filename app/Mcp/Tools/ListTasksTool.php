<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List all tasks across every board, ordered by board and position.')]
#[IsReadOnly()]
class ListTasksTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $tasks = Task::query()
            ->orderBy('board_id')
            ->orderBy('position')
            ->get();

        if ($tasks->isEmpty()) {
            return Response::text('No tasks found.');
        }

        $lines = $tasks->map(fn (Task $task) => sprintf(
            '%s [%s/%s] %s (board %d)',
            $task->code,
            $task->status->value,
            $task->priority->value,
            $task->title,
            $task->board_id,
        ))->implode("\n");

        return Response::text($tasks->count().' task(s):'."\n".$lines);
    }
}
