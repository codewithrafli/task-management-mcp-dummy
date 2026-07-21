<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Validation\Rule;
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

    // Task detail modal state.
    public ?int $editingId = null;

    public string $editCode = '';

    public string $editTitle = '';

    public string $editStatus = 'todo';

    public string $editPriority = 'medium';

    public ?string $editDueDate = null;

    public ?int $editAssignee = null;

    // Member invite state.
    public string $inviteEmail = '';

    // Board settings (rename) state.
    public string $boardName = '';

    public string $boardDescription = '';

    // Filters.
    public string $filterAssignee = 'all'; // all | mine | <user id>

    public string $filterPriority = 'all';

    public function mount(Board $board): void
    {
        $this->authorize('view', $board);

        $this->board = $board;
        $this->boardName = $board->name;
        $this->boardDescription = (string) $board->description;
    }

    public function addTask(TaskService $tasks): void
    {
        $this->authorize('update', $this->board);

        $data = $this->validate();

        $tasks->create([
            'board_id' => $this->board->id,
            'title' => $data['title'],
            'priority' => $data['priority'],
            'status' => TaskStatus::Todo->value,
        ]);

        $this->reset('title', 'priority');
        $this->priority = 'medium';

        $this->dispatch('task-created');
    }

    /**
     * @param  array<int, int|string>  $orderedIds
     */
    public function reorderColumn(string $status, array $orderedIds, TaskService $tasks): void
    {
        $this->authorize('update', $this->board);

        // Only touch tasks that actually belong to this board.
        $ids = Task::where('board_id', $this->board->id)
            ->whereIn('id', $orderedIds)
            ->pluck('id')
            ->all();

        $ordered = array_values(array_filter($orderedIds, fn ($id) => in_array((int) $id, $ids, false)));

        $tasks->reorder($this->board, TaskStatus::from($status), $ordered);
    }

    public function openTask(int $taskId): void
    {
        $task = Task::where('board_id', $this->board->id)->findOrFail($taskId);

        $this->editingId = $task->id;
        $this->editCode = $task->code;
        $this->editTitle = $task->title;
        $this->editStatus = $task->status->value;
        $this->editPriority = $task->priority->value;
        $this->editDueDate = $task->due_date?->toDateString();
        $this->editAssignee = $task->assignee_id;

        $this->dispatch('open-task');
    }

    public function saveTask(TaskService $tasks): void
    {
        $this->authorize('update', $this->board);

        $this->validate([
            'editTitle' => ['required', 'string', 'max:255'],
            'editStatus' => ['required', 'in:'.implode(',', TaskStatus::values())],
            'editPriority' => ['required', 'in:'.implode(',', TaskPriority::values())],
            'editDueDate' => ['nullable', 'date'],
            'editAssignee' => ['nullable', 'integer', Rule::in($this->board->teamIds())],
        ], ['editAssignee.in' => 'The assignee must be a member of this board.']);

        $task = Task::where('board_id', $this->board->id)->findOrFail($this->editingId);

        $tasks->update($task, [
            'title' => $this->editTitle,
            'status' => $this->editStatus,
            'priority' => $this->editPriority,
            'due_date' => $this->editDueDate ?: null,
            'assignee_id' => $this->editAssignee ?: null,
        ]);

        $this->dispatch('close-task');
    }

    public function deleteTask(int $taskId, TaskService $tasks): void
    {
        $this->authorize('update', $this->board);

        $task = Task::where('board_id', $this->board->id)->findOrFail($taskId);

        $tasks->delete($task);

        if ($this->editingId === $taskId) {
            $this->dispatch('close-task');
        }
    }

    public function saveBoard(): void
    {
        $this->authorize('manageSettings', $this->board);

        $data = $this->validate([
            'boardName' => ['required', 'string', 'max:255'],
            'boardDescription' => ['nullable', 'string'],
        ]);

        $this->board->update([
            'name' => $data['boardName'],
            'description' => $data['boardDescription'] ?: null,
        ]);

        $this->dispatch('board-saved');
    }

    public function invite(): void
    {
        $this->authorize('manageMembers', $this->board);

        $this->validate(['inviteEmail' => ['required', 'email', 'exists:users,email']]);

        $user = User::where('email', $this->inviteEmail)->firstOrFail();

        if ($user->id !== $this->board->user_id) {
            $this->board->members()->syncWithoutDetaching([$user->id]);
        }

        $this->reset('inviteEmail');
        $this->board->load('members');
    }

    public function removeMember(int $userId): void
    {
        $this->authorize('manageMembers', $this->board);

        $this->board->members()->detach($userId);

        // Unassign any tasks that were assigned to the removed member.
        Task::where('board_id', $this->board->id)
            ->where('assignee_id', $userId)
            ->update(['assignee_id' => null]);

        $this->board->load('members');
    }

    public function with(): array
    {
        $this->board->loadMissing('members');

        $tasks = $this->board->tasks()
            ->with('assignee')
            ->when($this->filterAssignee === 'mine', fn ($q) => $q->where('assignee_id', auth()->id()))
            ->when(is_numeric($this->filterAssignee), fn ($q) => $q->where('assignee_id', (int) $this->filterAssignee))
            ->when($this->filterPriority !== 'all', fn ($q) => $q->where('priority', $this->filterPriority))
            ->orderBy('position')
            ->get()
            ->groupBy(fn ($t) => $t->status->value);

        return [
            'columns' => TaskStatus::cases(),
            'tasksByStatus' => $tasks,
            'team' => $this->board->team(),
            'isOwner' => $this->board->user_id === auth()->id(),
            'filterActive' => $this->filterAssignee !== 'all' || $this->filterPriority !== 'all',
        ];
    }
};
?>

