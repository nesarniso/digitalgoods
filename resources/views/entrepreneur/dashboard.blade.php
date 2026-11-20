@extends('layouts.entrepreneur')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--brand-primary)]">Overview</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-sm text-slate-500">Entrepreneur code: {{ $profile->entrepreneur_code }}</p>
        </div>
        <div class="rounded-2xl bg-emerald-50 px-5 py-3 text-emerald-800">
            <p class="text-xs font-bold uppercase tracking-wide">Available balance</p>
            <p class="mt-1 text-2xl font-black">₹{{ number_format((float) $balance, 2) }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Total sales</p><p class="mt-1 text-2xl font-black">{{ $salesCount }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Delivered sales</p><p class="mt-1 text-2xl font-black text-emerald-600">{{ $deliveredSalesCount }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">My customers</p><p class="mt-1 text-2xl font-black">{{ $customersCount }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Referral team</p><p class="mt-1 text-2xl font-black">{{ $teamCount }}</p></div>
    </div>

    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Activity</p><h2 class="mt-1 text-xl font-black">Recent customer orders</h2></div>
            <a href="{{ route('entrepreneur.sales') }}" class="text-sm font-bold text-[var(--brand-primary)] hover:underline">View all sales →</a>
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            @forelse($recentOrders as $order)
                <a href="{{ route('entrepreneur.orders.show', $order) }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-[var(--brand-primary)] hover:bg-slate-50">
                    <p class="truncate text-sm font-bold">{{ $order->user?->name ?: 'Customer' }}</p>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $order->order_number }}</p>
                    <p class="mt-3 text-sm font-black">₹{{ number_format((float) $order->grand_total, 2) }}</p>
                    <p class="mt-1 text-xs capitalize text-slate-500">{{ str_replace('_', ' ', $order->status) }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500 md:col-span-2 xl:col-span-5">Customer orders will appear here after your first sale.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-8 rounded-2xl bg-gradient-to-r from-[var(--brand-primary)] to-slate-900 p-6 text-white shadow-lg">
        <p class="text-xs font-bold uppercase tracking-widest text-white/70">Get started</p>
        <h2 class="mt-2 text-2xl font-black">Ready to grow your sales?</h2>
        <p class="mt-2 max-w-2xl text-sm text-white/80">Choose products from the marketplace or create a customer order. Your links, profit and withdrawals are available from the sidebar.</p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('entrepreneur.marketplace') }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-900">Browse marketplace</a>
            <a href="{{ route('entrepreneur.orders.create') }}" class="rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/30">Create customer order</a>
        </div>
    </section>

    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">General referral</p>
                <h2 class="mt-1 text-xl font-black">Share your general referral link</h2>
                <p class="mt-1 text-sm text-slate-500">Visitors can browse any product and attributed purchases will count toward your account.</p>
            </div>
        </div>
        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
            <input readonly value="{{ $referralLink }}" aria-label="General referral link" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm">
            <button type="button" data-copy-link="{{ $referralLink }}" data-copy-original="Copy referral link" class="rounded-xl bg-[var(--brand-primary)] px-4 py-3 text-sm font-bold text-white">Copy referral link</button>
        </div>
        <span data-copy-feedback aria-live="polite" class="mt-2 hidden text-xs font-semibold text-emerald-600">Link copied</span>
    </section>
</main>
@endsection
