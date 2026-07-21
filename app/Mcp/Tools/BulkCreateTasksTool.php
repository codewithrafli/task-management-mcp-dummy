<?php

namespace App\Mcp\Tools;

use App\Enums\TaskStatus;
use App\Mcp\Concerns\InteractsWithBoards;
use App\Services\TaskService;
use Generator;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create many tasks on a board at once. Streams progress notifications while working (long-running).')]
class BulkCreateTasksTool extends Tool
{
    use InteractsWithBoards;

    public function __construct(private readonly TaskService $tasks) {}

    /**
     * @return Generator<int, Response>
     */
    public function handle(Request $request): Generator
    {
        $validated = $request->validate([
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'titles' => ['required', 'array', 'min:1', 'max:50'],
            'titles.*' => ['required', 'string', 'max:255'],
        ]);

        if ($this->resolveBoard($validated['board_id'], $request->user()) === null) {
            yield Response::error("Board #{$validated['board_id']} not found.");

            return;
        }

        $titles = $validated['titles'];
        $total = count($titles);
        $progressToken = $request->meta()['progressToken'] ?? null;

        $created = [];

        foreach (array_values($titles) as $index => $title) {
            $task = $this->tasks->create([
                'board_id' => $validated['board_id'],
                'title' => $title,
                'status' => TaskStatus::Todo->value,
            ]);

            $created[] = $task->id;

            // Emit a progress notification if the client asked for one.
            if ($progressToken !== null) {
                yield Response::notification('notifications/progress', [
                    'progressToken' => $progressToken,
                    'progress' => $index + 1,
                    'total' => $total,
                    'message' => "Created task \"{$title}\" ({$index}/{$total})",
                ]);
            }
        }

        yield Response::text(sprintf(
            'Created %d task(s) on board #%d: %s',
            $total,
            $validated['board_id'],
            implode(', ', array_map(fn ($id) => "#{$id}", $created)),
        ));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'board_id' => $schema->integer()
                ->description('The board to create the tasks on.')
                ->required(),
            'titles' => $schema->array()
                ->items($schema->string())
                ->description('A list of task titles to create (1-50).')
                ->required(),
        ];
    }
}
