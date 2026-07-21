<?php

use App\Models\Board;
use App\Models\User;
use Livewire\Livewire;

it('only lists boards owned by the current user', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Board::factory()->for($me)->create(['name' => 'Mine']);
    Board::factory()->for($other)->create(['name' => 'Theirs']);

    $this->actingAs($me);

    Livewire::test('board-index')
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});

it('creates a board owned by the current user', function () {
    $me = User::factory()->create();
    $this->actingAs($me);

    Livewire::test('board-index')
        ->set('name', 'Sprint 1')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('boards', ['name' => 'Sprint 1', 'user_id' => $me->id]);
});

it('validates the board name', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('board-index')
        ->set('name', '')
        ->call('create')
        ->assertHasErrors('name');
});

it('lets a user delete their own board', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->create();
    $this->actingAs($me);

    Livewire::test('board-index')->call('delete', $board);

    $this->assertDatabaseMissing('boards', ['id' => $board->id]);
});

it('forbids deleting a board owned by someone else', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for(User::factory()->create())->create();
    $this->actingAs($me);

    Livewire::test('board-index')->call('delete', $board)->assertForbidden();

    $this->assertDatabaseHas('boards', ['id' => $board->id]);
});
