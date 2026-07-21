<?php

use App\Enums\TaskStatus;
use App\Mcp\Servers\TaskManagementServer;
use App\Mcp\Tools\UpdateTaskStatusTool;
use App\Models\Board;
use App\Models\Task;

it('generates a board code from the name', function () {
    $board = Board::factory()->create(['name' => 'Sprint Release']);

    expect($board->code)->toBe('SPR');
});

it('generates sequential task codes per board', function () {
    $board = Board::factory()->create(['name' => 'Ops']);

    $a = Task::factory()->for($board)->create();
    $b = Task::factory()->for($board)->create();

    expect($a->code)->toBe('OPS-1')
        ->and($b->code)->toBe('OPS-2');
});

it('updates a task status by its code', function () {
    $board = Board::factory()->create(['name' => 'Bugs']);
    $task = Task::factory()->for($board)->create(['status' => TaskStatus::Todo->value]);

    TaskManagementServer::tool(UpdateTaskStatusTool::class, [
        'task' => $task->code, // e.g. "BUG-1"
        'status' => TaskStatus::Done->value,
    ])->assertOk()->assertSee($task->code);

    expect($task->refresh()->status)->toBe(TaskStatus::Done);
});

it('returns a friendly error for an unknown task code', function () {
    TaskManagementServer::tool(UpdateTaskStatusTool::class, [
        'task' => 'NOPE-999',
        'status' => TaskStatus::Done->value,
    ])->assertHasErrors();
});
