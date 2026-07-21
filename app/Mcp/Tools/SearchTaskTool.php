<?php

namespace App\Mcp\Tools;

use App\Models\Board;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Search tasks by keyword in their title or description. Optionally limit to a single board.')]
class SearchTaskTool extends Tool
{
    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1'],
            'board_id' => ['nullable', 'integer', 'exists:boards,id'],
        ]);

        $board = isset($validated['board_id']) ? Board::find($validated['board_id']) : null;
        $results = $this->tasks->search($validated['query'], $board);

        if ($results->isEmpty()) {
            return Response::text('No tasks found for query "'.$validated['query'].'".');
        }

        $lines = $results->map(fn ($task) => sprintf(
            '%s [%s/%s] %s (board %d)',
            $task->code,
            $task->status->value,
            $task->priority->value,
            $task->title,
            $task->board_id,
        ))->implode("\n");

        return Response::text($results->count().' task(s) found:'."\n".$lines);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Keyword to search for in task title or description.')
                ->required(),
            'board_id' => $schema->integer()
                ->description('Optional board id to restrict the search.'),
        ];
    }
}
