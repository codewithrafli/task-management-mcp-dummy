<?php

use App\Enums\TaskStatus;
use App\Mcp\Servers\TaskManagementServer;
use App\Mcp\Tools\InviteMemberTool;
use App\Mcp\Tools\ListMembersTool;
use App\Mcp\Tools\ListTasksTool;
use App\Mcp\Tools\RenameBoardTool;
use App\Mcp\Tools\UpdateTaskStatusTool;
use App\Mcp\Tools\UpdateTaskTool;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;

it('only lists tasks on boards the authenticated user can access', function () {
    $me = User::factory()->create();
    $mine = Board::factory()->for($me)->create();
    Task::factory()->for($mine)->create(['title' => 'Mine']);

    $foreign = Board::factory()->for(User::factory()->create())->create();
    Task::factory()->for($foreign)->create(['title' => 'Theirs']);

    TaskManagementServer::actingAs($me)
        ->tool(ListTasksTool::class, [])
        ->assertOk()
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});

it('cannot modify a task on an inaccessible board', function () {
    $me = User::factory()->create();
    $foreign = Task::factory()->for(Board::factory()->for(User::factory()->create()))->create();

    TaskManagementServer::actingAs($me)
        ->tool(UpdateTaskStatusTool::class, ['task' => $foreign->code, 'status' => 'done'])
        ->assertHasErrors();

    expect($foreign->refresh()->status)->not->toBe(TaskStatus::Done);
});

it('updates a task via update-task-tool', function () {
    $me = User::factory()->create();
    $task = Task::factory()->for(Board::factory()->for($me))->create();

    TaskManagementServer::actingAs($me)
        ->tool(UpdateTaskTool::class, [
            'task' => $task->code,
            'title' => 'Renamed',
            'priority' => 'high',
            'due_date' => '2026-08-01',
        ])->assertOk();

    expect($task->refresh())
        ->title->toBe('Renamed')
        ->due_date->toDateString()->toBe('2026-08-01');
});

it('renames a board via rename-board-tool', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->create();

    TaskManagementServer::actingAs($me)
        ->tool(RenameBoardTool::class, ['board' => $board->code, 'name' => 'New Name'])
        ->assertOk();

    expect($board->refresh()->name)->toBe('New Name');
});

it('lists the members of a board', function () {
    $me = User::factory()->create(['name' => 'Owner']);
    $board = Board::factory()->for($me)->create();
    $board->members()->attach(User::factory()->create(['name' => 'Member']));

    TaskManagementServer::actingAs($me)
        ->tool(ListMembersTool::class, ['board' => $board->code])
        ->assertOk()
        ->assertSee('Owner')
        ->assertSee('Member');
});

it('lets the owner invite a member by email', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->create();
    $invitee = User::factory()->create(['email' => 'new@example.com']);

    TaskManagementServer::actingAs($me)
        ->tool(InviteMemberTool::class, ['board' => $board->code, 'email' => 'new@example.com'])
        ->assertOk();

    expect($board->members()->whereKey($invitee->id)->exists())->toBeTrue();
});

it('forbids a non-owner from inviting members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $board = Board::factory()->for($owner)->create();
    $board->members()->attach($member);

    TaskManagementServer::actingAs($member)
        ->tool(InviteMemberTool::class, ['board' => $board->code, 'email' => User::factory()->create()->email])
        ->assertHasErrors();
});
