@extends('layouts.storefront')
@section('title', 'Set new password | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-16 sm:px-6"><div class="max-w-md">
    <h1 class="text-3xl font-black">Set a new password</h1>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('account.password.update') }}" class="mt-8 space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf
        <label class="block text-sm font-semibold">Email address<input name="email" type="email" required value="{{ old('email', session('email')) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <label class="block text-sm font-semibold">4-digit code<input name="code" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <label class="block text-sm font-semibold">New password<input name="password" type="password" minlength="4" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <label class="block text-sm font-semibold">Confirm new password<input name="password_confirmation" type="password" minlength="4" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Reset password</button>
    </form>
</div></section>
@endsection
