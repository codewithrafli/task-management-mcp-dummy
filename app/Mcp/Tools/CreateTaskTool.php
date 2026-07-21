<?php

namespace App\Mcp\Tools;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Board;
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
    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:'.implode(',', TaskStatus::values())],
            'priority' => ['nullable', 'in:'.implode(',', TaskPriority::values())],
        ]);

        $task = $this->tasks->create([
            'board_id' => $validated['board_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? TaskStatus::Todo->value,
            'priority' => $validated['priority'] ?? TaskPriority::Medium->value,
        ]);

        $board = Board::find($validated['board_id']);

        return Response::text(sprintf(
            'Task #%d "%s" created in board "%s" (status: %s, priority: %s).',
            $task->id,
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
            'title' => $schema->string()
                ->description('The task title.')
                ->required(),
            'description' => $schema->string()
                ->description('Optional longer description of the task.'),
            'status' => $schema->string()
                ->description('One of: '.implode(', ', TaskStatus::values()).'. Defaults to todo.'),
            'priority' => $schema->string()
                ->description('One of: '.implode(', ', TaskPriority::values()).'. Defaults to medium.'),
        ];
    }
}
