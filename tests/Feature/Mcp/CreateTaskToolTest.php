<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Mcp\Servers\TaskManagementServer;
use App\Mcp\Tools\CreateTaskTool;
use App\Models\Board;

it('creates a task inside a board via the MCP tool', function () {
    $board = Board::factory()->create(['name' => 'Backlog']);

    $response = TaskManagementServer::tool(CreateTaskTool::class, [
        'board_id' => $board->id,
        'title' => 'Write docs',
        'priority' => TaskPriority::High->value,
    ]);

    $response->assertOk()->assertSee('Write docs');
    $this->assertDatabaseHas('tasks', [
        'board_id' => $board->id,
        'title' => 'Write docs',
        'priority' => TaskPriority::High->value,
        'status' => TaskStatus::Todo->value,
    ]);
});

it('rejects a task on a non-existent board', function () {
    TaskManagementServer::tool(CreateTaskTool::class, [
        'board_id' => 999,
        'title' => 'Orphan',
    ])->assertHasErrors();
});

it('rejects an invalid priority', function () {
    $board = Board::factory()->create();

    TaskManagementServer::tool(CreateTaskTool::class, [
        'board_id' => $board->id,
        'title' => 'Bad priority',
        'priority' => 'urgent',
    ])->assertHasErrors();
});
