<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\InteractsWithBoards;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Search tasks by keyword in their title or description. Optionally limit to a single board.')]
#[IsReadOnly]
class SearchTaskTool extends Tool
{
    use InteractsWithBoards;

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1'],
            'board_id' => ['nullable', 'integer', 'exists:boards,id'],
        ]);

        $results = $this->tasksQuery($request->user())
            ->when($validated['board_id'] ?? null, fn ($q, $id) => $q->where('board_id', $id))
            ->search($validated['query'])
            ->orderBy('position')
            ->get();

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
