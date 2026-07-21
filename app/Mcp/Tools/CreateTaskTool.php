<?php

namespace App\Mcp\Tools;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Mcp\Concerns\InteractsWithBoards;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new task inside a board. Returns the created task with its id, status and priority.')]
class CreateTaskTool extends Tool
{
    use InteractsWithBoards;

    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate((new StoreTaskRequest)->rules());

        $board = $this->resolveBoard($validated['board_id'], $request->user());

        if ($board === null) {
            return Response::error("Board #{$validated['board_id']} not found.");
        }

        $task = $this->tasks->create([
            'board_id' => $board->id,
            'assignee_id' => $validated['assignee_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? TaskStatus::Todo->value,
            'priority' => $validated['priority'] ?? TaskPriority::Medium->value,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return Response::text(sprintf(
            'Task %s "%s" created in board "%s" (status: %s, priority: %s).',
            $task->code,
            $task->title,
            $board->name,
            $task->status->value,
            $task->priority->value,
        ));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'board_id' => $schema->integer()
                ->description('The id of the board the task belongs to.')
                ->required(),
            'assignee_id' => $schema->integer()
                ->description('Optional id of the user this task is assigned to.'),
            'title' => $schema->string()
                ->description('The task title.')
                ->required(),
            'description' => $schema->string()
                ->description('Optional longer description of the task.'),
            'due_date' => $schema->string()
                ->description('Optional due date (YYYY-MM-DD).'),
            'status' => $schema->string()
                ->description('One of: '.implode(', ', TaskStatus::values()).'. Defaults to todo.'),
            'priority' => $schema->string()
                ->description('One of: '.implode(', ', TaskPriority::values()).'. Defaults to medium.'),
        ];
    }
}
