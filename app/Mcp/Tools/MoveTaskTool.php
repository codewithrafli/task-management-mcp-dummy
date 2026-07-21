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
            'task' => ['required'],
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $task = Task::resolveRef($validated['task']);

        if ($task === null) {
            return Response::error("Task \"{$validated['task']}\" not found.");
        }

        $board = Board::findOrFail($validated['board_id']);
        $task = $this->tasks->move($task, $board, $validated['position'] ?? null);

        return Response::text(sprintf(
            'Task %s "%s" moved to board "%s" at position %d.',
            $task->code,
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
            'task' => $schema->string()
                ->description('The task code (e.g. "SPR-1") or numeric id.')
                ->required(),
            'board_id' => $schema->integer()
                ->description('The id of the destination board.')
                ->required(),
            'position' => $schema->integer()
                ->description('Optional position within the destination board.'),
        ];
    }
}
