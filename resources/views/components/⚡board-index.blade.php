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

        $boards->create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        $this->reset('name', 'description');

        $this->dispatch('board-created');
    }

    public function delete(Board $board, BoardService $boards): void
    {
        $this->authorize('delete', $board);

        $boards->delete($board);
    }

    public function with(BoardService $boards): array
    {
        return ['boards' => $boards->forUser(auth()->user())];
    }
};
?>

<div class="mx-auto max-w-7xl px-6 py-8" x-data="{ open: false }" @board-created.window="open = false">
    <div class="mb-5 flex items-center justify-between">
        <div class="flex items-baseline gap-2">
            <h1 class="text-base font-semibold text-neutral-900">Boards</h1>
            <span class="text-neutral-400">{{ $boards->count() }}</span>
        </div>
        <button @click="open = true"
            class="rounded-md bg-neutral-900 px-3 py-1.5 font-medium text-white hover:bg-neutral-700">
            Board baru
        </button>
    </div>

    {{-- Board cards --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($boards as $board)
            <div wire:key="board-{{ $board->id }}" class="group relative rounded-lg border border-neutral-200 bg-white p-4 transition hover:border-neutral-300">
                <a href="{{ route('boards.show', $board) }}" class="block">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[11px] text-neutral-400">{{ $board->code }}</span>
                    </div>
                    <h3 class="mt-1 font-medium text-neutral-900">{{ $board->name }}</h3>
                    <p class="mt-0.5 line-clamp-2 min-h-[2.5rem] text-neutral-400">{{ $board->description ?: 'Tanpa deskripsi' }}</p>
                    <span class="mt-2 inline-block text-neutral-400">{{ $board->tasks_count }} task</span>
                </a>
                <button wire:click="delete({{ $board->id }})"
                    wire:confirm="Hapus board ini beserta semua task-nya?"
                    class="absolute right-3 top-3 text-neutral-300 opacity-0 transition hover:text-neutral-700 group-hover:opacity-100">&times;</button>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-dashed border-neutral-300 px-4 py-12 text-center text-neutral-400">
                Belum ada board.
                <button @click="open = true" class="font-medium text-neutral-700 underline">Buat board pertamamu</button>.
            </div>
        @endforelse
    </div>

    {{-- Create board modal --}}
    <div x-cloak x-show="open" @keydown.escape.window="open = false"
        class="fixed inset-0 z-30 flex items-start justify-center p-4 pt-24">
        <div class="fixed inset-0 bg-neutral-900/30" @click="open = false"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="relative w-full max-w-md rounded-lg border border-neutral-200 bg-white p-5 shadow-lg">
            <h2 class="mb-4 font-semibold text-neutral-900">Board baru</h2>

            <form wire:submit="create" class="space-y-3">
                <div>
                    <label class="mb-1 block text-neutral-500">Nama</label>
                    <input type="text" wire:model="name" placeholder="mis. Sprint Release" x-ref="nameInput"
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 placeholder:text-neutral-400 focus:border-neutral-400 focus:outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-neutral-500">Deskripsi <span class="text-neutral-300">(opsional)</span></label>
                    <input type="text" wire:model="description" placeholder="Deskripsi singkat"
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 placeholder:text-neutral-400 focus:border-neutral-400 focus:outline-none">
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="open = false"
                        class="rounded-md border border-neutral-200 px-3 py-1.5 font-medium text-neutral-600 hover:bg-neutral-100">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-md bg-neutral-900 px-3 py-1.5 font-medium text-white hover:bg-neutral-700">
                        Buat board
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
