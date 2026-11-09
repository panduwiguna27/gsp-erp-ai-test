<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in &middot; {{ config('app.name', 'ERP') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6 text-slate-100">
            <div class="text-3xl">📦</div>
            <h1 class="text-xl font-semibold mt-2">{{ config('app.name', 'ERP') }}</h1>
            <p class="text-sm text-slate-400">Sign in to continue</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="bg-white rounded-xl shadow-lg p-6 space-y-4">
            @csrf
            <div>
                <label for="login" class="block text-sm font-medium text-slate-700">Username or email</label>
                <input id="login" name="login" type="text" required autofocus autocomplete="username" value="{{ old('login', 'admin') }}"
                       class="mt-1 w-full rounded-md border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 w-full rounded-md border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300"> Remember me
            </label>
            <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Sign in
            </button>
            <p class="text-xs text-center text-slate-400">Demo: admin / admin123</p>
        </form>
    </div>
</body>
</html>
