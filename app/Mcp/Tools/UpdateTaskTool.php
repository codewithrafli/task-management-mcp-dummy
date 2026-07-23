<?php

namespace App\Mcp\Tools;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\UpdateTaskRequest;
use App\Mcp\Concerns\InteractsWithBoards;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Description('Update the editable fields of an existing task: title, description, status, priority and due date. Only the fields you pass are changed.')]
#[IsIdempotent]
class UpdateTaskTool extends Tool
{
    use InteractsWithBoards;

    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'task' => ['required'],
            ...(new UpdateTaskRequest)->rules(),
        ]);

        $task = $this->resolveTask($validated['task'], $request->user());

        if ($task === null) {
            return Response::error("Task \"{$validated['task']}\" not found.");
        }

        $changes = collect($validated)->except('task')->all();

        if ($changes === []) {
            return Response::error('Nothing to update. Provide at least one field to change.');
        }

        $this->tasks->update($task, $changes);

        return Response::text(sprintf(
            'Task %s updated (%s).',
            $task->code,
            implode(', ', array_keys($changes)),
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
            'title' => $schema->string()->description('New title.'),
            'description' => $schema->string()->description('New description.'),
            'status' => $schema->string()->enum(TaskStatus::values())->description('New status.'),
            'priority' => $schema->string()->enum(TaskPriority::values())->description('New priority.'),
            'due_date' => $schema->string()->description('New due date (YYYY-MM-DD), or empty to clear.'),
        ];
    }
}
