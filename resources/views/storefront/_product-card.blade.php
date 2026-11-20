@php($hasSale = $product->sale_price !== null && (float) $product->sale_price < (float) $product->regular_price)
@php($siteBrand = app(\App\Support\SiteBrand::class))
@php($defaultVariant = $product->variants->first())
@php($isActiveEntrepreneur = auth()->check() && auth()->user()->hasRole('entrepreneur') && auth()->user()->entrepreneurProfile?->status === 'active')
@php($alreadyAdded = $isActiveEntrepreneur && $product->entrepreneurProducts()->where('entrepreneur_id', auth()->id())->where('status', 'active')->exists())
@php($quickVariantAttributes = $product->variants->flatMap(fn ($variant) => $variant->attributeValues)->groupBy('attribute_id')->map(fn ($values) => ['name' => $values->first()?->attribute?->name, 'values' => $values->unique('id')->map(fn ($value) => ['id' => $value->id, 'value' => $value->value])->values()])->values())
@php($quickVariants = $product->variants->map(fn ($variant) => ['id' => $variant->id, 'price' => (float) $variant->effective_price, 'attributes' => $variant->attributeValues->pluck('id')->values(), 'is_default' => (bool) $variant->is_default])->values())
<article class="premium-product-card group relative overflow-hidden rounded-lg border border-slate-200/90 bg-white transition duration-300 hover:-translate-y-1 hover:border-red-200">
    <button type="button" data-quick-view
        data-product-name="{{ $siteBrand->text($product->name) }}"
        data-product-price="₹{{ number_format((float) ($defaultVariant?->effective_price ?? $product->effective_price), 2) }}"
        data-product-image="{{ $product->images->first()?->url }}"
        data-product-url="{{ route('products.show', $product->slug) }}"
        data-product-id="{{ $product->id }}"
        data-product-description="{{ $siteBrand->text($product->short_description ?: $product->description) }}"
        data-product-rating="{{ number_format((float) $product->reviews->avg('rating'), 1) }}"
        data-product-reviews="{{ $product->reviews->count() }}"
        data-product-variant-attributes="{{ $quickVariantAttributes->toJson() }}"
        data-product-variants="{{ $quickVariants->toJson() }}"
        data-product-share-url="{{ route('products.show', $product->slug) }}"
        data-entrepreneur-add-url="{{ $isActiveEntrepreneur ? route('entrepreneur.marketplace.store', $product) : '' }}"
        data-entrepreneur-already-added="{{ $alreadyAdded ? '1' : '0' }}"
        aria-label="Quick view {{ $siteBrand->text($product->name) }}"
        class="product-quick-view-trigger relative block w-full text-left">
    <div data-product-image-wrap class="relative flex aspect-square items-center justify-center overflow-hidden">
        @if ($product->images->first())
            <img src="{{ $product->images->first()->url }}" alt="{{ $siteBrand->text($product->name) }}" loading="lazy" class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full flex-col items-center justify-center bg-[radial-gradient(circle_at_50%_30%,#fff_0%,#fff2e8_46%,#fce4e4_100%)] p-4 text-center">
                <span class="text-5xl font-black text-[#f58220]">✦</span>
                <span class="mt-2 text-[9px] font-black uppercase tracking-[0.2em] text-[#c96b44]">Digital product</span>
                <span class="mt-2 line-clamp-2 text-xs font-bold text-slate-700">{{ $siteBrand->text($product->name) }}</span>
            </div>
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
    @if($hasSale && (float) $product->regular_price > 0)<span class="absolute left-2.5 top-2.5 z-10 rounded-full bg-slate-900 px-2 py-1 text-[9px] font-black uppercase tracking-wide text-white shadow-sm">-{{ (int) (100 - (((float) $product->sale_price / (float) $product->regular_price) * 100)) }}%</span>@endif
    </button>
    <div class="p-3 sm:p-4">
        @if($product->category)
            <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="mb-1 block truncate text-[10px] font-medium text-slate-400 hover:text-[var(--brand-primary)]">{{ $product->category->name }}</a>
        @endif
        <a href="{{ route('products.show', $product->slug) }}" class="block min-h-11 line-clamp-2 text-[13px] font-bold leading-5 text-slate-700 transition group-hover:text-[var(--brand-primary)]">{{ $siteBrand->text($product->name) }}</a>
        @if($product->reviews->isNotEmpty())
            <div class="mt-2 flex items-center gap-1.5 text-xs"><span class="tracking-wide text-amber-400">★</span><span class="font-bold text-slate-700">{{ number_format((float) $product->reviews->avg('rating'), 1) }}</span><span class="text-slate-400">({{ $product->reviews->count() }})</span></div>
        @endif
        <div class="mt-3 flex flex-wrap items-baseline gap-2"><span class="text-lg font-black text-[var(--brand-primary)]">₹{{ number_format((float) ($defaultVariant?->effective_price ?? $product->effective_price), 2) }}</span>@if($defaultVariant?->sale_price !== null || $hasSale)<span class="text-[11px] text-slate-400 line-through">₹{{ number_format((float) ($defaultVariant?->price ?? $product->regular_price), 2) }}</span>@endif</div>
        <div class="mt-3 flex items-center justify-end">
            <button type="button" data-add-to-cart data-product-id="{{ $product->id }}" data-product-variant-id="{{ $defaultVariant?->id }}" aria-label="Add {{ $siteBrand->text($product->name) }} to cart" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-[var(--brand-primary)] transition hover:border-[var(--brand-primary)] hover:bg-[var(--brand-primary)] hover:text-white">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2 12h10l3-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg>
            </button>
        </div>
    </div>
</article>
