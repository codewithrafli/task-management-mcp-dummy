<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Otorisasi Aplikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow">
        <h1 class="text-xl font-bold text-gray-900">Permintaan Otorisasi</h1>

        <p class="mt-3 text-sm text-gray-600">
            <strong>{{ $client->name }}</strong> ingin mengakses akun
            <strong>{{ $user->name }}</strong>.
        </p>

        @if (count($scopes) > 0)
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-700">Aplikasi ini akan dapat:</p>
                <ul class="mt-2 list-disc pl-5 text-sm text-gray-600">
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
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Izinkan
                </button>
            </form>

            <form method="post" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                @csrf
                @method('DELETE')
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit"
                    class="w-full rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300">
                    Tolak
                </button>
            </form>
        </div>
    </div>
</body>
</html>
