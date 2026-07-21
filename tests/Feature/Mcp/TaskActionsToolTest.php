<?php

use App\Enums\TaskStatus;
use App\Mcp\Servers\TaskManagementServer;
use App\Mcp\Tools\MoveTaskTool;
use App\Mcp\Tools\SearchTaskTool;
use App\Mcp\Tools\UpdateTaskStatusTool;
use App\Models\Board;
use App\Models\Task;

it('updates a task status via the MCP tool', function () {
    $task = Task::factory()->create(['status' => TaskStatus::Todo->value]);

    TaskManagementServer::tool(UpdateTaskStatusTool::class, [
        'task' => $task->id,
        'status' => TaskStatus::Done->value,
    ])->assertOk()->assertSee('done');

    expect($task->refresh()->status)->toBe(TaskStatus::Done);
});

it('rejects an invalid status', function () {
    $task = Task::factory()->create();

    TaskManagementServer::tool(UpdateTaskStatusTool::class, [
        'task' => $task->id,
        'status' => 'archived',
    ])->assertHasErrors();
});

it('moves a task to another board via the MCP tool', function () {
    $from = Board::factory()->create();
    $to = Board::factory()->create(['name' => 'Done Board']);
    $task = Task::factory()->for($from)->create();

    TaskManagementServer::tool(MoveTaskTool::class, [
        'task' => $task->id,
        'board_id' => $to->id,
    ])->assertOk()->assertSee('Done Board');

    expect($task->refresh()->board_id)->toBe($to->id);
});

it('searches tasks via the MCP tool', function () {
    $board = Board::factory()->create();
    Task::factory()->for($board)->create(['title' => 'Design review']);

    TaskManagementServer::tool(SearchTaskTool::class, [
        'query' => 'design',
    ])->assertOk()->assertSee('Design review');
});

it('reports when no tasks match the search', function () {
    TaskManagementServer::tool(SearchTaskTool::class, [
        'query' => 'zzz-nothing',
    ])->assertOk()->assertSee('No tasks found');
});
