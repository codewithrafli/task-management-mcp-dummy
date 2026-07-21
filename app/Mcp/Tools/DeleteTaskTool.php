<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Permanently delete a task. Requires an authorised (admin) user.')]
class DeleteTaskTool extends Tool
{
    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        // Secure tool design: a destructive action must be authorised.
        if (Gate::forUser($request->user())->denies('delete-tasks')) {
            return Response::error('You are not authorised to delete tasks.');
        }

        $validated = $request->validate([
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
        ]);

        $task = Task::findOrFail($validated['task_id']);
        $title = $task->title;
        $this->tasks->delete($task);

        return Response::text(sprintf('Task #%d "%s" deleted.', $validated['task_id'], $title));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The id of the task to delete.')
                ->required(),
        ];
    }
}
