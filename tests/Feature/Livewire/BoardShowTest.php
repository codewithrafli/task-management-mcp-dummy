<?php

use App\Enums\TaskStatus;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->board = Board::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

it('forbids viewing a board owned by someone else', function () {
    $board = Board::factory()->for(User::factory()->create())->create();

    Livewire::test('board-show', ['board' => $board])->assertForbidden();
});

it('allows a member to view the board', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $board = Board::factory()->for($owner)->create();
    $board->members()->attach($member);

    $this->actingAs($member);

    Livewire::test('board-show', ['board' => $board])->assertOk();
});

it('lets the owner invite a member by email', function () {
    $invitee = User::factory()->create(['email' => 'new@example.com']);

    Livewire::test('board-show', ['board' => $this->board])
        ->set('inviteEmail', 'new@example.com')
        ->call('invite')
        ->assertHasNoErrors();

    expect($this->board->members()->whereKey($invitee->id)->exists())->toBeTrue();
});

it('rejects inviting an email without an account', function () {
    Livewire::test('board-show', ['board' => $this->board])
        ->set('inviteEmail', 'ghost@example.com')
        ->call('invite')
        ->assertHasErrors('inviteEmail');
});

it('forbids a member from managing members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $board = Board::factory()->for($owner)->create();
    $board->members()->attach($member);
    $this->actingAs($member);

    Livewire::test('board-show', ['board' => $board])
        ->set('inviteEmail', User::factory()->create()->email)
        ->call('invite')
        ->assertForbidden();
});

it('adds a task to the board', function () {
    Livewire::test('board-show', ['board' => $this->board])
        ->set('title', 'Write docs')
        ->set('priority', 'high')
        ->call('addTask')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tasks', [
        'board_id' => $this->board->id,
        'title' => 'Write docs',
        'status' => TaskStatus::Todo->value,
    ]);
});

it('persists status and position when a column is reordered', function () {
    $a = Task::factory()->for($this->board)->create(['status' => TaskStatus::Todo->value]);
    $b = Task::factory()->for($this->board)->create(['status' => TaskStatus::Todo->value]);

    // Drag both into "done" with b before a.
    Livewire::test('board-show', ['board' => $this->board])
        ->call('reorderColumn', 'done', [$b->id, $a->id]);

    expect($b->refresh())->status->toBe(TaskStatus::Done)->position->toBe(1)
        ->and($a->refresh())->status->toBe(TaskStatus::Done)->position->toBe(2);
});

it('ignores tasks from other boards when reordering', function () {
    $foreign = Task::factory()->create(['status' => TaskStatus::Todo->value]);

    Livewire::test('board-show', ['board' => $this->board])
        ->call('reorderColumn', 'done', [$foreign->id]);

    expect($foreign->refresh()->status)->toBe(TaskStatus::Todo);
});

it('renames the board', function () {
    Livewire::test('board-show', ['board' => $this->board])
        ->set('boardName', 'Renamed Board')
        ->set('boardDescription', 'New desc')
        ->call('saveBoard')
        ->assertHasNoErrors();

    expect($this->board->refresh())
        ->name->toBe('Renamed Board')
        ->description->toBe('New desc');
});

it('forbids a member from renaming the board', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $board = Board::factory()->for($owner)->create(['name' => 'Original']);
    $board->members()->attach($member);
    $this->actingAs($member);

    Livewire::test('board-show', ['board' => $board])
        ->set('boardName', 'Hacked')
        ->call('saveBoard')
        ->assertForbidden();

    expect($board->refresh()->name)->toBe('Original');
});

it('filters the board to only my tasks', function () {
    $mine = Task::factory()->for($this->board)->create(['assignee_id' => $this->user->id, 'title' => 'My task']);
    $other = Task::factory()->for($this->board)->create(['assignee_id' => null, 'title' => 'Other task']);

    Livewire::test('board-show', ['board' => $this->board])
        ->set('filterAssignee', 'mine')
        ->assertSee('My task')
        ->assertDontSee('Other task');
});

it('filters the board by priority', function () {
    Task::factory()->for($this->board)->create(['priority' => 'high', 'title' => 'Urgent']);
    Task::factory()->for($this->board)->create(['priority' => 'low', 'title' => 'Whenever']);

    Livewire::test('board-show', ['board' => $this->board])
        ->set('filterPriority', 'high')
        ->assertSee('Urgent')
        ->assertDontSee('Whenever');
});

it('updates a task via the detail modal', function () {
    $task = Task::factory()->for($this->board)->create(['status' => TaskStatus::Todo->value]);

    Livewire::test('board-show', ['board' => $this->board])
        ->call('openTask', $task->id)
        ->set('editTitle', 'Renamed')
        ->set('editStatus', 'in_progress')
        ->set('editPriority', 'high')
        ->call('saveTask')
        ->assertHasNoErrors();

    expect($task->refresh())
        ->title->toBe('Renamed')
        ->status->toBe(TaskStatus::InProgress);
});
