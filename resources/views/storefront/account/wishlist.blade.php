@extends('layouts.storefront')
@section('title', 'Wishlist | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Saved products</p><h1 class="mt-2 text-4xl font-black">My wishlist</h1></div>
        <a href="{{ route('account.index') }}" class="text-sm font-semibold text-indigo-700">← Back to account</a>
    </div>
    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($items as $item)
            @include('storefront._product-card', ['product' => $item->product])
            <div class="-mt-5 sm:col-span-2 lg:col-span-4">
                <form method="POST" action="{{ route('account.wishlist.destroy', $item->product) }}">@csrf @method('DELETE')<button class="text-xs font-semibold text-red-600">Remove {{ $item->product->name }} from wishlist</button></form>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-12 text-center text-slate-500 ring-1 ring-slate-200">Your wishlist is empty. <a href="{{ route('shop') }}" class="font-semibold text-indigo-700">Browse products</a></div>
        @endforelse
    </div>
</section>
@endsection
