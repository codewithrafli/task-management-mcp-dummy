<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function create(array $data): Task
    {
        $data['position'] ??= (int) Task::where('board_id', $data['board_id'])
            ->where('status', $data['status'] ?? TaskStatus::Todo->value)
            ->max('position') + 1;

        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    public function changeStatus(Task $task, TaskStatus $status): Task
    {
        return $this->update($task, ['status' => $status->value]);
    }

    public function assign(Task $task, ?User $user): Task
    {
        return $this->update($task, ['assignee_id' => $user?->id]);
    }

    public function move(Task $task, Board $board, ?int $position = null): Task
    {
        return $this->update($task, [
            'board_id' => $board->id,
            'position' => $position ?? (int) Task::where('board_id', $board->id)->max('position') + 1,
        ]);
    }

    /**
     * Persist the order (and status) of a column after a drag-and-drop.
     *
     * @param  array<int, int|string>  $orderedIds
     */
    public function reorder(Board $board, TaskStatus $status, array $orderedIds): void
    {
        DB::transaction(function () use ($board, $status, $orderedIds) {
            foreach (array_values($orderedIds) as $index => $id) {
                Task::where('board_id', $board->id)
                    ->whereKey($id)
                    ->update([
                        'status' => $status->value,
                        'position' => $index + 1,
                    ]);
            }
        });
    }

    public function search(string $term, ?Board $board = null): Collection
    {
        return Task::query()
            ->when($board, fn ($q) => $q->where('board_id', $board->id))
            ->search($term)
            ->orderBy('position')
            ->get();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
