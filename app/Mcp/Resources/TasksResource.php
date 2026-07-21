<?php

namespace App\Mcp\Resources;

use App\Mcp\Concerns\InteractsWithBoards;
use App\Models\Task;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Resource;

#[Description('The tasks across every board the current user can access, ordered by board and position.')]
class TasksResource extends Resource
{
    use InteractsWithBoards;

    public function handle(Request $request): Response
    {
        $tasks = $this->tasksQuery($request->user())
            ->orderBy('board_id')
            ->orderBy('position')
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'code' => $task->code,
                'board_id' => $task->board_id,
                'assignee_id' => $task->assignee_id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'due_date' => $task->due_date?->toDateString(),
                'position' => $task->position,
            ]);

        return Response::json($tasks);
    }
}
