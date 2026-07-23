<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Otorisasi Aplikasi · TaskFlow</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-neutral-50 p-4 font-sans text-[13px] text-neutral-900 antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 flex items-center gap-2">
            <span class="grid h-6 w-6 place-items-center rounded bg-neutral-900 text-xs font-bold text-white">T</span>
            <span class="text-base font-semibold">TaskFlow</span>
        </div>

        <div class="rounded-lg border border-neutral-200 bg-white p-6">
            <h1 class="font-semibold text-neutral-900">Permintaan otorisasi</h1>
            <p class="mt-0.5 text-neutral-500">
                <strong class="font-medium text-neutral-800">{{ $client->name }}</strong> ingin mengakses akun
                <strong class="font-medium text-neutral-800">{{ $user->name }}</strong>.
            </p>

            @if (count($scopes) > 0)
                <div class="mt-5">
                    <p class="text-neutral-500">Aplikasi ini akan dapat:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-neutral-600">
                        @foreach ($scopes as $scope)
                            <li>{{ $scope->description }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 flex gap-3">
                <form method="post" action="{{ route('passport.authorizations.approve') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit"
                        class="w-full rounded-md bg-neutral-900 px-4 py-2 font-medium text-white hover:bg-neutral-700">
                        Izinkan
                    </button>
                </form>

                <form method="post" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit"
                        class="w-full rounded-md border border-neutral-200 bg-white px-4 py-2 font-medium text-neutral-800 hover:bg-neutral-50">
                        Tolak
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
