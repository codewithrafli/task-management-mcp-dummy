<?php

namespace App\Mcp\Tools;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Move a task to a different board.')]
class MoveTaskTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'task' => ['required'],
            'board_id' => ['required', 'integer', 'exists:boards,id'],
        ]);

        $task = Task::resolveRef($validated['task']);

        if ($task === null) {
            return Response::error("Task \"{$validated['task']}\" not found.");
        }

        $board = Board::findOrFail($validated['board_id']);
        $task->update(['board_id' => $board->id]);

        return Response::text(sprintf('Task %s "%s" moved to board "%s".', $task->code, $task->title, $board->name));
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
        ];
    }
}
