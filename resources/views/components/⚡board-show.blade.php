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
        $tasks = $this->board->tasks()->with('assignee')->orderBy('position')->get()->groupBy(fn ($t) => $t->status->value);

        return [
            'columns' => TaskStatus::cases(),
            'tasksByStatus' => $tasks,
        ];
    }
};
?>

<div class="flex h-[calc(100vh-3rem)] flex-col">
    {{-- Board header --}}
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 border-b border-neutral-200 bg-white px-4 py-2.5">
        <a href="{{ route('boards.index') }}" class="text-neutral-400 hover:text-neutral-700">Boards</a>
        <span class="text-neutral-300">/</span>
        <span class="font-mono text-xs text-neutral-400">{{ $board->code }}</span>
        <h1 class="font-semibold text-neutral-900">{{ $board->name }}</h1>
        @if ($board->description)
            <span class="hidden text-neutral-400 md:inline">{{ $board->description }}</span>
        @endif

        <form wire:submit="addTask" class="ml-auto flex items-center gap-2">
            <input type="text" wire:model="title" placeholder="Task baru…"
                class="w-40 rounded-md border border-neutral-200 bg-white px-2.5 py-1.5 text-neutral-800 placeholder:text-neutral-400 focus:border-neutral-400 focus:outline-none sm:w-52">
            <select wire:model="priority"
                class="rounded-md border border-neutral-200 bg-white px-2 py-1.5 text-neutral-600 focus:border-neutral-400 focus:outline-none">
                @foreach (TaskPriority::cases() as $p)
                    <option value="{{ $p->value }}">{{ $p->label() }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="rounded-md bg-neutral-900 px-3 py-1.5 font-medium text-white hover:bg-neutral-700">
                Tambah
            </button>
        </form>
        @error('title') <p class="w-full text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Columns --}}
    <div class="flex flex-1 gap-4 overflow-x-auto p-4">
        @foreach ($columns as $column)
            @php
                $dot = match ($column) {
                    \App\Enums\TaskStatus::Todo => 'bg-neutral-400',
                    \App\Enums\TaskStatus::InProgress => 'bg-amber-500',
                    \App\Enums\TaskStatus::Done => 'bg-emerald-500',
                };
            @endphp
            <div class="flex max-h-full w-72 flex-none flex-col">
                <div class="mb-2 flex items-center gap-2 px-1">
                    <span class="h-2 w-2 rounded-full {{ $dot }}"></span>
                    <h3 class="font-medium text-neutral-700">{{ $column->label() }}</h3>
                    <span class="text-neutral-400">{{ optional($tasksByStatus->get($column->value))->count() ?? 0 }}</span>
                </div>

                <div x-data
                    x-init="Sortable.create($refs.list, {
                        group: 'tasks',
                        animation: 150,
                        ghostClass: 'opacity-40',
                        onAdd: (e) => $wire.updateStatus(e.item.dataset.id, '{{ $column->value }}'),
                    })"
                    x-ref="list"
                    class="flex-1 space-y-1.5 overflow-y-auto rounded-lg bg-neutral-100/70 p-1.5">
                    @foreach ($tasksByStatus->get($column->value, []) as $task)
                        @php
                            $prioDot = match ($task->priority) {
                                TaskPriority::Low => 'bg-neutral-300',
                                TaskPriority::Medium => 'bg-amber-500',
                                TaskPriority::High => 'bg-red-500',
                            };
                            $overdue = $task->due_date && $task->due_date->isPast() && $task->status !== \App\Enums\TaskStatus::Done;
                        @endphp
                        <div wire:key="task-{{ $task->id }}" data-id="{{ $task->id }}"
                            class="group cursor-grab rounded-md border border-neutral-200 bg-white p-2.5 hover:border-neutral-300 active:cursor-grabbing">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-[11px] text-neutral-400">{{ $task->code }}</span>
                                <button wire:click="deleteTask({{ $task->id }})"
                                    class="text-neutral-300 opacity-0 transition group-hover:opacity-100 hover:text-neutral-700">&times;</button>
                            </div>
                            <p class="mt-1 text-neutral-800">{{ $task->title }}</p>
                            <div class="mt-2 flex items-center gap-2 text-neutral-500">
                                <span class="inline-flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $prioDot }}"></span>
                                    {{ $task->priority->label() }}
                                </span>

                                @if ($task->due_date)
                                    <span class="{{ $overdue ? 'text-red-600' : '' }}">· {{ $task->due_date->format('d M') }}</span>
                                @endif

                                @if ($task->assignee)
                                    <span class="ml-auto grid h-5 w-5 place-items-center rounded-full bg-neutral-200 text-[10px] font-medium text-neutral-600"
                                        title="{{ $task->assignee->name }}">
                                        {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if (($tasksByStatus->get($column->value)?->count() ?? 0) === 0)
                        <p class="px-2 py-6 text-center text-xs text-neutral-400">Kosong</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
