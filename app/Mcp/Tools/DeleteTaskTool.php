<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\InteractsWithBoards;
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
    use InteractsWithBoards;

    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        // Secure tool design: a destructive action must be authorised.
        if (Gate::forUser($request->user())->denies('delete-tasks')) {
            return Response::error('You are not authorised to delete tasks.');
        }

        $validated = $request->validate([
            'task' => ['required'],
        ]);

        $task = $this->resolveTask($validated['task'], $request->user());

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
