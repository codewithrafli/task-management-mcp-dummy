<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Search tasks by keyword in their title or description.')]
class SearchTaskTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1'],
        ]);

        $results = Task::query()
            ->search($validated['query'])
            ->orderBy('position')
            ->get();

        if ($results->isEmpty()) {
            return Response::text('No tasks found for query "'.$validated['query'].'".');
        }

        $lines = $results->map(fn (Task $task) => sprintf(
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
        ];
    }
}
