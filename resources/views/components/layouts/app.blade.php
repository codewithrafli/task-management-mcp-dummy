<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-neutral-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'TaskFlow' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-neutral-50 font-sans text-[13px] text-neutral-900 antialiased">
    <header class="sticky top-0 z-20 border-b border-neutral-200 bg-white">
        <div class="mx-auto flex h-12 max-w-7xl items-center justify-between px-6">
            <a href="/" class="flex items-center gap-2 font-semibold tracking-tight">
                <span class="grid h-5 w-5 place-items-center rounded bg-neutral-900 text-[11px] font-bold text-white">T</span>
                TaskFlow
            </a>
            <div class="flex items-center gap-3 text-neutral-500">
                @auth
                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-md border border-neutral-200 px-2.5 py-1 font-medium text-neutral-600 hover:bg-neutral-100">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md border border-neutral-200 px-2.5 py-1 font-medium text-neutral-600 hover:bg-neutral-100">Masuk</a>
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
