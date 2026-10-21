@php($hasSale = $product->sale_price !== null && (float) $product->sale_price < (float) $product->regular_price)
@php($defaultVariant = $product->variants->first())
@php($isActiveEntrepreneur = auth()->check() && auth()->user()->hasRole('entrepreneur') && auth()->user()->entrepreneurProfile?->status === 'active')
@php($alreadyAdded = $isActiveEntrepreneur && $product->entrepreneurProducts()->where('entrepreneur_id', auth()->id())->where('status', 'active')->exists())
@php($quickVariantAttributes = $product->variants->flatMap(fn ($variant) => $variant->attributeValues)->groupBy('attribute_id')->map(fn ($values) => ['name' => $values->first()?->attribute?->name, 'values' => $values->unique('id')->map(fn ($value) => ['id' => $value->id, 'value' => $value->value])->values()])->values())
@php($quickVariants = $product->variants->map(fn ($variant) => ['id' => $variant->id, 'price' => (float) $variant->effective_price, 'attributes' => $variant->attributeValues->pluck('id')->values(), 'is_default' => (bool) $variant->is_default])->values())
<article class="premium-product-card group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white transition duration-300 hover:-translate-y-1 hover:border-orange-200">
    <button type="button" data-quick-view
        data-product-name="{{ $product->name }}"
        data-product-price="৳{{ number_format((float) ($defaultVariant?->effective_price ?? $product->effective_price), 2) }}"
        data-product-image="{{ $product->images->first()?->url }}"
        data-product-url="{{ route('products.show', $product->slug) }}"
        data-product-id="{{ $product->id }}"
        data-product-description="{{ $product->short_description ?: $product->description }}"
        data-product-rating="{{ number_format((float) $product->reviews->avg('rating'), 1) }}"
        data-product-reviews="{{ $product->reviews->count() }}"
        data-product-variant-attributes="{{ $quickVariantAttributes->toJson() }}"
        data-product-variants="{{ $quickVariants->toJson() }}"
        data-product-share-url="{{ route('products.show', $product->slug) }}"
        data-entrepreneur-add-url="{{ $isActiveEntrepreneur ? route('entrepreneur.marketplace.store', $product) : '' }}"
        data-entrepreneur-already-added="{{ $alreadyAdded ? '1' : '0' }}"
        aria-label="Quick view {{ $product->name }}"
        class="product-quick-view-trigger relative block w-full text-left">
    <div data-product-image-wrap class="relative flex aspect-square items-center justify-center overflow-hidden">
        @if ($product->images->first())
            <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
        @else
            <span class="text-4xl font-black text-indigo-200">{{ Str::upper(Str::substr($product->name, 0, 1)) }}</span>
        @endif
        <span class="product-quick-view-overlay pointer-events-none absolute inset-0 flex items-center justify-center bg-slate-950/10 transition duration-200" aria-hidden="true">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/95 text-[var(--brand-primary)] shadow-lg ring-1 ring-slate-200 transition duration-200">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                    <circle cx="12" cy="12" r="2.8"/>
                </svg>
            </span>
        </span>
    </div>
    @if($hasSale)<span class="absolute left-3 top-3 z-10 rounded-full bg-[var(--brand-primary)] px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-white shadow-sm">Sale</span>@endif
    </button>
    <div class="p-4 sm:p-5">
        <a href="{{ route('products.show', $product->slug) }}" class="block min-h-11 line-clamp-2 text-sm font-bold leading-5 text-slate-800 transition group-hover:text-[var(--brand-primary)]">{{ $product->name }}</a>
        @if($product->reviews->isNotEmpty())
            <div class="mt-2 flex items-center gap-1.5 text-xs"><span class="tracking-wide text-amber-400">★</span><span class="font-bold text-slate-700">{{ number_format((float) $product->reviews->avg('rating'), 1) }}</span><span class="text-slate-400">({{ $product->reviews->count() }})</span></div>
        @endif
        <div class="mt-3 flex flex-wrap items-baseline gap-2"><span class="text-xl font-black text-slate-950">৳{{ number_format((float) ($defaultVariant?->effective_price ?? $product->effective_price), 2) }}</span>@if($defaultVariant?->sale_price !== null || $hasSale)<span class="text-xs text-slate-400 line-through">৳{{ number_format((float) ($defaultVariant?->price ?? $product->regular_price), 2) }}</span>@endif</div>
        <div class="mt-4 grid grid-cols-2 gap-2">
            <button type="button" data-add-to-cart data-product-id="{{ $product->id }}" data-product-variant-id="{{ $defaultVariant?->id }}" class="rounded-xl border border-[var(--brand-primary)] px-2 py-2.5 text-xs font-bold text-[var(--brand-primary)] transition hover:bg-[var(--brand-primary)] hover:text-white">Add to cart</button>
            <button type="button" data-buy-now data-product-id="{{ $product->id }}" data-product-variant-id="{{ $defaultVariant?->id }}" class="rounded-xl bg-[var(--brand-primary)] px-2 py-2.5 text-xs font-bold text-white transition hover:opacity-90">Buy now</button>
        </div>
    </div>
</article>
