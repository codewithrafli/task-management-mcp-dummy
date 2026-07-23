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

        if (isset($validated['assignee_id']) && ! in_array($validated['assignee_id'], $board->teamIds(), true)) {
            return Response::error('The assignee must be a member of the board.');
        }

        $task = $this->tasks->create([
            ...$validated,
            'board_id' => $board->id, // override for scoping — never trust the raw input id
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
                ->enum(TaskStatus::values())
                ->default(TaskStatus::Todo->value)
                ->description('The task status.'),
            'priority' => $schema->string()
                ->enum(TaskPriority::values())
                ->default(TaskPriority::Medium->value)
                ->description('The task priority.'),
        ];
    }
}
