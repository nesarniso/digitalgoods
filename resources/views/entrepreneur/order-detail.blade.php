@extends('layouts.entrepreneur')

@section('title', 'Order Details')
@section('header_title', 'Order Details')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Order breakdown</p><h1 class="mt-2 text-3xl font-black">{{ $order->order_number }}</h1></div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold capitalize">{{ str_replace('_', ' ', $order->status) }}</span>
    </div>
    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
            <h2 class="font-bold">Products and profit</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <div class="grid gap-2 py-4 sm:grid-cols-4 sm:items-center"><div class="sm:col-span-2"><p class="font-semibold">{{ $item->product_name }}</p><p class="text-sm text-slate-500">Qty {{ $item->quantity }} · ₹{{ number_format((float) $item->unit_price, 2) }} each</p></div><div><p class="text-xs text-slate-500">Cost</p><p>₹{{ number_format((float) ($item->cost_price * $item->quantity), 2) }}</p></div><div><p class="text-xs text-slate-500">Profit ({{ $item->profit_status }})</p><p class="font-bold text-emerald-600">₹{{ number_format((float) $item->profit, 2) }}</p></div></div>
                @endforeach
            </div>
        </section>
        <aside class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><h2 class="font-bold">Customer</h2><p class="mt-3">{{ $order->user?->name }}</p><p class="text-sm text-slate-500">{{ $order->user?->phone ?: $order->user?->email }}</p><hr class="my-5"><div class="flex justify-between font-bold"><span>Order total</span><span>₹{{ number_format((float) $order->grand_total, 2) }}</span></div></aside>
    </div>
</main>
@endsection
