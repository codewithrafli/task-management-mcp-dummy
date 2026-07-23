<?php

namespace App\Mcp\Tools;

use App\Enums\TaskStatus;
use App\Mcp\Concerns\InteractsWithBoards;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Description('Update the status of an existing task (todo, in_progress or done).')]
#[IsIdempotent]
class UpdateTaskStatusTool extends Tool
{
    use InteractsWithBoards;

    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'task' => ['required'],
            'status' => ['required', Rule::in(TaskStatus::values())],
        ]);

        $task = $this->resolveTask($validated['task'], $request->user());

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
                ->enum(TaskStatus::values())
                ->description('The new task status.')
                ->required(),
        ];
    }
}
