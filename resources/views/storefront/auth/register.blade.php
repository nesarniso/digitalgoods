@extends('layouts.storefront')
@section('title', 'Create account | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-16 sm:px-6"><div class="max-w-md"><h1 class="text-3xl font-black">Create your account</h1><p class="mt-2 text-slate-500">Track orders and enjoy a faster checkout.</p>
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('account.register.store') }}" class="mt-8 space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf
        <label class="block text-sm font-semibold">Name<input name="name" required value="{{ old('name') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <label class="block text-sm font-semibold">Email<input name="email" type="email" required value="{{ old('email') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <label class="block text-sm font-semibold">Password<input name="password" type="password" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <label class="block text-sm font-semibold">Confirm password<input name="password_confirmation" type="password" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Create account</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-600">Already registered? <a href="{{ route('account.login') }}" class="font-bold text-indigo-700">Sign in</a></p></div>
</section>
@endsection
