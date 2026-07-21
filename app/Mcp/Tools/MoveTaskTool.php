<?php

namespace App\Mcp\Tools;

use App\Models\Board;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Move a task to a different board, optionally at a given position.')]
class MoveTaskTool extends Tool
{
    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $task = Task::findOrFail($validated['task_id']);
        $board = Board::findOrFail($validated['board_id']);
        $task = $this->tasks->move($task, $board, $validated['position'] ?? null);

        return Response::text(sprintf(
            'Task #%d "%s" moved to board "%s" at position %d.',
            $task->id,
            $task->title,
            $board->name,
            $task->position,
        ));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The id of the task to move.')
                ->required(),
            'board_id' => $schema->integer()
                ->description('The id of the destination board.')
                ->required(),
            'position' => $schema->integer()
                ->description('Optional position within the destination board.'),
        ];
    }
}
