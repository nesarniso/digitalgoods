@extends('layouts.storefront')
@section('title', 'Compare products | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Product comparison</p><h1 class="mt-2 text-4xl font-black">Compare products</h1></div>
        <a href="{{ route('account.index') }}" class="text-sm font-semibold text-indigo-700">← Back to account</a>
    </div>
    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($items->isEmpty())
        <div class="mt-8 rounded-2xl bg-white p-12 text-center text-slate-500 ring-1 ring-slate-200">No products selected for comparison. <a href="{{ route('shop') }}" class="font-semibold text-indigo-700">Browse products</a></div>
    @else
        <div class="mt-8 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <table class="min-w-[680px] w-full text-left text-sm">
                <thead><tr class="border-b border-slate-200">@foreach($items as $item)<th class="min-w-[180px] p-5 align-top"><div class="h-32 overflow-hidden rounded-xl bg-slate-100">@if($item->product->images->first())<img src="{{ $item->product->images->first()->url }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">@endif</div><a href="{{ route('products.show', $item->product->slug) }}" class="mt-3 block font-bold text-slate-800">{{ $item->product->name }}</a></th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>@foreach($items as $item)<td class="p-5"><span class="text-slate-500">Price</span><p class="mt-1 text-lg font-black">₹{{ number_format((float) $item->product->effective_price, 2) }}</p></td>@endforeach</tr>
                    <tr>@foreach($items as $item)<td class="p-5"><span class="text-slate-500">Brand</span><p class="mt-1 font-semibold">{{ $item->product->brand?->name ?: '—' }}</p></td>@endforeach</tr>
                    <tr>@foreach($items as $item)<td class="p-5"><span class="text-slate-500">SKU</span><p class="mt-1 font-semibold">{{ $item->product->sku }}</p></td>@endforeach</tr>
                    <tr>@foreach($items as $item)<td class="p-5"><span class="text-slate-500">Description</span><p class="mt-1 text-slate-700">{{ Str::limit(strip_tags($item->product->short_description ?: $item->product->description), 130) ?: '—' }}</p></td>@endforeach</tr>
                    <tr>@foreach($items as $item)<td class="p-5"><form method="POST" action="{{ route('account.compare.destroy', $item->product) }}">@csrf @method('DELETE')<button class="font-semibold text-red-600">Remove</button></form></td>@endforeach</tr>
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
