<?php

namespace App\Mcp\Tools;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update the status of an existing task (todo, in_progress or done).')]
class UpdateTaskStatusTool extends Tool
{
    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'task' => ['required'],
            'status' => ['required', 'in:'.implode(',', TaskStatus::values())],
        ]);

        $task = Task::resolveRef($validated['task']);

        if ($task === null) {
            return Response::error("Task \"{$validated['task']}\" not found.");
        }

        $task = $this->tasks->changeStatus($task, TaskStatus::from($validated['status']));

        return Response::text(sprintf(
            'Task %s "%s" status updated to "%s".',
            $task->code,
            $task->title,
            $task->status->value,
        ));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'task' => $schema->string()
                ->description('The task code (e.g. "SPR-1") or numeric id.')
                ->required(),
            'status' => $schema->string()
                ->description('New status. One of: '.implode(', ', TaskStatus::values()).'.')
                ->required(),
        ];
    }
}
