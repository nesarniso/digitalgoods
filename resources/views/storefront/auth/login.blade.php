@extends('layouts.storefront')
@section('title', 'Customer login | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-16 sm:px-6"><div class="max-w-md"><h1 class="text-3xl font-black">Welcome back</h1><p class="mt-2 text-slate-500">Sign in to view your orders and account.</p>
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('account.login.store') }}" class="mt-8 space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf
        <label class="block text-sm font-semibold">Email address<input name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <label class="block text-sm font-semibold">Password<input name="password" type="password" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <a href="{{ route('account.password.request') }}" class="text-sm font-semibold text-indigo-700">Forgot password?</a>
        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Sign in</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-600">New customer? <a href="{{ route('account.register') }}" class="font-bold text-indigo-700">Create an account</a></p></div>
</section>
@endsection
