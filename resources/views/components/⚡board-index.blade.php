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

<div class="space-y-8">
    <div class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-base font-semibold">Buat Board Baru</h2>
        <form wire:submit="create" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input type="text" wire:model="name" placeholder="Nama board"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex-1">
                <input type="text" wire:model="description" placeholder="Deskripsi (opsional)"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <button type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Tambah
            </button>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($boards as $board)
            <div class="rounded-lg bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-start justify-between">
                    <a href="{{ route('boards.show', $board) }}" class="block">
                        <h3 class="font-semibold">{{ $board->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $board->description ?: 'Tanpa deskripsi' }}</p>
                    </a>
                    <button wire:click="delete({{ $board->id }})"
                        wire:confirm="Hapus board ini beserta semua task-nya?"
                        class="text-gray-300 hover:text-red-500">&times;</button>
                </div>
                <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                    <span>{{ $board->tasks_count }} task</span>
                    <a href="{{ route('boards.show', $board) }}" class="text-indigo-600 hover:underline">Buka &rarr;</a>
                </div>
            </div>
        @empty
            <p class="col-span-full text-sm text-gray-400">Belum ada board. Buat board pertamamu di atas.</p>
        @endforelse
    </div>
</div>
