<?php

use App\Mcp\Servers\TaskManagementServer;
use App\Mcp\Tools\AssignTaskTool;
use App\Mcp\Tools\ListTasksTool;
use App\Mcp\Tools\MyTasksTool;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;

it('assigns a task to a user', function () {
    $user = User::factory()->create(['name' => 'Budi']);
    $task = Task::factory()->create(['assignee_id' => null]);

    TaskManagementServer::tool(AssignTaskTool::class, [
        'task_id' => $task->id,
        'assignee_id' => $user->id,
    ])->assertOk()->assertSee('Budi');

    expect($task->refresh()->assignee_id)->toBe($user->id);
});

it('unassigns a task when assignee_id is omitted', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['assignee_id' => $user->id]);

    TaskManagementServer::tool(AssignTaskTool::class, ['task_id' => $task->id])
        ->assertOk()->assertSee('unassigned');

    expect($task->refresh()->assignee_id)->toBeNull();
});

it('lists the authenticated user\'s tasks with my-tasks-tool', function () {
    $me = User::factory()->create(['name' => 'Ani']);
    Task::factory()->create(['assignee_id' => $me->id, 'title' => 'Mine to do']);
    Task::factory()->create(['assignee_id' => null, 'title' => 'Not mine']);

    TaskManagementServer::actingAs($me)
        ->tool(MyTasksTool::class)
        ->assertOk()
        ->assertSee('Mine to do')
        ->assertDontSee('Not mine');
});

it('requires authentication for my-tasks-tool', function () {
    TaskManagementServer::tool(MyTasksTool::class)->assertHasErrors();
});

it('filters tasks by assignee via list-tasks-tool', function () {
    $board = Board::factory()->create();
    $user = User::factory()->create();
    Task::factory()->for($board)->count(2)->create(['assignee_id' => $user->id]);
    Task::factory()->for($board)->count(3)->create(['assignee_id' => null]);

    TaskManagementServer::tool(ListTasksTool::class, ['assignee_id' => $user->id])
        ->assertOk()->assertSee('"total":2');
});
