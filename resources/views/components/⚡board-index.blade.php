<?php

use App\Models\Board;
use App\Services\BoardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('components.layouts.app')] class extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    public function create(BoardService $boards): void
    {
        $data = $this->validate();

        $boards->create($data);

        $this->reset('name', 'description');
    }

    public function delete(Board $board, BoardService $boards): void
    {
        $boards->delete($board);
    }

    public function with(BoardService $boards): array
    {
        return ['boards' => $boards->all()];
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-8">
    <div class="mb-5 flex items-baseline gap-2">
        <h1 class="text-base font-semibold text-neutral-900">Boards</h1>
        <span class="text-neutral-400">{{ $boards->count() }}</span>
    </div>

    {{-- Create board --}}
    <form wire:submit="create" class="mb-6 flex flex-col gap-2 sm:flex-row">
        <div class="flex-1">
            <input type="text" wire:model="name" placeholder="Nama board (mis. Sprint Release)"
                class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 placeholder:text-neutral-400 focus:border-neutral-400 focus:outline-none">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex-1">
            <input type="text" wire:model="description" placeholder="Deskripsi (opsional)"
                class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 placeholder:text-neutral-400 focus:border-neutral-400 focus:outline-none">
        </div>
        <button type="submit"
            class="rounded-md bg-neutral-900 px-4 py-2 font-medium text-white hover:bg-neutral-700">
            Buat board
        </button>
    </form>

    {{-- Board list --}}
    <div class="divide-y divide-neutral-200 overflow-hidden rounded-lg border border-neutral-200 bg-white">
        @forelse ($boards as $board)
            <div class="group flex items-center gap-3 px-4 py-3 hover:bg-neutral-50">
                <span class="font-mono text-xs text-neutral-400">{{ $board->code }}</span>
                <a href="{{ route('boards.show', $board) }}" class="min-w-0 flex-1">
                    <span class="font-medium text-neutral-900">{{ $board->name }}</span>
                    @if ($board->description)
                        <span class="ml-2 text-neutral-400">{{ $board->description }}</span>
                    @endif
                </a>
                <span class="text-neutral-400">{{ $board->tasks_count }} task</span>
                <button wire:click="delete({{ $board->id }})"
                    wire:confirm="Hapus board ini beserta semua task-nya?"
                    class="text-neutral-300 opacity-0 transition hover:text-neutral-700 group-hover:opacity-100">&times;</button>
            </div>
        @empty
            <p class="px-4 py-10 text-center text-neutral-400">Belum ada board. Buat board pertamamu di atas.</p>
        @endforelse
    </div>
</div>
