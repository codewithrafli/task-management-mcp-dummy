<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Task Management' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen font-sans text-gray-900 antialiased">
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
            <a href="/" class="text-lg font-semibold">🗂️ Task Management</a>
            <span class="text-xs text-gray-400">MCP Ready</span>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
