@extends('layouts.storefront')

@section('title', 'Shop | '.config('app.name'))
@section('meta_description', request('q') ? 'Search results for '.request('q').' at '.config('app.name') : 'Browse quality products at '.config('app.name'))
@section('canonical_url', route('shop'))
@if(request()->hasAny(['q', 'sort', 'page'])) @section('robots', 'noindex,follow') @endif

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6">
    <div class="grid gap-8 lg:grid-cols-[250px_minmax(0,1fr)]">
        <aside class="hidden h-fit rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-24 lg:block">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4"><h2 class="text-base font-black text-slate-800">Categories</h2><span class="text-xs font-semibold text-[var(--brand-primary)]">Browse</span></div>
            <nav class="mt-3 max-h-[65vh] space-y-1 overflow-y-auto pr-1">
                <a href="{{ route('shop', array_filter(['q' => request('q')])) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ !request('category') ? 'bg-orange-50 text-[var(--brand-primary)]' : 'text-slate-700 hover:bg-slate-50' }}"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-xs">▦</span>All products</a>
                @foreach($categories as $category)
                    @php($isCurrentBranch = request('category') === $category->slug || $category->children->contains('slug', request('category')) || $category->children->flatMap(fn ($child) => $child->children)->contains('slug', request('category')))
                    <div class="rounded-xl {{ $isCurrentBranch ? 'bg-orange-50/60' : '' }}">
                        <a href="{{ route('shop', array_filter(['category' => $category->slug, 'q' => request('q')])) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request('category') === $category->slug ? 'text-[var(--brand-primary)]' : 'text-slate-700 hover:bg-slate-50' }}">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-xs">@if($category->icon_path)<img src="{{ asset('storage/media/'.$category->icon_path) }}" alt="" class="h-full w-full object-cover">@else ✦ @endif</span><span class="min-w-0 flex-1 truncate">{{ $category->name }}</span>@if($category->children->isNotEmpty())<span class="text-slate-400">›</span>@endif
                        </a>
                        @if($category->children->isNotEmpty())
                            <div class="ml-6 border-l border-slate-200 pb-2 pl-3">
                                @foreach($category->children as $child)
                                    <a href="{{ route('shop', array_filter(['category' => $child->slug, 'q' => request('q')])) }}" class="mt-1 block rounded-lg px-2 py-1.5 text-xs {{ request('category') === $child->slug ? 'font-bold text-[var(--brand-primary)]' : 'text-slate-500 hover:bg-white hover:text-slate-800' }}">{{ $child->name }}</a>
                                    @foreach($child->children as $grandchild)
                                        <a href="{{ route('shop', array_filter(['category' => $grandchild->slug, 'q' => request('q')])) }}" class="ml-3 mt-1 block rounded-lg px-2 py-1.5 text-xs {{ request('category') === $grandchild->slug ? 'font-bold text-[var(--brand-primary)]' : 'text-slate-400 hover:bg-white hover:text-slate-800' }}">› {{ $grandchild->name }}</a>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>
        </aside>
        <div class="min-w-0">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end"><div><p class="text-sm font-bold uppercase tracking-widest text-[var(--brand-primary)]">Catalog</p><h1 class="mt-2 text-4xl font-black">{{ request('category') ? 'Products' : 'Shop all products' }}</h1><p class="mt-2 text-slate-500">{{ $products->total() }} products available</p></div>
                <form method="GET" class="flex gap-2"><input type="hidden" name="q" value="{{ request('q') }}"><input type="hidden" name="category" value="{{ request('category') }}"><select name="sort" onchange="this.form.submit()" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm"><option value="">Newest</option><option value="price_low" @selected(request('sort') === 'price_low')>Price: low to high</option><option value="price_high" @selected(request('sort') === 'price_high')>Price: high to low</option></select></form>
            </div>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($products as $product) @include('storefront._product-card', ['product' => $product]) @empty <p class="col-span-full rounded-2xl bg-white p-12 text-center text-slate-500 ring-1 ring-slate-200">No published products match your search.</p> @endforelse
    </div>
    <div class="mt-10">{{ $products->links() }}</div>
        </div>
    </div>
</section>
@endsection
