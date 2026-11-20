@extends('layouts.entrepreneur')

@section('title', 'Customer Orders')
@section('header_title', 'Customer Orders')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
    <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">{{ $customer->name }}</p>
    <h1 class="mt-2 text-3xl font-black">Order history</h1>
    <div class="mt-8 grid gap-4">
        @foreach($orders as $order)
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-wrap justify-between gap-3"><div><h2 class="font-bold"><a class="text-[var(--brand-primary)] hover:underline" href="{{ route('entrepreneur.orders.show', $order) }}">{{ $order->order_number }}</a></h2><p class="text-sm capitalize text-slate-500">{{ str_replace('_', ' ', $order->status) }}</p></div><strong>₹{{ number_format((float) $order->grand_total, 2) }}</strong></div>
                <div class="mt-4 space-y-2 text-sm">@foreach($order->items as $item)<div class="flex justify-between"><span>{{ $item->product_name }} × {{ $item->quantity }}</span><span>Profit: ₹{{ number_format((float) $item->profit, 2) }}</span></div>@endforeach</div>
            </article>
        @endforeach
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</main>
@endsection
