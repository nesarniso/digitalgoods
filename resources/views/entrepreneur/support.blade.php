@extends('layouts.entrepreneur')

@section('title', 'Training & Support')
@section('header_title', 'Training & Support')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
    <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Entrepreneur resources</p><h1 class="mt-2 text-3xl font-black">Training & Support</h1><p class="mt-2 max-w-2xl text-slate-600">Learn the selling workflow and contact eflix support whenever you need help.</p></div>
    <section class="mt-8 grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-xs font-bold text-[var(--brand-primary)]">01</p><h2 class="mt-2 font-bold">Choose products</h2><p class="mt-2 text-sm text-slate-600">Add published products from Marketplace to My Products before sharing links.</p></article>
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-xs font-bold text-[var(--brand-primary)]">02</p><h2 class="mt-2 font-bold">Share tracking links</h2><p class="mt-2 text-sm text-slate-600">Use product links for a specific item or your general referral link for the full store.</p></article>
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-xs font-bold text-[var(--brand-primary)]">03</p><h2 class="mt-2 font-bold">Follow every sale</h2><p class="mt-2 text-sm text-slate-600">Track customer orders, delivery status, profit release and withdrawals from your workspace.</p></article>
    </section>
    <section class="mt-8 grid gap-6 lg:grid-cols-[1fr_1fr]">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-bold">Contact support</h2>
            @if(session('success'))<div class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('entrepreneur.support.store') }}" class="mt-5 space-y-3">
                @csrf
                <input name="subject" required maxlength="150" placeholder="Subject" class="w-full rounded-xl border border-slate-300 p-3">
                <select name="category" required class="w-full rounded-xl border border-slate-300 p-3"><option value="">Select category</option><option>Order support</option><option>Payment</option><option>Returns and refunds</option><option>Product question</option><option>General inquiry</option></select>
                <textarea name="message" required minlength="10" maxlength="5000" rows="5" placeholder="Describe your question or issue" class="w-full rounded-xl border border-slate-300 p-3"></textarea>
                <button class="rounded-xl bg-[var(--brand-primary)] px-5 py-3 font-bold text-white">Submit support request</button>
            </form>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-bold">My support requests</h2>
            <div class="mt-4 space-y-3">
                @forelse($tickets as $ticket)
                    <div class="rounded-xl border border-slate-200 p-4"><div class="flex justify-between gap-3"><p class="font-semibold">{{ $ticket->subject }}</p><span class="rounded-full bg-slate-100 px-2 py-1 text-xs capitalize">{{ $ticket->status ?: 'open' }}</span></div><p class="mt-1 text-xs text-slate-500">{{ $ticket->category }}</p><p class="mt-2 text-sm text-slate-600">{{ Str::limit($ticket->message, 120) }}</p></div>
                @empty
                    <p class="text-sm text-slate-500">No support requests yet.</p>
                @endforelse
            </div>
        </div>
    </section>
</main>
@endsection
