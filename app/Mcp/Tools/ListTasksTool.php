<?php

namespace App\Mcp\Tools;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List tasks with optional filtering (board, status, priority) and pagination. Returns a page of tasks plus pagination metadata.')]
class ListTasksTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'board_id' => ['nullable', 'integer', 'exists:boards,id'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'in:'.implode(',', TaskStatus::values())],
            'priority' => ['nullable', 'in:'.implode(',', TaskPriority::values())],
            'overdue' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $paginator = Task::query()
            ->when($validated['board_id'] ?? null, fn ($q, $id) => $q->where('board_id', $id))
            ->when($validated['assignee_id'] ?? null, fn ($q, $id) => $q->where('assignee_id', $id))
            ->when($validated['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($validated['priority'] ?? null, fn ($q, $p) => $q->where('priority', $p))
            ->when($validated['overdue'] ?? false, fn ($q) => $q->overdue())
            ->orderBy('board_id')
            ->orderBy('position')
            ->paginate(perPage: $perPage, page: $validated['page'] ?? 1);

        $data = collect($paginator->items())->map(fn (Task $task) => [
            'id' => $task->id,
            'board_id' => $task->board_id,
            'assignee_id' => $task->assignee_id,
            'title' => $task->title,
            'status' => $task->status->value,
            'priority' => $task->priority->value,
            'due_date' => $task->due_date?->toDateString(),
            'position' => $task->position,
        ]);

        return Response::json([
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'board_id' => $schema->integer()->description('Filter by board id.'),
            'assignee_id' => $schema->integer()->description('Filter by assignee (user) id.'),
            'overdue' => $schema->boolean()->description('If true, only tasks past their due date and not done.'),
            'status' => $schema->string()->description('Filter by status: '.implode(', ', TaskStatus::values()).'.'),
            'priority' => $schema->string()->description('Filter by priority: '.implode(', ', TaskPriority::values()).'.'),
            'page' => $schema->integer()->description('Page number (default 1).'),
            'per_page' => $schema->integer()->description('Items per page (default 10, max 100).'),
        ];
    }
}
