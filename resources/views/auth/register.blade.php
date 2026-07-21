<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar · TaskFlow</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-neutral-50 p-4 font-sans text-[13px] text-neutral-900 antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 flex items-center gap-2">
            <span class="grid h-6 w-6 place-items-center rounded bg-neutral-900 text-xs font-bold text-white">T</span>
            <span class="text-base font-semibold">TaskFlow</span>
        </div>

        <div class="rounded-lg border border-neutral-200 bg-white p-6">
            <h1 class="font-semibold text-neutral-900">Buat akun</h1>
            <p class="mt-0.5 text-neutral-500">Daftar untuk mulai memakai TaskFlow.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('register.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-neutral-500">Nama</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 focus:border-neutral-400 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-neutral-500">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 focus:border-neutral-400 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-neutral-500">Password</label>
                    <input type="password" name="password" required
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 focus:border-neutral-400 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-neutral-500">Konfirmasi password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-md border border-neutral-200 bg-white px-3 py-2 focus:border-neutral-400 focus:outline-none">
                </div>
                <button type="submit"
                    class="w-full rounded-md bg-neutral-900 px-4 py-2 font-medium text-white hover:bg-neutral-700">
                    Daftar
                </button>
            </form>
        </div>

        <p class="mt-3 text-center text-neutral-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-medium text-neutral-800 underline">Masuk</a>
        </p>
    </div>
</body>
</html>
