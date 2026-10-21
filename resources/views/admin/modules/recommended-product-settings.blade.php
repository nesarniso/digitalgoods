@extends('layouts.admin')
@section('title', 'Recommended product')
@section('content')
<section class="mx-auto max-w-4xl px-6 py-10 lg:px-10">
    <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Homepage settings</p>
    <h1 class="mt-2 text-3xl font-black">Recommended product</h1>
    <p class="mt-2 text-slate-500">Select the recommended product and choose multiple latest products. Only product names are loaded here.</p>
    @if(session('success'))
        <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.settings.recommended-product.save') }}" class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        <label class="block text-sm font-semibold text-slate-700">
            Recommended product
            <select name="recommended_product_id" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                <option value="0">Automatic selection (featured/latest)</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected((int) old('recommended_product_id', $recommendedProductId) === $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="mt-6 block text-sm font-semibold text-slate-700">
            Latest products
            @php($selectedLatestProductIds = array_map('intval', $latestProductIds))
            <select name="latest_product_ids[]" multiple size="8" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(in_array($product->id, old('latest_product_ids', $selectedLatestProductIds), true))>{{ $product->name }}</option>
                @endforeach
            </select>
            <span class="mt-2 block text-xs font-normal text-slate-500">Use Ctrl/Cmd + click to select multiple products. Maximum 12.</span>
        </label>
        <button type="submit" class="mt-6 w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-500">Save recommended product</button>
    </form>
</section>
@endsection
