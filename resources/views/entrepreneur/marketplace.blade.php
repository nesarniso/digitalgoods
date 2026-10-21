@extends('layouts.storefront')

@section('title', 'Marketplace | eflix Entrepreneur Platform')

@section('content')
<main class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    @if(session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Entrepreneur marketplace</p><h1 class="mt-2 text-3xl font-black">Choose products to sell</h1></div>
        <form><input name="q" value="{{ request('q') }}" placeholder="Search products" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm"></form>
    </div>
    <div class="mt-6 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800">Products can use their own profit rate; products without one use the global rate of <strong>{{ rtrim(rtrim(number_format($profitPercent, 2), '0'), '.') }}%</strong>.</div>
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($products as $product)
            <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="flex aspect-square items-center justify-center bg-slate-50">@if($product->images->first())<img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">@else<span class="text-5xl font-black text-orange-100">{{ Str::upper(Str::substr($product->name, 0, 1)) }}</span>@endif</div>
                <div class="p-4"><h2 class="line-clamp-2 min-h-12 font-bold">{{ $product->name }}</h2><p class="mt-2 text-sm text-slate-500">eflix price: ৳{{ number_format((float) $product->effective_price, 2) }}</p><p class="mt-1 text-xs text-slate-500">Profit: {{ rtrim(rtrim(number_format($product->effective_entrepreneur_profit_percent, 2), '0'), '.') }}% + ৳{{ number_format((float) $product->entrepreneur_profit_flat_rate, 2) }}</p><p class="mt-1 text-sm font-bold text-emerald-600">Estimated profit: ৳{{ number_format((float) $product->estimated_entrepreneur_profit, 2) }}</p><form method="POST" action="{{ route('entrepreneur.marketplace.store', $product) }}" class="mt-4">@csrf<button class="w-full rounded-xl bg-[var(--brand-primary)] px-4 py-2.5 text-xs font-bold text-white">{{ in_array($product->id, $selectedProductIds, true) ? 'Added to My Products' : 'Sell This Product' }}</button></form></div>
            </article>
        @empty
            <p class="col-span-full rounded-2xl bg-white p-10 text-center text-slate-500 ring-1 ring-slate-200">No published products available.</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
</main>
@endsection
