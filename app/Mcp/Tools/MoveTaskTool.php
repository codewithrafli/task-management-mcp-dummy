<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\InteractsWithBoards;
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
    use InteractsWithBoards;

    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'task' => ['required'],
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $task = $this->resolveTask($validated['task'], $request->user());

        if ($task === null) {
            return Response::error("Task \"{$validated['task']}\" not found.");
        }

        $board = $this->resolveBoard($validated['board_id'], $request->user());

        if ($board === null) {
            return Response::error("Destination board #{$validated['board_id']} not found.");
        }

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
