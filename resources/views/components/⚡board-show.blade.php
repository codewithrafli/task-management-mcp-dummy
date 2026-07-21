<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Board;
use App\Models\Task;
use App\Services\TaskService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('components.layouts.app')] class extends Component
{
    public Board $board;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|in:low,medium,high')]
    public string $priority = 'medium';

    public function mount(Board $board): void
    {
        $this->board = $board;
    }

    public function addTask(TaskService $tasks): void
    {
        $data = $this->validate();

        $tasks->create([
            'board_id' => $this->board->id,
            'title' => $data['title'],
            'priority' => $data['priority'],
            'status' => TaskStatus::Todo->value,
        ]);

        $this->reset('title', 'priority');
        $this->priority = 'medium';
    }

    public function updateStatus(int $taskId, string $status, TaskService $tasks): void
    {
        $task = Task::where('board_id', $this->board->id)->findOrFail($taskId);

        $tasks->changeStatus($task, TaskStatus::from($status));
    }

    public function deleteTask(int $taskId, TaskService $tasks): void
    {
        $task = Task::where('board_id', $this->board->id)->findOrFail($taskId);

        $tasks->delete($task);
    }

    public function with(): array
    {
        $tasks = $this->board->tasks()->orderBy('position')->get()->groupBy(fn ($t) => $t->status->value);

        return [
            'columns' => TaskStatus::cases(),
            'tasksByStatus' => $tasks,
        ];
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('boards.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Semua board</a>
            <h1 class="mt-1 text-2xl font-bold">{{ $board->name }}</h1>
            <p class="text-sm text-gray-500">{{ $board->description }}</p>
        </div>
    </div>

    <form wire:submit="addTask" class="mb-8 flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm sm:flex-row">
        <div class="flex-1">
            <input type="text" wire:model="title" placeholder="Judul task baru"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <select wire:model="priority"
            class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            @foreach (TaskPriority::cases() as $p)
                <option value="{{ $p->value }}">{{ $p->label() }}</option>
            @endforeach
        </select>
        <button type="submit"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Tambah Task
        </button>
    </form>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($columns as $column)
            <div class="rounded-lg bg-gray-50 p-3"
                x-data
                x-init="Sortable.create($refs.list, {
                    group: 'tasks',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    onAdd: (e) => $wire.updateStatus(e.item.dataset.id, '{{ $column->value }}'),
                })">
                <div class="mb-3 flex items-center justify-between px-1">
                    <h3 class="text-sm font-semibold">{{ $column->label() }}</h3>
                    <span class="rounded-full bg-gray-200 px-2 text-xs text-gray-600">
                        {{ optional($tasksByStatus->get($column->value))->count() ?? 0 }}
                    </span>
                </div>

                <div x-ref="list" class="min-h-[120px] space-y-2">
                    @foreach ($tasksByStatus->get($column->value, []) as $task)
                        <div wire:key="task-{{ $task->id }}" data-id="{{ $task->id }}"
                            class="group cursor-grab rounded-md bg-white p-3 shadow-sm active:cursor-grabbing">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm">{{ $task->title }}</p>
                                <button wire:click="deleteTask({{ $task->id }})"
                                    class="text-gray-300 opacity-0 transition group-hover:opacity-100 hover:text-red-500">&times;</button>
                            </div>
                            <span @class([
                                'mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-gray-100 text-gray-600' => $task->priority === TaskPriority::Low,
                                'bg-amber-100 text-amber-700' => $task->priority === TaskPriority::Medium,
                                'bg-red-100 text-red-700' => $task->priority === TaskPriority::High,
                            ])>{{ $task->priority->label() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
