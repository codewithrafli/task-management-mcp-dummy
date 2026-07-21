<?php

namespace App\Mcp\Tools;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Assign a task to a user, or unassign it. Pass assignee_id to assign, or omit it to unassign.')]
class AssignTaskTool extends Tool
{
    public function __construct(private readonly TaskService $tasks) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'task' => ['required'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $task = Task::resolveRef($validated['task']);

        if ($task === null) {
            return Response::error("Task \"{$validated['task']}\" not found.");
        }

        $user = isset($validated['assignee_id']) ? User::find($validated['assignee_id']) : null;

        $this->tasks->assign($task, $user);

        return Response::text($user
            ? sprintf('Task %s "%s" assigned to %s.', $task->code, $task->title, $user->name)
            : sprintf('Task %s "%s" is now unassigned.', $task->code, $task->title)
        );
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'task' => $schema->string()
                ->description('The task code (e.g. "SPR-1") or numeric id to (re)assign.')
                ->required(),
            'assignee_id' => $schema->integer()
                ->description('The id of the user to assign. Omit to unassign.'),
        ];
    }
}
