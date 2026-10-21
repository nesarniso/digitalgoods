@extends('layouts.admin')
@section('title', 'Inventory')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <h1 class="text-3xl font-black">Inventory</h1>
    <p class="mt-2 text-slate-500">Monitor and adjust stock by product and warehouse.</p>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.inventory.adjust') }}" class="mt-8 grid gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:grid-cols-4">
        @csrf
        <select name="product_id" required class="rounded-xl border border-slate-300 px-4 py-3"><option value="">Select product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>@endforeach</select>
        <select name="warehouse_id" required class="rounded-xl border border-slate-300 px-4 py-3"><option value="">Select warehouse</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach</select>
        <input name="delta" type="number" required min="-999999" placeholder="Stock change (+/-)" class="rounded-xl border border-slate-300 px-4 py-3">
        <input name="reason" required placeholder="Reason" class="rounded-xl border border-slate-300 px-4 py-3">
        <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white md:col-span-4">Update stock</button>
    </form>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-6 py-3">Product</th><th class="px-6 py-3">Warehouse</th><th class="px-6 py-3">Physical</th><th class="px-6 py-3">Reserved</th><th class="px-6 py-3">Available</th><th class="px-6 py-3">State</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($inventory as $stock)<tr><td class="px-6 py-4 font-semibold">{{ $stock->product?->name }}</td><td class="px-6 py-4">{{ $stock->warehouse?->name }}</td><td class="px-6 py-4">{{ $stock->physical_stock }}</td><td class="px-6 py-4">{{ $stock->reserved_stock }}</td><td class="px-6 py-4 font-bold">{{ $stock->available_stock }}</td><td class="px-6 py-4">{{ $stock->isOutOfStock() ? 'Out of stock' : ($stock->isLowStock() ? 'Low stock' : 'Healthy') }}</td></tr>@empty<tr><td colspan="6" class="px-6 py-10 text-center text-slate-500">Create a warehouse first, then use the form above to add stock.</td></tr>@endforelse</tbody></table></div><div class="mt-6">{{ $inventory->links() }}</div>
</section>
@endsection
