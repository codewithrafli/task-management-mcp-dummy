<?php

namespace App\Mcp\Tools;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Board;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new task inside a board. Returns the created task with its code, status and priority.')]
class CreateTaskTool extends Tool
{
    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate((new StoreTaskRequest)->rules());

        $task = $this->tasks->create([
            'board_id' => $validated['board_id'],
            'assignee_id' => $validated['assignee_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? TaskStatus::Todo->value,
            'priority' => $validated['priority'] ?? TaskPriority::Medium->value,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $board = Board::find($validated['board_id']);

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
            'status' => $schema->string()
                ->enum(TaskStatus::values())
                ->default(TaskStatus::Todo->value)
                ->description('The task status.'),
            'priority' => $schema->string()
                ->enum(TaskPriority::values())
                ->default(TaskPriority::Medium->value)
                ->description('The task priority.'),
            'due_date' => $schema->string()
                ->description('Optional due date (YYYY-MM-DD).'),
        ];
    }
}
