@extends('layouts.storefront')

@php
    $siteBrand = app(\App\Support\SiteBrand::class);
@endphp
@section('title', 'Previews | '.config('app.name'))
@section('meta_description', 'Watch product previews and explore '.config('app.name').' reels bundles.')
@section('canonical_url', route('reels'))

@section('content')
<section class="reels-page px-4 pb-12 pt-16 sm:px-6" aria-label="Product previews">
    @if ($products->isNotEmpty())
        <div class="reels-feed mx-auto" data-reels-feed tabindex="0" aria-label="Video feed; use up and down arrows">
            @foreach ($products as $product)
                @php
                    $preview = $product->videos->first();
                    $defaultVariant = $product->variants->first();
                    $price = (float) ($defaultVariant?->effective_price ?? $product->effective_price);
                    $regularPrice = (float) ($defaultVariant?->price ?? $product->regular_price);
                    $hasSale = $regularPrice > $price;
                    $discount = $hasSale && $regularPrice > 0 ? (int) round((1 - ($price / $regularPrice)) * 100) : 0;
                @endphp
                <article class="reel-card" data-reel-card data-reel-product-id="{{ $product->id }}" data-reel-playing="false">
                    <video
                        data-reel-video
                        class="reel-video"
                        muted
                        loop
                        playsinline
                        preload="none"
                        aria-hidden="true"
                        @if ($product->images->first()) poster="{{ $product->images->first()->url }}" @endif
                    >
                        <source src="{{ $preview->url }}" type="{{ $preview->mime_type ?: 'video/mp4' }}">
                    </video>

                    <div class="reel-progress" aria-hidden="true"><span data-reel-progress></span></div>
                    <button type="button" data-reel-toggle class="reel-play-toggle" aria-label="Play preview: {{ $siteBrand->text($product->name) }}">
                        <svg data-reel-play-icon class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                    <p data-reel-status class="reel-status" role="status" aria-live="polite"></p>

                    <div class="reel-actions" aria-label="Preview actions">
                        <button type="button" data-reel-sound class="reel-action-button" aria-label="Play preview with sound" aria-pressed="false">
                            <svg data-reel-sound-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 10v4h4l5 4V6l-5 4H4Z"/><path d="M17 9a5 5 0 0 1 0 6m2.5-8.5a8.5 8.5 0 0 1 0 11"/></svg>
                        </button>
                        <button type="button" data-reel-like data-reel-product-id="{{ $product->id }}" class="reel-action-button" aria-label="Like preview" aria-pressed="false">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 8.7c0 5.1-8.8 10-8.8 10s-8.8-4.9-8.8-10A4.7 4.7 0 0 1 12 6.3a4.7 4.7 0 0 1 8.8 2.4Z"/></svg>
                            <span data-reel-like-count>0</span>
                        </button>
                        <button type="button" data-reel-save data-reel-product-id="{{ $product->id }}" class="reel-action-button" aria-label="Save for later" aria-pressed="false">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 4.75A1.75 1.75 0 0 1 7.75 3h8.5A1.75 1.75 0 0 1 18 4.75V21l-6-4-6 4V4.75Z"/></svg>
                        </button>
                        <button
                            type="button"
                            data-share-product
                            data-share-title="{{ $siteBrand->text($product->name) }}"
                            data-share-text="Watch {{ $siteBrand->text($product->name) }} on {{ config('app.name') }}"
                            data-share-url="{{ route('products.show', $product->slug) }}"
                            class="reel-action-button"
                            aria-label="Share {{ $siteBrand->text($product->name) }}"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="m8.2 10.8 7.6-4.5m-7.6 7.9 7.6 4.5"/></svg>
                        </button>
                    </div>

                    <div class="reel-details">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/75">Reels Bundle</p>
                        <h2 class="mt-1 line-clamp-2 text-xl font-black leading-tight">{{ $siteBrand->text($product->name) }}</h2>
                        <a href="{{ route('products.show', $product->slug) }}" class="reel-product-link mt-3 inline-flex max-w-full items-center rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                            <span class="truncate">View {{ $siteBrand->text($product->name) }}</span>
                            <span class="ml-2 shrink-0" aria-hidden="true">↗</span>
                        </a>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="text-lg font-black">₹{{ number_format($price, 2) }}</span>
                            @if ($hasSale)
                                <span class="text-xs text-white/65 line-through">₹{{ number_format($regularPrice, 2) }}</span>
                                <span class="rounded-full bg-emerald-400/20 px-2 py-1 text-[10px] font-extrabold text-emerald-100">{{ $discount }}% OFF</span>
                            @endif
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <button type="button" data-add-to-cart data-product-id="{{ $product->id }}" data-product-variant-id="{{ $defaultVariant?->id }}" class="reel-cart-button">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2 12h10l3-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg>
                                Add to cart
                            </button>
                            <button type="button" data-buy-now data-product-id="{{ $product->id }}" data-product-variant-id="{{ $defaultVariant?->id }}" class="reel-buy-button">
                                Buy now <span aria-hidden="true">→</span>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="mx-auto max-w-xl rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12">
            <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-orange-500">Product previews</p>
            <h1 class="mt-3 text-2xl font-black text-slate-900">No reel previews are available yet.</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Explore the Reels Bundle collection to find products.</p>
            <a href="{{ route('shop', ['category' => 'reels-bundle']) }}" class="mt-6 inline-flex rounded-full bg-orange-500 px-5 py-3 text-sm font-bold text-white">Browse Reels Bundle</a>
        </div>
    @endif
</section>
@endsection
