<?php

use App\Ai\Agents\PolicyAdvisor;
use App\Ai\Agents\TaskAssistant;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app')] class extends Component
{
    public string $mode = 'task';   // 'task' | 'policy'
    public string $message = '';
    public array $history = [];
    public bool $thinking = false;
    public ?string $conversationId = null;

    public function switchMode(string $mode): void
    {
        $this->mode = $mode;
        $this->history = [];
        $this->conversationId = null;
    }

    public function send(): void
    {
        $input = trim($this->message);

        if ($input === '') {
            return;
        }

        $this->history[] = ['role' => 'user', 'content' => $input];
        $this->message = '';
        $this->thinking = true;

        $agent = $this->mode === 'policy'
            ? new PolicyAdvisor
            : new TaskAssistant;

        $agent = $this->conversationId
            ? $agent->continue($this->conversationId, as: auth()->user())
            : $agent->forUser(auth()->user());

        $response = $agent->prompt($input);

        $this->conversationId = $response->conversationId;
        $this->history[] = ['role' => 'assistant', 'content' => (string) $response];
        $this->thinking = false;
    }

    public function with(): array
    {
        return [
            'modeLabel' => $this->mode === 'policy' ? 'Policy Advisor' : 'Task Assistant',
        ];
    }
}

?>

<div class="flex flex-col h-[calc(100vh-8rem)] max-w-3xl mx-auto">

    {{-- Mode toggle --}}
    <div class="flex gap-2 mb-4">
        <button
            wire:click="switchMode('task')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition
                {{ $mode === 'task' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border hover:bg-gray-50' }}"
        >
            🤖 Task Assistant
        </button>
        <button
            wire:click="switchMode('policy')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition
                {{ $mode === 'policy' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border hover:bg-gray-50' }}"
        >
            📋 Policy Advisor
        </button>

        <span class="ml-auto text-xs text-gray-400 self-center">
            {{ $mode === 'policy' ? 'Menjawab berdasarkan SOP tim' : 'Bisa buat, ubah, & lihat task' }}
        </span>
    </div>

    {{-- Chat messages --}}
    <div
        class="flex-1 overflow-y-auto space-y-4 p-4 bg-gray-50 rounded-xl border"
        x-data
        x-init="$watch('$wire.history', () => $el.scrollTo({ top: $el.scrollHeight, behavior: 'smooth' }))"
    >
        @forelse ($history as $msg)
            <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-2xl px-4 py-3 text-sm leading-relaxed
                    {{ $msg['role'] === 'user'
                        ? 'bg-indigo-600 text-white rounded-br-sm'
                        : 'bg-white text-gray-800 border rounded-bl-sm shadow-sm' }}">
                    {!! nl2br(e($msg['content'])) !!}
                </div>
            </div>
        @empty
            <div class="flex items-center justify-center h-full text-gray-400 text-sm">
                @if ($mode === 'task')
                    <div class="text-center space-y-2">
                        <p class="text-2xl">🤖</p>
                        <p class="font-medium">Task Assistant siap membantu!</p>
                        <p>Coba: <em>"Tampilkan semua task yang belum selesai"</em></p>
                        <p>atau: <em>"Buatkan task baru untuk fitur login"</em></p>
                    </div>
                @else
                    <div class="text-center space-y-2">
                        <p class="text-2xl">📋</p>
                        <p class="font-medium">Policy Advisor siap menjawab!</p>
                        <p>Coba: <em>"Berapa maksimum task yang boleh in_progress?"</em></p>
                        <p>atau: <em>"Apa syarat task bisa dipindah ke done?"</em></p>
                    </div>
                @endif
            </div>
        @endforelse

        @if ($thinking)
            <div class="flex justify-start">
                <div class="bg-white border rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm">
                    <div class="flex gap-1 items-center">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Input --}}
    <form wire:submit="send" class="flex gap-3 mt-4">
        <input
            wire:model="message"
            type="text"
            placeholder="{{ $mode === 'policy' ? 'Tanya tentang SOP atau kebijakan tim...' : 'Ketik perintah atau pertanyaan...' }}"
            class="flex-1 rounded-xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            :disabled="$wire.thinking"
            autofocus
        />
        <button
            type="submit"
            class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl px-5 py-3 text-sm font-medium transition"
            :disabled="$wire.thinking || $wire.message.trim() === ''"
        >
            Kirim
        </button>
    </form>
</div>