<div class="flex h-[calc(100vh-3rem)] flex-col"
    x-data="{ open: false, addOpen: false, membersOpen: false, settingsOpen: false }"
    @open-task.window="open = true"
    @close-task.window="open = false"
    @task-created.window="addOpen = false"
    @board-saved.window="settingsOpen = false">
    {{-- Board header --}}
    <div class="border-b border-neutral-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-3 gap-y-2 px-6 py-2.5">
        <a href="{{ route('boards.index') }}" class="text-neutral-400 hover:text-neutral-700">Boards</a>
        <span class="text-neutral-300">/</span>
        <span class="font-mono text-xs text-neutral-400">{{ $board->code }}</span>
        @if ($isOwner)
            <button @click="settingsOpen = true" class="group/edit flex items-center gap-1.5">
                <span class="font-semibold text-neutral-900 group-hover/edit:text-neutral-600">{{ $board->name }}</span>
                <svg class="h-3.5 w-3.5 text-neutral-300 group-hover/edit:text-neutral-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M4 13.5V16h2.5l7-7L11 6.5l-7 7zM12.5 5l2.5 2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        @else
            <h1 class="font-semibold text-neutral-900">{{ $board->name }}</h1>
        @endif
        @if ($board->description)
            <span class="hidden text-neutral-400 md:inline">{{ $board->description }}</span>
        @endif

        <div class="ml-auto flex items-center gap-2">
            {{-- Team avatars --}}
            <button @click="membersOpen = true" class="flex -space-x-1.5" title="Kelola anggota">
                @foreach ($team->take(4) as $member)
                    <span class="grid h-6 w-6 place-items-center rounded-full border border-white bg-neutral-200 text-[10px] font-medium text-neutral-600">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </span>
                @endforeach
                @if ($team->count() > 4)
                    <span class="grid h-6 w-6 place-items-center rounded-full border border-white bg-neutral-100 text-[10px] text-neutral-500">+{{ $team->count() - 4 }}</span>
                @endif
            </button>

            <button @click="membersOpen = true"
                class="rounded-md border border-neutral-200 px-3 py-1.5 font-medium text-neutral-600 hover:bg-neutral-100">
                Anggota
            </button>
            <button @click="addOpen = true"
                class="rounded-md bg-neutral-900 px-3 py-1.5 font-medium text-white hover:bg-neutral-700">
                Task baru
            </button>
        </div>
    </div>
    </div>

    {{-- Filter bar --}}
    <div class="border-b border-neutral-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-2 px-6 py-2 text-neutral-600">
            <button wire:click="$set('filterAssignee', @js($filterAssignee === 'mine' ? 'all' : 'mine'))"
                @class([
                    'rounded-md border px-2.5 py-1 font-medium',
                    'border-neutral-900 bg-neutral-900 text-white' => $filterAssignee === 'mine',
                    'border-neutral-200 hover:bg-neutral-100' => $filterAssignee !== 'mine',
                ])>Task saya</button>

            <select wire:model.live="filterAssignee"
                class="rounded-md border border-neutral-200 bg-white px-2 py-1 focus:border-neutral-400 focus:outline-none">
                <option value="all">Semua assignee</option>
                <option value="mine">Task saya</option>
                @foreach ($team as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterPriority"
                class="rounded-md border border-neutral-200 bg-white px-2 py-1 focus:border-neutral-400 focus:outline-none">
                <option value="all">Semua prioritas</option>
                @foreach (TaskPriority::cases() as $p)
                    <option value="{{ $p->value }}">{{ $p->label() }}</option>
                @endforeach
            </select>

            @if ($filterActive)
                <button wire:click="$set('filterAssignee', 'all'); $set('filterPriority', 'all')"
                    class="text-neutral-400 hover:text-neutral-700">Reset</button>
            @endif
        </div>
    </div>

    {{-- Columns --}}
    <div class="mx-auto flex w-full max-w-7xl flex-1 gap-4 overflow-x-auto px-6 py-4">
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
                        onAdd: (e) => $wire.reorderColumn(e.to.dataset.status, [...e.to.children].map(c => c.dataset.id).filter(Boolean)),
                        onUpdate: (e) => $wire.reorderColumn(e.to.dataset.status, [...e.to.children].map(c => c.dataset.id).filter(Boolean)),
                    })"
                    x-ref="list"
                    data-status="{{ $column->value }}"
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
                            wire:click="openTask({{ $task->id }})"
                            class="group cursor-pointer rounded-md border border-neutral-200 bg-white p-2.5 hover:border-neutral-300 active:cursor-grabbing">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-[11px] text-neutral-400">{{ $task->code }}</span>
                                <button wire:click.stop="deleteTask({{ $task->id }})"
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

    {{-- Board settings modal --}}
    <div x-cloak x-show="settingsOpen" @keydown.escape.window="settingsOpen = false"
        class="fixed inset-0 z-30 flex items-start justify-center p-4 pt-24">
        <div class="fixed inset-0 bg-neutral-900/30" @click="settingsOpen = false"></div>

        <div x-show="settingsOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="relative w-full max-w-md rounded-lg border border-neutral-200 bg-white p-5 shadow-lg">
            <h2 class="mb-4 font-semibold text-neutral-900">Pengaturan board</h2>

            <form wire:submit="saveBoard" class="space-y-3">
                <div>
                    <label class="mb-1 block text-neutral-500">Nama</label>
                    <input type="text" wire:model="boardName"
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-neutral-800 focus:border-neutral-400 focus:outline-none">
                    @error('boardName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-neutral-500">Deskripsi</label>
                    <input type="text" wire:model="boardDescription"
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-neutral-800 focus:border-neutral-400 focus:outline-none">
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="settingsOpen = false"
                        class="rounded-md border border-neutral-200 px-3 py-1.5 font-medium text-neutral-600 hover:bg-neutral-100">Batal</button>
                    <button type="submit"
                        class="rounded-md bg-neutral-900 px-3 py-1.5 font-medium text-white hover:bg-neutral-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Members modal --}}
    <div x-cloak x-show="membersOpen" @keydown.escape.window="membersOpen = false"
        class="fixed inset-0 z-30 flex items-start justify-center p-4 pt-24">
        <div class="fixed inset-0 bg-neutral-900/30" @click="membersOpen = false"></div>

        <div x-show="membersOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="relative w-full max-w-md rounded-lg border border-neutral-200 bg-white p-5 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold text-neutral-900">Anggota board</h2>
                <button @click="membersOpen = false" class="text-neutral-400 hover:text-neutral-700">&times;</button>
            </div>

            <ul class="mb-4 divide-y divide-neutral-100">
                @foreach ($team as $member)
                    <li class="flex items-center gap-2 py-2">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-neutral-200 text-[10px] font-medium text-neutral-600">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </span>
                        <span class="text-neutral-800">{{ $member->name }}</span>
                        <span class="text-neutral-400">{{ $member->email }}</span>
                        @if ($member->id === $board->user_id)
                            <span class="ml-auto rounded bg-neutral-100 px-1.5 py-0.5 text-[11px] text-neutral-500">Owner</span>
                        @elseif ($isOwner)
                            <button wire:click="removeMember({{ $member->id }})"
                                class="ml-auto text-neutral-300 hover:text-red-600">&times;</button>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($isOwner)
                <form wire:submit="invite" class="flex items-start gap-2">
                    <div class="flex-1">
                        <input type="email" wire:model="inviteEmail" placeholder="email@contoh.com"
                            class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 placeholder:text-neutral-400 focus:border-neutral-400 focus:outline-none">
                        @error('inviteEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit"
                        class="rounded-md bg-neutral-900 px-3 py-2 font-medium text-white hover:bg-neutral-700">Undang</button>
                </form>
                <p class="mt-2 text-xs text-neutral-400">User harus sudah punya akun untuk diundang.</p>
            @else
                <p class="text-xs text-neutral-400">Hanya owner yang bisa mengelola anggota.</p>
            @endif
        </div>
    </div>

    {{-- Add task modal --}}
    <div x-cloak x-show="addOpen" @keydown.escape.window="addOpen = false"
        class="fixed inset-0 z-30 flex items-start justify-center p-4 pt-24">
        <div class="fixed inset-0 bg-neutral-900/30" @click="addOpen = false"></div>

        <div x-show="addOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="relative w-full max-w-md rounded-lg border border-neutral-200 bg-white p-5 shadow-lg">
            <h2 class="mb-4 font-semibold text-neutral-900">Task baru</h2>

            <form wire:submit="addTask" class="space-y-3">
                <div>
                    <label class="mb-1 block text-neutral-500">Judul</label>
                    <input type="text" wire:model="title" placeholder="mis. Perbaiki bug login"
                        x-ref="addTitle" x-effect="if (addOpen) $nextTick(() => $refs.addTitle.focus())"
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-neutral-800 placeholder:text-neutral-400 focus:border-neutral-400 focus:outline-none">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-neutral-500">Prioritas</label>
                    <select wire:model="priority"
                        class="w-full rounded-md border border-neutral-200 bg-white px-2 py-2 text-neutral-700 focus:border-neutral-400 focus:outline-none">
                        @foreach (TaskPriority::cases() as $p)
                            <option value="{{ $p->value }}">{{ $p->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="addOpen = false"
                        class="rounded-md border border-neutral-200 px-3 py-1.5 font-medium text-neutral-600 hover:bg-neutral-100">Batal</button>
                    <button type="submit"
                        class="rounded-md bg-neutral-900 px-3 py-1.5 font-medium text-white hover:bg-neutral-700">Tambah task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Task detail modal --}}
    <div x-cloak x-show="open" @keydown.escape.window="open = false"
        class="fixed inset-0 z-30 flex items-start justify-center p-4 pt-24">
        <div class="fixed inset-0 bg-neutral-900/30" @click="open = false"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="relative w-full max-w-lg rounded-lg border border-neutral-200 bg-white shadow-lg">
            @if ($editingId)
                <div class="flex items-center justify-between border-b border-neutral-200 px-5 py-3">
                    <span class="font-mono text-xs text-neutral-400">{{ $editCode }}</span>
                    <button @click="open = false" class="text-neutral-400 hover:text-neutral-700">&times;</button>
                </div>

                <div class="space-y-4 px-5 py-4">
                    <div>
                        <label class="mb-1 block text-neutral-500">Judul</label>
                        <input type="text" wire:model="editTitle"
                            class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-neutral-800 focus:border-neutral-400 focus:outline-none">
                        @error('editTitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-neutral-500">Status</label>
                            <select wire:model="editStatus"
                                class="w-full rounded-md border border-neutral-200 bg-white px-2 py-2 text-neutral-700 focus:border-neutral-400 focus:outline-none">
                                @foreach ($columns as $c)
                                    <option value="{{ $c->value }}">{{ $c->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-neutral-500">Prioritas</label>
                            <select wire:model="editPriority"
                                class="w-full rounded-md border border-neutral-200 bg-white px-2 py-2 text-neutral-700 focus:border-neutral-400 focus:outline-none">
                                @foreach (TaskPriority::cases() as $p)
                                    <option value="{{ $p->value }}">{{ $p->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-neutral-500">Due date</label>
                            <input type="date" wire:model="editDueDate"
                                class="w-full rounded-md border border-neutral-200 bg-white px-2 py-2 text-neutral-700 focus:border-neutral-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-neutral-500">Assignee</label>
                            <select wire:model="editAssignee"
                                class="w-full rounded-md border border-neutral-200 bg-white px-2 py-2 text-neutral-700 focus:border-neutral-400 focus:outline-none">
                                <option value="">— Tidak ada —</option>
                                @foreach ($team as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-neutral-200 px-5 py-3">
                    <button wire:click="deleteTask({{ $editingId }})" wire:confirm="Hapus task ini?"
                        class="text-red-600 hover:text-red-700">Hapus</button>
                    <div class="flex gap-2">
                        <button @click="open = false"
                            class="rounded-md border border-neutral-200 px-3 py-1.5 font-medium text-neutral-600 hover:bg-neutral-100">Batal</button>
                        <button wire:click="saveTask"
                            class="rounded-md bg-neutral-900 px-3 py-1.5 font-medium text-white hover:bg-neutral-700">Simpan</button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
