<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Task Management' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-600 via-indigo-600 to-violet-700 font-sans text-slate-900 antialiased">
    <header class="sticky top-0 z-20 border-b border-white/10 bg-white/10 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <a href="/" class="flex items-center gap-2 text-lg font-bold text-white">
                <span class="grid h-7 w-7 place-items-center rounded bg-white/20">🗂️</span>
                TaskFlow
            </a>
            <div class="flex items-center gap-3 text-sm text-white/80">
                <span class="hidden rounded-full bg-white/15 px-3 py-1 text-xs font-medium sm:inline">⚡ MCP Ready</span>
                @auth
                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-md bg-white/15 px-3 py-1 text-xs font-medium text-white hover:bg-white/25">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md bg-white/15 px-3 py-1 text-xs font-medium text-white hover:bg-white/25">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
