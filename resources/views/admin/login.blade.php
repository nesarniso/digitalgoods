<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login | {{ config('app.name') }}</title>
    @if(file_exists(public_path('build/manifest.json'))) @vite(['resources/css/app.css', 'resources/js/app.js']) @endif
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-950 px-6 py-12 text-slate-900">
    <main class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
        <a href="{{ route('home') }}" class="text-sm font-semibold text-indigo-700">← Back to storefront</a>
        <h1 class="mt-8 text-3xl font-black">Admin sign in</h1>
        <p class="mt-2 text-sm text-slate-500">Sign in to manage your catalog and orders.</p>
        @if ($errors->any())
            <div class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
            @csrf
            <label class="block text-sm font-semibold">Email
                <input name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal outline-none focus:border-indigo-500">
            </label>
            <label class="block text-sm font-semibold">Password
                <input name="password" type="password" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal outline-none focus:border-indigo-500">
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-600"><input name="remember" type="checkbox" value="1"> Remember me</label>
            <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white hover:bg-indigo-700">Sign in</button>
        </form>
    </main>
</body>
</html>
