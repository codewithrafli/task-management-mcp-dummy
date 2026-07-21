<?php

use App\Models\Board;
use App\Models\Task;
use App\Services\BoardService;

beforeEach(function () {
    $this->service = app(BoardService::class);
});

it('creates a board', function () {
    $board = $this->service->create([
        'name' => 'Sprint July',
        'description' => 'Release work',
    ]);

    expect($board->name)->toBe('Sprint July');
    $this->assertDatabaseHas('boards', ['name' => 'Sprint July']);
});

it('lists boards with their task counts', function () {
    $board = Board::factory()->create();
    Task::factory()->for($board)->count(3)->create();

    $boards = $this->service->all();

    expect($boards)->toHaveCount(1)
        ->and($boards->first()->tasks_count)->toBe(3);
});

it('updates and deletes a board', function () {
    $board = Board::factory()->create(['name' => 'Old']);

    $updated = $this->service->update($board, ['name' => 'New']);
    expect($updated->name)->toBe('New');

    $this->service->delete($board);
    $this->assertDatabaseMissing('boards', ['id' => $board->id]);
});
