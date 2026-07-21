<?php

namespace App\Mcp\Resources;

use App\Models\Board;
use App\Models\Task;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Description('Tasks for a specific board, addressed dynamically by board id.')]
class BoardTasksResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('board://{boardId}/tasks');
    }

    public function handle(Request $request): Response
    {
        $boardId = (int) $request->get('boardId');
        $board = Board::find($boardId);

        if (! $board) {
            return Response::error("Board #{$boardId} not found.");
        }

        $tasks = $board->tasks()
            ->orderBy('position')
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'code' => $task->code,
                'assignee_id' => $task->assignee_id,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'due_date' => $task->due_date?->toDateString(),
                'position' => $task->position,
            ]);

        return Response::json([
            'board' => ['id' => $board->id, 'name' => $board->name],
            'tasks' => $tasks,
        ]);
    }
}
