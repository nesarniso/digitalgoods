@extends('layouts.entrepreneur')

@section('title', 'Sales Reports')
@section('header_title', 'Sales Reports')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
    <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Performance analytics</p><h1 class="mt-2 text-3xl font-black">Sales Reports</h1><p class="mt-2 text-slate-600">Understand your orders, revenue and product performance.</p></div>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Total orders</p><p class="mt-1 text-2xl font-black">{{ $totalOrders }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Delivered orders</p><p class="mt-1 text-2xl font-black text-emerald-600">{{ $deliveredOrders }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Sales revenue</p><p class="mt-1 text-2xl font-black">₹{{ number_format((float) $totalRevenue, 2) }}</p></div>
        <div class="rounded-2xl bg-emerald-50 p-5 text-emerald-800"><p class="text-sm">Tracked profit</p><p class="mt-1 text-2xl font-black">₹{{ number_format((float) $profit, 2) }}</p></div>
    </div>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><h2 class="text-xl font-bold">Order status breakdown</h2><div class="mt-4 space-y-3">@forelse($statusCounts as $status => $count)<div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3"><span class="capitalize">{{ str_replace('_', ' ', $status) }}</span><strong>{{ $count }}</strong></div>@empty<p class="text-sm text-slate-500">No sales data yet.</p>@endforelse</div></section>
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><h2 class="text-xl font-bold">Revenue realization</h2><div class="mt-6 flex items-end gap-3"><div class="h-40 flex-1 rounded-t-xl bg-slate-200" style="height: {{ $totalRevenue > 0 ? max(12, min(160, ((float) $totalRevenue / max(1, (float) $totalRevenue)) * 160)) : 12 }}px"></div><div class="h-40 flex-1 rounded-t-xl bg-emerald-500" style="height: {{ $totalRevenue > 0 ? max(12, min(160, ((float) $deliveredRevenue / (float) $totalRevenue) * 160)) : 12 }}px"></div></div><div class="mt-3 flex justify-between text-sm text-slate-500"><span>All revenue: ₹{{ number_format((float) $totalRevenue, 2) }}</span><span>Delivered: ₹{{ number_format((float) $deliveredRevenue, 2) }}</span></div></section>
    </div>
    <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold">Top products</h2></div><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Product</th><th class="px-5 py-3">Units</th><th class="px-5 py-3">Revenue</th><th class="px-5 py-3">Profit</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($topProducts as $product)<tr><td class="px-5 py-4 font-semibold">{{ $product->product_name }}</td><td class="px-5 py-4">{{ $product->units }}</td><td class="px-5 py-4">₹{{ number_format((float) $product->revenue, 2) }}</td><td class="px-5 py-4 font-bold text-emerald-600">₹{{ number_format((float) $product->profit, 2) }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No product performance data yet.</td></tr>@endforelse</tbody></table></section>
</main>
@endsection
