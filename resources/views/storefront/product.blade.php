@extends('layouts.storefront')

@php
    $siteBrand = app(\App\Support\SiteBrand::class);
@endphp
@section('title', $siteBrand->text($product->meta_title ?: $product->name).' | '.config('app.name'))
@section('meta_description', $siteBrand->text($product->meta_description ?: $product->short_description ?: $product->description ?: $product->name))
@section('canonical_url', route('products.show', $product->slug))
@section('og_type', 'product')
@section('og_title', $siteBrand->text($product->name).' | '.config('app.name'))
@if ($product->images->first())
    @section('og_image', $product->images->first()->url)
@endif
@section('structured_data')
@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $siteBrand->text($product->name),
        'description' => $siteBrand->text($product->meta_description ?: $product->short_description ?: $product->description),
        'sku' => $product->sku,
        'url' => route('products.show', $product->slug),
        'image' => $product->images->first()?->url,
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'INR',
            'price' => number_format((float) $product->effective_price, 2, '.', ''),
            'availability' => 'https://schema.org/InStock',
            'url' => route('products.show', $product->slug),
        ],
        'brand' => $product->brand ? [
            '@type' => 'Brand',
            'name' => $siteBrand->text($product->brand->name),
        ] : null,
        'aggregateRating' => $totalReviews > 0 ? [
            '@type' => 'AggregateRating',
            'ratingValue' => number_format((float) $averageRating, 1, '.', ''),
            'reviewCount' => $totalReviews,
        ] : null,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES) !!}</script>
@php
    $breadcrumbData = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Shop', 'item' => route('shop')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $siteBrand->text($product->name), 'item' => route('products.show', $product->slug)],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($breadcrumbData, JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')
