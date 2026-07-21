<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List the tasks assigned to the currently authenticated user. Use this for "my tasks" style questions.')]
class MyTasksTool extends Tool
{
    public function handle(Request $request): Response
    {
        $user = $request->user();

        if ($user === null) {
            return Response::error('No authenticated user. This tool requires an authenticated (OAuth) session.');
        }

        $tasks = Task::query()
            ->where('assignee_id', $user->id)
            ->orderBy('due_date')
            ->orderBy('board_id')
            ->orderBy('position')
            ->get();

        if ($tasks->isEmpty()) {
            return Response::text("You ({$user->name}) have no assigned tasks.");
        }

        $lines = $tasks->map(fn (Task $task) => sprintf(
            '#%d [%s/%s]%s %s (board %d)',
            $task->id,
            $task->status->value,
            $task->priority->value,
            $task->due_date ? ' due '.$task->due_date->toDateString() : '',
            $task->title,
            $task->board_id,
        ))->implode("\n");

        return Response::text("Tasks assigned to {$user->name}:\n".$lines);
    }
}
