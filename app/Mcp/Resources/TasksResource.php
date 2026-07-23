<?php

namespace App\Mcp\Resources;

use App\Models\Task;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Description('The list of all tasks across every board, ordered by board and position.')]
#[Uri('tasks://tasks')]
#[MimeType('application/json')]
class TasksResource extends Resource
{
    public function handle(Request $request): Response
    {
        $tasks = Task::query()
            ->orderBy('board_id')
            ->orderBy('position')
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'code' => $task->code,
                'board_id' => $task->board_id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'position' => $task->position,
            ]);

        return Response::json($tasks);
    }
}
