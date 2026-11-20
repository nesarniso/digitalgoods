@extends('layouts.entrepreneur')
@section('title', 'My Sales & Orders')
@section('header_title', 'My Sales & Orders')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
    <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Sales workspace</p>
    <h1 class="mt-2 text-3xl font-black">My Sales & Orders</h1>
    <p class="mt-2 text-slate-600">Track every order attributed to your entrepreneur account.</p>
    <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Order</th><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Date</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Total</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($sales as $sale)<tr><td class="px-5 py-4"><a href="{{ route('entrepreneur.orders.show', $sale) }}" class="font-bold text-indigo-700">{{ $sale->order_number }}</a></td><td class="px-5 py-4">{{ $sale->user?->name ?: 'Guest customer' }}</td><td class="px-5 py-4 text-slate-500">{{ $sale->created_at?->format('d M Y') }}</td><td class="px-5 py-4 capitalize">{{ str_replace('_', ' ', $sale->status) }}</td><td class="px-5 py-4 text-right font-bold">₹{{ number_format((float) $sale->grand_total, 2) }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No attributed sales yet.</td></tr>@endforelse
        </tbody></table>
    </section>
    <div class="mt-5">{{ $sales->links() }}</div>
</main>
@endsection
