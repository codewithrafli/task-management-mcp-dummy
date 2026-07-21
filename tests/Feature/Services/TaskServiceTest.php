<?php

use App\Enums\TaskStatus;
use App\Models\Board;
use App\Models\Task;
use App\Services\TaskService;

beforeEach(function () {
    $this->service = app(TaskService::class);
});

it('creates a task and auto-assigns the next position', function () {
    $board = Board::factory()->create();
    Task::factory()->for($board)->create(['status' => TaskStatus::Todo->value, 'position' => 5]);

    $task = $this->service->create([
        'board_id' => $board->id,
        'title' => 'New task',
        'status' => TaskStatus::Todo->value,
    ]);

    expect($task->position)->toBe(6)
        ->and($task->title)->toBe('New task')
        ->and($task->status)->toBe(TaskStatus::Todo);
});

it('changes the status of a task', function () {
    $task = Task::factory()->create(['status' => TaskStatus::Todo->value]);

    $updated = $this->service->changeStatus($task, TaskStatus::Done);

    expect($updated->status)->toBe(TaskStatus::Done);
});

it('moves a task to another board at the next position', function () {
    $from = Board::factory()->create();
    $to = Board::factory()->create();
    Task::factory()->for($to)->create(['position' => 2]);
    $task = Task::factory()->for($from)->create();

    $moved = $this->service->move($task, $to);

    expect($moved->board_id)->toBe($to->id)
        ->and($moved->position)->toBe(3);
});

it('searches tasks by keyword in title or description', function () {
    $board = Board::factory()->create();
    Task::factory()->for($board)->create(['title' => 'Design the landing page']);
    Task::factory()->for($board)->create(['title' => 'Unrelated task', 'description' => 'about design system']);
    Task::factory()->for($board)->create(['title' => 'Nothing here']);

    $results = $this->service->search('design');

    expect($results)->toHaveCount(2);
});
