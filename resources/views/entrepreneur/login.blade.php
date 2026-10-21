@extends('layouts.storefront')

@section('content')
<main class="mx-auto max-w-md px-4 py-12">
    <h1 class="text-3xl font-bold">Entrepreneur Login</h1>
    @if ($errors->any())
        <div class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('entrepreneur.login.store') }}" class="mt-8 space-y-4">
        @csrf
        <input name="identifier" required type="email" placeholder="Email address" class="w-full rounded border p-3">
        <input name="password" required type="password" placeholder="Password" class="w-full rounded border p-3">
        <button type="submit" class="group flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[var(--brand-primary)] px-5 py-3.5 text-center font-black text-white shadow-lg shadow-slate-300 transition duration-200 hover:-translate-y-0.5 hover:opacity-90 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-slate-200">
            <span>Sign in</span><span class="transition-transform group-hover:translate-x-1">→</span>
        </button>
        <p class="pt-2 text-center text-sm text-slate-500">
            Not an entrepreneur yet?
            <a href="{{ route('entrepreneur.register') }}" class="font-bold text-[var(--brand-primary)] hover:underline">Register now</a>
        </p>
    </form>
</main>
@endsection
