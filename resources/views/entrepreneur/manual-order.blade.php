@extends('layouts.storefront')

@section('title', 'Create Customer Order | eflix Entrepreneur Platform')

@section('content')
<main class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
    <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Manual selling</p>
    <h1 class="mt-2 text-3xl font-black">Create Customer Order</h1>
    @if($errors->any())
        <div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    @if($products->isEmpty())
        <div class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">No products added yet. Add products from the Marketplace first.</div>
    @endif
    <form method="POST" action="{{ route('entrepreneur.orders.store') }}" class="mt-8 space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        <input name="customer_name" required placeholder="Customer name" class="w-full rounded-xl border border-slate-300 p-3">
        <input name="phone" required placeholder="Customer mobile" class="w-full rounded-xl border border-slate-300 p-3">
        <textarea name="address" required placeholder="Delivery address" class="w-full rounded-xl border border-slate-300 p-3"></textarea>
        <select name="product_id" required @disabled($products->isEmpty()) class="w-full rounded-xl border border-slate-300 p-3"><option value="">Select your product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} — ৳{{ number_format((float) ($product->sale_price ?: $product->regular_price), 2) }}</option>@endforeach</select>
        <div class="grid gap-4 sm:grid-cols-2"><input name="quantity" type="number" min="1" value="1" required placeholder="Quantity" class="w-full rounded-xl border border-slate-300 p-3"><input name="selling_price" type="number" min="0.01" step="0.01" required placeholder="Selling price" class="w-full rounded-xl border border-slate-300 p-3"></div>
        <button @disabled($products->isEmpty()) class="rounded-xl bg-[var(--brand-primary)] px-5 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50">Create Order</button>
    </form>
</main>
@endsection
