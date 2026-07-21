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

<div class="flex h-[calc(100vh-3.25rem)] flex-col">
    {{-- Board bar --}}
    <div class="flex flex-wrap items-center gap-3 px-4 py-3 text-white sm:px-6">
        <a href="{{ route('boards.index') }}" class="text-sm text-white/70 hover:text-white">&larr; Boards</a>
        <span class="rounded bg-white/20 px-2 py-0.5 font-mono text-xs">{{ $board->code }}</span>
        <h1 class="text-xl font-bold drop-shadow-sm">{{ $board->name }}</h1>
        @if ($board->description)
            <span class="hidden text-sm text-white/70 md:inline">— {{ $board->description }}</span>
        @endif

        <form wire:submit="addTask" class="ml-auto flex items-center gap-2">
            <input type="text" wire:model="title" placeholder="Judul task baru…"
                class="w-44 rounded-md border-0 bg-white/95 px-3 py-1.5 text-sm text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-white sm:w-56">
            <select wire:model="priority"
                class="rounded-md border-0 bg-white/95 px-2 py-1.5 text-sm text-slate-800 focus:ring-2 focus:ring-white">
                @foreach (TaskPriority::cases() as $p)
                    <option value="{{ $p->value }}">{{ $p->label() }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-indigo-700 shadow hover:bg-white/90">
                + Tambah
            </button>
        </form>
    </div>
    @error('title') <p class="px-4 pb-1 text-xs text-amber-200 sm:px-6">{{ $message }}</p> @enderror

    {{-- Lists --}}
    <div class="flex flex-1 gap-3 overflow-x-auto px-4 pb-4 sm:px-6">
        @foreach ($columns as $column)
            @php
                $accent = match ($column) {
                    \App\Enums\TaskStatus::Todo => 'bg-slate-400',
                    \App\Enums\TaskStatus::InProgress => 'bg-amber-400',
                    \App\Enums\TaskStatus::Done => 'bg-emerald-400',
                };
            @endphp
            <div class="flex max-h-full w-72 flex-none flex-col rounded-xl bg-slate-100/95 shadow-lg">
                <div class="flex items-center justify-between px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full {{ $accent }}"></span>
                        <h3 class="text-sm font-semibold text-slate-700">{{ $column->label() }}</h3>
                    </div>
                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600">
                        {{ optional($tasksByStatus->get($column->value))->count() ?? 0 }}
                    </span>
                </div>

                <div x-data
                    x-init="Sortable.create($refs.list, {
                        group: 'tasks',
                        animation: 150,
                        ghostClass: 'opacity-40',
                        onAdd: (e) => $wire.updateStatus(e.item.dataset.id, '{{ $column->value }}'),
                    })"
                    x-ref="list"
                    class="flex-1 space-y-2 overflow-y-auto px-2 pb-2">
                    @foreach ($tasksByStatus->get($column->value, []) as $task)
                        <div wire:key="task-{{ $task->id }}" data-id="{{ $task->id }}"
                            class="group cursor-grab rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md active:cursor-grabbing">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-mono text-[11px] font-medium text-slate-400">{{ $task->code }}</span>
                                <button wire:click="deleteTask({{ $task->id }})"
                                    class="-mt-1 text-slate-300 opacity-0 transition group-hover:opacity-100 hover:text-red-500">&times;</button>
                            </div>
                            <p class="mt-0.5 text-sm text-slate-800">{{ $task->title }}</p>
                            <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                <span @class([
                                    'inline-block rounded px-1.5 py-0.5 text-[11px] font-semibold',
                                    'bg-slate-100 text-slate-500' => $task->priority === TaskPriority::Low,
                                    'bg-amber-100 text-amber-700' => $task->priority === TaskPriority::Medium,
                                    'bg-red-100 text-red-700' => $task->priority === TaskPriority::High,
                                ])>{{ $task->priority->label() }}</span>

                                @if ($task->due_date)
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] font-medium',
                                        'bg-red-100 text-red-700' => $task->due_date->isPast() && $task->status !== \App\Enums\TaskStatus::Done,
                                        'bg-slate-100 text-slate-500' => ! ($task->due_date->isPast() && $task->status !== \App\Enums\TaskStatus::Done),
                                    ])>📅 {{ $task->due_date->format('d M') }}</span>
                                @endif

                                @if ($task->assignee)
                                    <span class="ml-auto grid h-6 w-6 place-items-center rounded-full bg-indigo-500 text-[11px] font-semibold text-white"
                                        title="{{ $task->assignee->name }}">
                                        {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if (($tasksByStatus->get($column->value)?->count() ?? 0) === 0)
                        <p class="rounded-lg border border-dashed border-slate-300 py-6 text-center text-xs text-slate-400">
                            Tarik task ke sini
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
