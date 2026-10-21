@extends('layouts.storefront')
@section('title', 'Reset password | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-16 sm:px-6"><div class="max-w-md">
    <h1 class="text-3xl font-black">Reset your password</h1>
    <p class="mt-2 text-slate-500">Enter your email and we will send a 4-digit reset code.</p>
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('account.password.email') }}" class="mt-8 space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf
        <label class="block text-sm font-semibold">Email address<input name="email" type="email" required autocomplete="email" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Send reset code</button>
    </form>
</div></section>
@endsection
