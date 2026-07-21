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

<div class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6 flex items-center gap-2 text-white">
        <h1 class="text-2xl font-bold drop-shadow-sm">Boards kamu</h1>
        <span class="rounded-full bg-white/15 px-2 py-0.5 text-xs">{{ $boards->count() }}</span>
    </div>

    {{-- Create board --}}
    <div class="mb-8 rounded-xl bg-white/95 p-5 shadow-lg backdrop-blur">
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Buat board baru</h2>
        <form wire:submit="create" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input type="text" wire:model="name" placeholder="Nama board (mis. Sprint Release)"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex-1">
                <input type="text" wire:model="description" placeholder="Deskripsi (opsional)"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <button type="submit"
                class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                + Buat
            </button>
        </form>
    </div>

    {{-- Board tiles --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($boards as $board)
            @php
                $gradients = ['from-sky-500 to-indigo-600', 'from-fuchsia-500 to-purple-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-blue-600'];
                $grad = $gradients[$board->id % count($gradients)];
            @endphp
            <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br {{ $grad }} p-4 shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
                <a href="{{ route('boards.show', $board) }}" class="block h-28">
                    <span class="inline-block rounded bg-white/25 px-2 py-0.5 font-mono text-xs text-white">{{ $board->code }}</span>
                    <h3 class="mt-2 text-lg font-bold text-white drop-shadow">{{ $board->name }}</h3>
                    <p class="mt-1 line-clamp-2 text-sm text-white/80">{{ $board->description ?: 'Tanpa deskripsi' }}</p>
                </a>
                <div class="flex items-center justify-between">
                    <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-medium text-white">{{ $board->tasks_count }} task</span>
                    <button wire:click="delete({{ $board->id }})"
                        wire:confirm="Hapus board ini beserta semua task-nya?"
                        class="text-white/60 opacity-0 transition hover:text-white group-hover:opacity-100">Hapus</button>
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-xl bg-white/90 p-8 text-center text-sm text-slate-400">
                Belum ada board. Buat board pertamamu di atas.
            </p>
        @endforelse
    </div>
</div>
