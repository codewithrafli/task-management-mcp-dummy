<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Permanently delete a task.')]
#[IsDestructive]
class DeleteTaskTool extends Tool
{
    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'task' => ['required'],
        ]);

        $task = Task::resolveRef($validated['task']);

        if ($task === null) {
            return Response::error("Task \"{$validated['task']}\" not found.");
        }

        $code = $task->code;
        $title = $task->title;
        $this->tasks->delete($task);

        return Response::text(sprintf('Task %s "%s" deleted.', $code, $title));
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
        ];
    }
}
