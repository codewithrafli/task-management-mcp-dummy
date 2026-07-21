<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Mcp\Prompts\StandupPrompt;
use App\Mcp\Resources\BoardTasksResource;
use App\Mcp\Servers\TaskManagementServer;
use App\Mcp\Tools\BulkCreateTasksTool;
use App\Mcp\Tools\ListTasksTool;
use App\Models\Board;
use App\Models\Task;

it('paginates and filters tasks', function () {
    $board = Board::factory()->create();
    Task::factory()->for($board)->count(15)->create(['status' => TaskStatus::Todo->value]);
    Task::factory()->for($board)->count(3)->create(['status' => TaskStatus::Done->value]);

    $response = TaskManagementServer::tool(ListTasksTool::class, [
        'status' => TaskStatus::Todo->value,
        'per_page' => 10,
        'page' => 1,
    ]);

    $response->assertOk()->assertSee([
        '"total":15',
        '"last_page":2',
        '"has_more":true',
    ]);
});

it('rejects an out-of-range per_page', function () {
    TaskManagementServer::tool(ListTasksTool::class, ['per_page' => 1000])
        ->assertHasErrors();
});

it('reads tasks for a board via a dynamic resource uri', function () {
    $board = Board::factory()->create(['name' => 'Sprint']);
    Task::factory()->for($board)->create(['title' => 'Dynamic task']);

    TaskManagementServer::resource(BoardTasksResource::class, ['boardId' => $board->id])
        ->assertOk()
        ->assertSee('Dynamic task');
});

it('builds a standup prompt for a board', function () {
    Board::factory()->create(['name' => 'Ops']);

    TaskManagementServer::prompt(StandupPrompt::class, [
        'board' => 'Ops',
        'audience' => 'stakeholders',
    ])->assertOk()->assertSee('stakeholders');
});

it('bulk-creates tasks and streams progress notifications', function () {
    $board = Board::factory()->create();

    $response = TaskManagementServer::tool(BulkCreateTasksTool::class, [
        'board_id' => $board->id,
        'titles' => ['One', 'Two', 'Three'],
    ]);

    $response->assertOk()->assertSee('Created 3 task(s)');

    expect(Task::where('board_id', $board->id)->count())->toBe(3);
});
