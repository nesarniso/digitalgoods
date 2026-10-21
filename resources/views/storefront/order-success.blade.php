@extends('layouts.storefront')
@section('title', 'Order confirmed | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-24 text-center sm:px-6"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl text-emerald-700">✓</div><h1 class="mt-6 text-4xl font-black">Order confirmed</h1><p data-order-number class="mt-4 text-slate-600"></p><p class="mx-auto mt-4 max-w-xl rounded-xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-800">Your order is now in your customer account. You will be redirected to your dashboard shortly.</p><a href="{{ route('account.index') }}" class="mt-8 inline-flex rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white">Go to dashboard</a></section>
@endsection