<section data-product-page class="mx-auto max-w-5xl px-4 py-12 pb-44 sm:px-6 sm:pb-28">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
            {{ $errors->first() }}
        </div>
    @endif
    <a href="{{ route('shop') }}" class="text-sm font-semibold text-indigo-700">← Back to shop</a>
    <div class="mt-6 grid gap-6 md:grid-cols-2 lg:gap-8">
    <div class="product-gallery w-full max-w-xl">
        <div class="product-gallery-main group relative flex items-center justify-center overflow-hidden rounded-[2rem] border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/70 to-slate-100 shadow-[0_24px_70px_rgba(30,41,59,0.12)]" style="aspect-ratio: 1.16 / 1;">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_15%,rgba(255,255,255,.95),transparent_35%)]"></div>
            @if ($product->images->first())
                <img data-gallery-main src="{{ $product->images->first()->url }}" alt="{{ $siteBrand->text($product->images->first()->alt_text ?: $product->name) }}" title="{{ $siteBrand->text($product->images->first()->title) }}" class="product-gallery-main-image relative h-full w-full object-cover">
            @else
                <span class="text-8xl font-black text-indigo-200">{{ Str::upper(Str::substr($siteBrand->text($product->name), 0, 1)) }}</span>
            @endif
        </div>
        @if ($product->images->count() > 1)
            <div class="mt-4 flex gap-3 overflow-x-auto pb-2" aria-label="Product images">
                @foreach ($product->images as $image)
                    <button type="button" data-gallery-thumbnail data-image-url="{{ $image->url }}" data-image-alt="{{ $siteBrand->text($image->alt_text ?: $product->name) }}" class="product-gallery-thumb shrink-0 overflow-hidden rounded-2xl border-2 p-1 transition hover:-translate-y-0.5 hover:border-[var(--brand-primary)] {{ $loop->first ? 'is-active' : 'border-slate-200' }}" aria-label="View product image {{ $loop->iteration }}">
                        <img src="{{ $image->url }}" alt="" class="h-16 w-16 rounded-xl object-cover sm:h-20 sm:w-20">
                    </button>
                @endforeach
            </div>
        @endif
        </div>
        <div data-sticky-purchase-source class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.08)] sm:p-7">
            @if ($product->category)
                <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="inline-flex text-sm font-bold uppercase tracking-widest text-indigo-600 transition hover:text-indigo-800 hover:underline">
                    {{ $product->category->name }}
                </a>
            @endif
            <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">{{ $siteBrand->text($product->name) }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                <span class="text-lg tracking-wide text-amber-500" aria-label="{{ $totalReviews > 0 ? number_format((float) $averageRating, 1).' out of 5 stars' : 'No ratings yet' }}">{{ $totalReviews > 0 ? str_repeat('★', (int) round($averageRating)).str_repeat('☆', 5 - (int) round($averageRating)) : str_repeat('☆', 5) }}</span>
                <span class="font-bold text-slate-700">{{ $totalReviews > 0 ? number_format((float) $averageRating, 1) : 'New' }}</span>
                <span class="text-slate-300">|</span>
                <a href="#customer-reviews" class="text-slate-600 underline decoration-slate-300 underline-offset-4 hover:text-[var(--brand-primary)]"><strong>{{ $totalReviews }}</strong> {{ \Illuminate\Support\Str::plural('review', $totalReviews) }}</a>
                <span class="text-slate-300">|</span>
                <span><strong class="text-orange-500">{{ $productOrderCount }}</strong> Orders</span>
                <span class="text-slate-300">|</span>
                <span><strong class="text-orange-500">{{ $productWishlistCount }}</strong> Wish Listed</span>
            </div>
            @php
                $displayVariant = $product->variants->first();
            @endphp
            <p data-variant-price class="mt-4 text-3xl font-black text-indigo-700">₹{{ number_format((float) ($displayVariant?->effective_price ?? $product->effective_price), 2) }}</p>
            <p data-variant-regular-price class="{{ ($displayVariant?->sale_price !== null || $product->sale_price) ? '' : 'hidden' }} mt-1 text-sm text-slate-400 line-through">₹{{ number_format((float) ($displayVariant?->price ?? $product->regular_price), 2) }}</p>
            <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50/70 p-4"><p class="leading-7 text-slate-600">{{ $product->slug === 'all-bundles' ? 'Access ready-made reels, premium digital content, video assets and creative resources for personal use.' : ($product->slug === 'all-bundles-reseller-rights' ? 'Get all Personal Use plan benefits plus reseller rights for eligible digital products, your own selling prices and branding options.' : $siteBrand->text($product->description ?: $product->short_description ?: 'A quality product selected for your everyday needs.')) }}</p></div>
            @if ($product->isVariable() && $product->variants->isNotEmpty())
                @php
                    $defaultVariant = $product->variants->first();
                    $variantPickerData = $product->variants->map(static function ($variant): array {
                        return [
                            'id' => $variant->id,
                            'attributes' => $variant->attributeValues->pluck('id')->values(),
                            'price' => (float) $variant->effective_price,
                            'regular_price' => (float) $variant->price,
                            'has_sale' => $variant->sale_price !== null && (float) $variant->sale_price < (float) $variant->price,
                            'is_default' => (bool) $variant->is_default,
                        ];
                    })->values();
                @endphp
                <div class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4" data-variant-picker data-variants="{{ $variantPickerData->toJson() }}">
                    <p class="font-bold text-slate-800">Choose an option</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($variantAttributes as $attributeId => $values)
                            <label class="text-sm font-semibold text-slate-700">
                                {{ $values->first()->attribute?->name ?? 'Option' }}
                                <select data-variant-attribute="{{ $attributeId }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 font-normal">
                                    <option value="">Select {{ strtolower($values->first()->attribute?->name ?? 'option') }}</option>
                                    @foreach ($values as $value)
                                        <option value="{{ $value->id }}" @selected($defaultVariant?->attributeValues->contains('id', $value->id))>{{ $value->value }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endforeach
                    </div>
                    <p data-variant-message class="mt-2 hidden text-sm font-semibold text-red-600">This combination is unavailable.</p>
                    <input type="hidden" data-selected-variant>
                </div>
            @endif
            <div class="mt-6 flex items-center gap-3">
                <span class="font-bold text-slate-700">Quantity:</span>
                <div class="flex h-12 overflow-hidden rounded-xl border border-orange-400">
                    <button type="button" data-quantity-decrease class="w-11 bg-white text-lg text-orange-500 hover:bg-orange-50">−</button>
                    <input data-product-quantity data-product-page-quantity value="1" min="1" type="number" class="w-14 border-x border-orange-100 text-center outline-none">
                    <button type="button" data-quantity-increase class="w-11 bg-white text-lg text-orange-500 hover:bg-orange-50">+</button>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <button type="button" data-share-product data-share-title="{{ $siteBrand->text($product->name) }}" data-share-url="{{ $shareUrl }}" class="flex h-12 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 font-semibold text-slate-700 shadow-sm hover:border-indigo-300 hover:text-indigo-700" aria-label="Share {{ $siteBrand->text($product->name) }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="m8.2 10.8 7.6-4.5m-7.6 7.9 7.6 4.5"/></svg>
                    Share
                </button>
                <button data-buy-now data-product-id="{{ $product->id }}" class="rounded-xl bg-[var(--brand-primary)] px-6 py-3 font-bold text-white hover:opacity-90">Buy now</button>
                <button data-add-to-cart data-product-id="{{ $product->id }}" class="rounded-xl bg-[var(--brand-primary)] px-6 py-3 font-bold text-white hover:opacity-90">Add to cart</button>
                @if($entrepreneurCanSell)
                    <form method="POST" action="{{ route('entrepreneur.marketplace.store', $product) }}">
                        @csrf
                        <button type="submit" @disabled($entrepreneurHasProduct) class="rounded-xl border-2 px-5 py-2.5 font-bold {{ $entrepreneurHasProduct ? 'cursor-not-allowed border-slate-300 bg-slate-100 text-slate-500' : 'border-emerald-500 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                            {{ $entrepreneurHasProduct ? 'Already Added' : 'Sell This Product' }}
                        </button>
                    </form>
                @endif
                @auth
                    <form method="POST" action="{{ route('account.wishlist.store', $product) }}">
                        @csrf
                        <button type="submit" class="flex h-12 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-orange-500 shadow-sm hover:border-orange-300" aria-label="Add {{ $siteBrand->text($product->name) }} to wishlist">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.35-9.5-9.1C.7 7.9 2.9 4.5 6.4 4.5c2.1 0 3.6 1.2 4.6 2.5 1-1.3 2.5-2.5 4.6-2.5 3.5 0 5.7 3.4 3.9 7.4C19.5 16.65 12 21 12 21Z"/></svg>
                            <span>{{ $productWishlistCount }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('account.login') }}" class="flex h-12 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-orange-500 shadow-sm hover:border-orange-300" aria-label="Sign in to use wishlist">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.35-9.5-9.1C.7 7.9 2.9 4.5 6.4 4.5c2.1 0 3.6 1.2 4.6 2.5 1-1.3 2.5-2.5 4.6-2.5 3.5 0 5.7 3.4 3.9 7.4C19.5 16.65 12 21 12 21Z"/></svg>
                        <span>{{ $productWishlistCount }}</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
    @if ($product->videos->isNotEmpty())
        <section class="mt-12" aria-labelledby="sample-videos-title">
            <div class="mb-5 text-center">
                <p class="text-sm font-black uppercase tracking-[0.18em] text-rose-500">Product preview</p>
                <h2 id="sample-videos-title" class="mt-2 text-3xl font-black tracking-tight text-slate-900">Sample videos 👀</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($product->videos as $video)
                    <figure class="overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm">
                        <video class="aspect-[9/16] w-full bg-slate-950 object-contain" controls preload="metadata" playsinline>
                            <source src="{{ $video->url }}" type="{{ $video->mime_type }}">
                            Your browser does not support video playback.
                        </video>
                        <figcaption class="p-3 text-center text-sm font-bold text-orange-700">{{ $video->title ?: 'Sample '.$loop->iteration }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif
    <div class="mt-16 space-y-6">
            <div class="min-w-0 space-y-6">
            @if ($product->slug === 'all-bundles')
                @include('storefront.product-plans.personal-details')
            @elseif ($product->slug === 'all-bundles-reseller-rights')
                @include('storefront.product-plans.reseller-details')
            @else
                <div class="relative min-w-0 overflow-hidden rounded-3xl border border-indigo-100/80 bg-gradient-to-br from-white via-white to-indigo-50/60 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8">
                    <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-indigo-100/70 blur-3xl"></div>
                    <div class="relative">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-lg text-indigo-600">▤</span>
                        <h2 class="mt-4 text-2xl font-black tracking-tight text-slate-900">Product description</h2>
                        <p class="mt-1 text-sm text-slate-500">Everything you need to know about this product</p>
                        <div class="mt-6 border-t border-indigo-100/80 pt-5 text-sm leading-7 text-slate-600">
                            {{ $siteBrand->text($product->description ?: $product->short_description ?: 'A quality product selected for your everyday needs.') }}
                        </div>
                        </div>
                </div>
            @endif
            <section id="customer-reviews" class="relative min-w-0 scroll-mt-28 overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8" aria-labelledby="customer-reviews-title">
                <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-orange-100/60 blur-3xl"></div>
                <div class="relative">
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-6">
                        <div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-50 text-lg text-[var(--brand-primary)]" aria-hidden="true">★</span>
                            <h2 id="customer-reviews-title" class="mt-4 text-2xl font-black tracking-tight text-slate-900">Customer reviews</h2>
                            <p class="mt-1 text-sm text-slate-500">Honest feedback from customers who received this product.</p>
                        </div>
                        <a href="#write-review" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[var(--brand-primary)] hover:text-[var(--brand-primary)]">Write a review</a>
                    </div>

                    @if($totalReviews > 0)
                        <div class="relative mt-6 grid gap-6 rounded-2xl bg-slate-50/80 p-5 sm:grid-cols-[minmax(180px,0.8fr)_minmax(0,1.2fr)] sm:p-6">
                            <div class="flex flex-col justify-center sm:border-r sm:border-slate-200 sm:pr-6">
                                <p class="text-5xl font-black tracking-tight text-slate-900">{{ number_format((float) $averageRating, 1) }}</p>
                                <p class="mt-2 text-xl tracking-widest text-amber-500" aria-label="{{ number_format((float) $averageRating, 1) }} out of 5 stars">{{ str_repeat('★', (int) round($averageRating)).str_repeat('☆', 5 - (int) round($averageRating)) }}</p>
                                <p class="mt-2 text-sm font-medium text-slate-500">Based on {{ $totalReviews }} {{ \Illuminate\Support\Str::plural('review', $totalReviews) }}</p>
                            </div>
                            <div class="space-y-2.5" aria-label="Rating breakdown">
                                @for($star = 5; $star >= 1; $star--)
                                    <a href="{{ request()->fullUrlWithQuery(['rating' => $star, 'reviews_page' => null]).'#customer-reviews' }}" class="group flex min-h-8 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]" aria-label="Filter by {{ $star }} star{{ $star === 1 ? '' : 's' }}: {{ $reviewCounts[$star] }} reviews">
                                        <span class="w-12 shrink-0 text-sm font-semibold text-slate-600">{{ $star }} star{{ $star === 1 ? '' : 's' }}</span>
                                        <span class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-200"><span class="block h-full rounded-full bg-amber-400 transition group-hover:bg-amber-500" style="width: {{ $totalReviews > 0 ? ($reviewCounts[$star] / $totalReviews) * 100 : 0 }}%"></span></span>
                                        <span class="w-8 text-right text-xs tabular-nums text-slate-500">{{ $reviewCounts[$star] }}</span>
                                    </a>
                                @endfor
                            </div>
                        </div>
                    @else
                        <div class="relative mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 px-5 py-9 text-center">
                            <span class="text-2xl text-slate-300" aria-hidden="true">✦</span>
                            <p class="mt-2 font-semibold text-slate-700">Be the first to review</p>
                            <p class="mt-1 text-sm text-slate-500">Your experience can help another shopper choose with confidence.</p>
                        </div>
                    @endif

                    <div class="relative mt-6 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm font-bold text-slate-800">
                            @if($ratingFilter)
                                {{ $ratingFilter }}-star reviews <span class="font-normal text-slate-500">({{ $reviews->total() }})</span>
                                <a href="{{ request()->fullUrlWithQuery(['rating' => null, 'reviews_page' => null]).'#customer-reviews' }}" class="ml-2 text-xs font-bold text-[var(--brand-primary)] underline underline-offset-2">Clear filter</a>
                            @else
                                {{ $totalReviews }} {{ \Illuminate\Support\Str::plural('review', $totalReviews) }}
                            @endif
                        </p>
                        @if($totalReviews > 1)
                            <form method="GET" action="{{ route('products.show', $product->slug) }}" class="flex items-center gap-2">
                                @if($ratingFilter)<input type="hidden" name="rating" value="{{ $ratingFilter }}">@endif
                                <label for="review-sort" class="text-xs font-semibold text-slate-500">Sort by</label>
                                <select id="review-sort" name="review_sort" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[var(--brand-primary)] focus:outline-none focus:ring-2 focus:ring-orange-100">
                                    <option value="newest" @selected($reviewSort === 'newest')>Most recent</option>
                                    <option value="highest" @selected($reviewSort === 'highest')>Highest rated</option>
                                    <option value="lowest" @selected($reviewSort === 'lowest')>Lowest rated</option>
                                </select>
                                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700">Apply</button>
                            </form>
                        @endif
                    </div>

                    <div class="relative mt-4 divide-y divide-slate-100 border-y border-slate-100">
                        @forelse($reviews as $review)
                            @php
                                $reviewItem = $review->order?->items?->firstWhere('product_id', $product->id);
                            @endphp
                            <article class="py-5 first:pt-5 last:pb-5">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                    <span class="text-lg tracking-widest text-amber-500" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating).str_repeat('☆', 5 - $review->rating) }}</span>
                                    <span class="text-xs font-semibold text-slate-600">{{ $review->rating }} / 5</span>
                                    @if($review->verified_purchase)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700"><span aria-hidden="true">✓</span> Verified purchase</span>
                                    @endif
                                    @if($reviewItem?->variant_label)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $reviewItem->variant_label }}</span>
                                    @endif
                                </div>
                                @if($review->title)<h3 class="mt-3 font-bold text-slate-900">{{ $review->title }}</h3>@endif
                                @if($review->body)<p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $review->body }}</p>@else<p class="mt-2 text-sm italic text-slate-500">Rating only</p>@endif
                                <div class="mt-4 flex flex-wrap items-center gap-x-2 text-xs text-slate-500">
                                    <span class="font-semibold text-slate-700">{{ $review->public_author_name }}</span>
                                    <span aria-hidden="true">·</span>
                                    <time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->format('M j, Y') }}</time>
                                </div>
                            </article>
                        @empty
                            @if($ratingFilter)
                                <div class="py-10 text-center">
                                    <p class="font-semibold text-slate-700">No reviews at this rating yet.</p>
                                    <a href="{{ request()->fullUrlWithQuery(['rating' => null, 'reviews_page' => null]).'#customer-reviews' }}" class="mt-2 inline-block text-sm font-bold text-[var(--brand-primary)] underline underline-offset-2">See all reviews</a>
                                </div>
                            @endif
                        @endforelse
                    </div>
                    @if($reviews->hasPages())
                        <div class="relative mt-5">{{ $reviews->fragment('customer-reviews')->links() }}</div>
                    @endif

                    <div id="write-review" class="relative mt-8 scroll-mt-28 border-t border-slate-100 pt-7">
                        @auth
                            @if($reviewOrders->isNotEmpty())
                                <div class="max-w-3xl">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-lg text-indigo-600" aria-hidden="true">✎</span>
                                        <div><h3 class="text-xl font-black tracking-tight text-slate-900">Share your experience</h3><p class="mt-1 text-sm leading-6 text-slate-500">Reviews are available after delivery. Your verified purchase badge confirms the order included this product.</p></div>
                                    </div>
                                    <form method="POST" action="{{ route('account.products.reviews.store', $product) }}" class="mt-5 space-y-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-5 sm:p-6">
                                        @csrf
                                        <div>
                                            <label for="review-order" class="mb-2 block text-sm font-bold text-slate-700">Delivered order</label>
                                            <select id="review-order" name="order_id" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-orange-100">
                                                @foreach($reviewOrders as $reviewOrder)<option value="{{ $reviewOrder->id }}" @selected(old('order_id', request()->integer('review_order')) == $reviewOrder->id)>{{ $reviewOrder->order_number }} · {{ $reviewOrder->updated_at?->format('M j, Y') }}</option>@endforeach
                                            </select>
                                            @error('order_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                        <fieldset>
                                            <legend class="mb-2 text-sm font-bold text-slate-700">Your rating <span class="text-red-600" aria-hidden="true">*</span></legend>
                                            <div class="flex flex-row-reverse justify-end gap-1">
                                                @for($rating = 5; $rating >= 1; $rating--)
                                                    <input id="review-rating-{{ $rating }}" name="rating" type="radio" value="{{ $rating }}" required class="peer sr-only" @checked(old('rating') == $rating)>
                                                    <label for="review-rating-{{ $rating }}" class="flex h-11 w-11 cursor-pointer items-center justify-center rounded-xl text-2xl text-slate-300 transition hover:bg-amber-50 hover:text-amber-400 peer-checked:text-amber-500 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[var(--brand-primary)]" aria-label="{{ $rating }} out of 5 stars">★</label>
                                                @endfor
                                            </div>
                                            @error('rating')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </fieldset>
                                        <div>
                                            <label for="review-title" class="mb-2 block text-sm font-bold text-slate-700">Headline <span class="font-normal text-slate-400">· optional</span></label>
                                            <input id="review-title" name="title" value="{{ old('title') }}" maxlength="120" placeholder="Sum up your experience" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-orange-100">
                                            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label for="review-body" class="mb-2 block text-sm font-bold text-slate-700">Your review <span class="font-normal text-slate-400">· optional</span></label>
                                            <textarea id="review-body" name="body" rows="4" maxlength="3000" placeholder="What should other shoppers know about this product?" class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-400 focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-orange-100">{{ old('body') }}</textarea>
                                            <p class="mt-1 text-xs text-slate-500">Please keep your feedback honest and avoid sharing personal information.</p>
                                            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                                            <p class="text-xs text-slate-500">Your review will appear after moderation.</p>
                                            <button class="rounded-xl bg-[var(--brand-primary)] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:ring-offset-2">Submit review</button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="rounded-2xl bg-slate-50 p-5">
                                    <h3 class="font-bold text-slate-800">Bought this product?</h3>
                                    <p class="mt-1 text-sm text-slate-500">Once your order is delivered, you can leave one verified review for that purchase.</p>
                                </div>
                            @endif
                        @else
                            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-slate-50 p-5">
                                <div><h3 class="font-bold text-slate-800">Purchased and received this product?</h3><p class="mt-1 text-sm text-slate-500">Sign in to leave a review linked to your delivered order.</p></div>
                                <a href="{{ route('account.login') }}" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700">Sign in</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </section>
            </div>
            </div>
        </div>
    </div>
    @if ($relatedProducts->isNotEmpty())<div class="mt-20"><h2 class="text-2xl font-black">You may also like</h2><div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">@foreach ($relatedProducts as $relatedProduct) @include('storefront._product-card', ['product' => $relatedProduct]) @endforeach</div></div>@endif
    @php
        $stickyImage = $product->images->first();
        $stickyPrice = $displayVariant?->effective_price ?? $product->effective_price;
        $stickyRegularPrice = $displayVariant?->sale_price !== null
            ? $displayVariant->price
            : ($product->sale_price ? $product->regular_price : null);
    @endphp
    <aside data-sticky-purchase aria-hidden="true" class="fixed inset-x-0 bottom-0 z-[60] border-t border-slate-200 bg-white/95 opacity-0 shadow-[0_-8px_30px_rgba(15,23,42,0.16)] backdrop-blur-xl transition duration-200" aria-label="Quick product purchase">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-3 py-2 sm:flex-row sm:items-center sm:gap-4 sm:px-5 sm:py-2.5 lg:px-8">
            <div class="flex min-w-0 items-center gap-2 sm:flex-1 sm:gap-3">
                @if($stickyImage)
                    <img src="{{ $stickyImage->url }}" alt="" class="h-10 w-10 shrink-0 rounded-xl object-cover ring-1 ring-slate-200 sm:h-12 sm:w-12">
                @else
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 font-black text-orange-500 sm:h-12 sm:w-12">{{ Str::upper(Str::substr($siteBrand->text($product->name), 0, 1)) }}</span>
                @endif
                <p class="min-w-0 flex-1 truncate text-xs font-bold text-slate-800 sm:text-sm">{{ $siteBrand->text($product->name) }}</p>
                <div class="shrink-0 text-right">
                    @if($stickyRegularPrice !== null)
                        <span data-sticky-regular-price class="mr-1 text-[10px] text-slate-400 line-through sm:mr-2 sm:text-xs">₹{{ number_format((float) $stickyRegularPrice, 2) }}</span>
                    @else
                        <span data-sticky-regular-price class="mr-1 hidden text-[10px] text-slate-400 line-through sm:mr-2 sm:text-xs"></span>
                    @endif
                    <span data-sticky-price class="text-sm font-black text-orange-600 sm:text-base">₹{{ number_format((float) $stickyPrice, 2) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between gap-1 sm:justify-end sm:gap-2">
                <div class="flex h-9 shrink-0 items-center overflow-hidden rounded-full border border-slate-300 sm:h-10">
                    <button type="button" data-quantity-decrease class="h-full w-7 text-slate-600 hover:bg-slate-100 sm:w-9" aria-label="Decrease quantity">−</button>
                    <input data-product-quantity data-product-page-quantity data-sticky-quantity value="1" min="1" type="number" inputmode="numeric" class="h-full w-8 border-x border-slate-200 text-center text-xs outline-none sm:w-10 sm:text-sm" aria-label="Quantity">
                    <button type="button" data-quantity-increase class="h-full w-7 text-slate-600 hover:bg-slate-100 sm:w-9" aria-label="Increase quantity">+</button>
                </div>
                <button type="button" data-add-to-cart data-product-id="{{ $product->id }}" class="shrink-0 rounded-full bg-[#f58220] px-3 py-2 text-[10px] font-bold text-white transition hover:bg-orange-600 sm:px-5 sm:py-2.5 sm:text-xs">Add to Cart</button>
                <button type="button" data-buy-now data-product-id="{{ $product->id }}" class="shrink-0 rounded-full bg-[#242424] px-3 py-2 text-[10px] font-bold text-white transition hover:bg-black sm:px-5 sm:py-2.5 sm:text-xs">Buy Now</button>
                @if($compareEnabled)
                    @auth
                        <form method="POST" action="{{ route('account.compare.store', $product) }}" class="shrink-0">
                            @csrf
                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-700 hover:bg-slate-100 sm:h-10 sm:w-10" aria-label="Add {{ $siteBrand->text($product->name) }} to compare">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3v18M5 6h14M5 6l-3 7h6L5 6Zm14 0-3 7h6l-3-7ZM8 21h8"/></svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('account.login') }}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-700 hover:bg-slate-100 sm:h-10 sm:w-10" aria-label="Sign in to compare {{ $siteBrand->text($product->name) }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3v18M5 6h14M5 6l-3 7h6L5 6Zm14 0-3 7h6l-3-7ZM8 21h8"/></svg>
                        </a>
                    @endauth
                @endif
                @auth
                    <form method="POST" action="{{ route('account.wishlist.store', $product) }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-700 hover:bg-rose-50 hover:text-rose-600 sm:h-10 sm:w-10" aria-label="Add {{ $siteBrand->text($product->name) }} to wishlist">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 8.8c0 5.2-8.8 10.2-8.8 10.2S3.2 14 3.2 8.8A4.8 4.8 0 0 1 12 6.2a4.8 4.8 0 0 1 8.8 2.6Z"/></svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('account.login') }}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-700 hover:bg-rose-50 hover:text-rose-600 sm:h-10 sm:w-10" aria-label="Sign in to wishlist {{ $siteBrand->text($product->name) }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 8.8c0 5.2-8.8 10.2-8.8 10.2S3.2 14 3.2 8.8A4.8 4.8 0 0 1 12 6.2a4.8 4.8 0 0 1 8.8 2.6Z"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </aside>
</section>
@endsection
