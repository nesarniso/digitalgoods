@extends('layouts.storefront')

@section('title', ($systemSettings['site_name'] ?? config('app.name')).(!empty($homeSettings['slogan']) ? ' | '.$homeSettings['slogan'] : ''))
@section('meta_description', 'eflix-এ quality products, transparent pricing এবং dependable delivery পান।')

@section('content')
    @if ($homeSettings['hero_enabled'])
        <section class="mx-auto max-w-5xl px-4 py-3 sm:px-6">
            <div data-category-layout class="grid gap-2 lg:grid-rows-[440px] lg:grid-cols-[260px_1fr]">
                <aside data-category-drawer class="fixed inset-y-0 left-0 z-[70] w-80 max-w-[85vw] -translate-x-full overflow-y-auto rounded-r-2xl border border-slate-100 bg-white px-4 py-5 shadow-2xl transition-transform duration-200 lg:relative lg:inset-auto lg:z-20 lg:block lg:h-[440px] lg:w-auto lg:max-w-none lg:translate-x-0 lg:overflow-hidden lg:rounded-xl lg:py-2.5 lg:shadow-sm lg:ring-1 lg:ring-slate-200/80">
                    <div class="flex h-full flex-col">
                        <div class="mb-1 flex items-center justify-between border-b border-slate-100 pb-2 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500">
                            <span>Shop by category</span>
                            <button type="button" data-category-drawer-close class="rounded-lg px-2 py-1 text-lg text-slate-500 lg:hidden" aria-label="Close categories">×</button>
                            <span class="hidden lg:inline">›</span>
                        </div>
                        <ul class="min-h-0 flex-1 overflow-hidden text-[13px] font-medium text-slate-700">
                            @forelse ($categories as $category)
                                <li class="storefront-category-item relative border-b border-slate-100/80 last:border-b-0 {{ $loop->index >= 12 ? 'category-extra hidden' : '' }}">
                                    <div class="flex w-full items-center justify-between rounded-xl text-left transition-colors hover:bg-orange-50">
                                        <a href="{{ route('shop', ['category' => $category->slug]) }}" class="flex min-w-0 flex-1 items-center gap-2 px-2 py-1">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-xs font-bold text-[var(--brand-primary)]">
                                                @if($category->icon_path)
                                                    <img src="{{ asset('storage/media/'.$category->icon_path) }}" alt="" class="h-full w-full object-cover">
                                                @else
                                                    ✦
                                                @endif
                                            </span>
                                            <span class="min-w-0 break-words text-[11px] leading-4">{{ $category->name }}</span>
                                        </a>
                                        @if ($category->children->isNotEmpty())
                                            <button type="button" class="mr-2 rounded-lg px-2 py-1 text-lg leading-none text-slate-400 transition hover:bg-white hover:text-[var(--brand-primary)]" data-category-toggle aria-label="Show {{ $category->name }} subcategories">›</button>
                                        @endif
                                    </div>
                                    @if ($category->children->isNotEmpty())
                                        <div class="storefront-category-flyout absolute left-full top-0 hidden min-w-[190px] rounded-sm border border-slate-100 bg-white p-3 text-slate-700 shadow-xl" data-category-panel>
                                            <a href="{{ route('shop', ['category' => $category->slug]) }}" class="mb-2 block border-b border-slate-100 pb-2 font-semibold hover:text-[var(--brand-primary)]">All {{ $category->name }}</a>
                                            <ul class="space-y-2 text-sm">
                                            @foreach ($category->children as $child)
                                                <li><a href="{{ route('shop', ['category' => $child->slug]) }}" class="hover:text-[var(--brand-primary)]">{{ $child->name }}</a></li>
                                            @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </li>
                            @empty
                                <li class="py-2 text-slate-500">No categories yet.</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('shop') }}" class="mt-1 inline-flex w-full items-center justify-center rounded-xl bg-slate-50 py-1.5 text-[11px] font-bold text-[var(--brand-primary)] transition hover:bg-orange-50">View all categories <span class="ml-1 text-base leading-none">›</span></a>
                    </div>
                </aside>
                <div data-category-drawer-overlay class="fixed inset-0 z-[60] hidden bg-slate-950/40 lg:hidden"></div>

                <div class="relative h-auto min-h-[430px] overflow-hidden rounded-2xl bg-[radial-gradient(circle_at_top_left,_#fffaf6,_#f8e4d0_45%,_#f5efe9_100%)] shadow-[0_24px_70px_rgba(15,23,42,0.10)] ring-1 ring-orange-100 sm:min-h-[460px] lg:h-[440px] lg:min-h-0" style="background-color: {{ $homeSettings['hero_background_color'] ?? '#f8e4d0' }};">
                    @if ($homeSettings['slider_image_1'])
                        <div class="relative isolate h-full min-h-[430px] overflow-hidden sm:min-h-[460px] lg:min-h-0">
                            <img src="{{ $homeSettings['slider_image_1'] }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
                            @if ($homeSettings['hero_overlay_enabled'] ?? true)
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/55 to-slate-950/10"></div>
                            @endif
                            <div class="relative z-10 flex h-full min-h-[430px] max-w-2xl flex-col justify-center px-4 py-8 text-white sm:min-h-[460px] sm:px-8 sm:py-10 lg:min-h-0 lg:px-10">
                                <div class="mb-3 inline-flex w-fit max-w-full items-center gap-2 rounded-full border border-white/25 bg-white/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-orange-100 backdrop-blur sm:mb-4 sm:px-3 sm:text-[10px] sm:tracking-[0.18em]">
                                    <span class="text-orange-300">★</span> {{ $homeSettings['hero_badge'] ?? 'eflix Store' }}
                                </div>
                                <h1 class="max-w-xl break-words text-2xl font-black leading-tight tracking-[-0.04em] sm:text-4xl lg:text-5xl">{{ $homeSettings['hero_title'] }}</h1>
                                <p class="mt-3 max-w-xl break-words text-xs leading-5 text-white/80 sm:mt-4 sm:text-base sm:leading-7">{{ $homeSettings['hero_subtitle'] }}</p>
                                <div class="mt-5 flex flex-col gap-2.5 sm:mt-6 sm:flex-row sm:flex-wrap sm:gap-3">
                                    @if ($homeSettings['hero_primary_button_enabled'] ?? true)
                                        <a href="{{ Str::startsWith($homeSettings['hero_primary_button_url'] ?? '/shop', ['http://', 'https://']) ? $homeSettings['hero_primary_button_url'] : url($homeSettings['hero_primary_button_url'] ?? '/shop') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[var(--brand-primary)] px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-orange-950/20 hover:opacity-90 sm:w-auto sm:px-5 sm:py-3 sm:text-sm">{{ $homeSettings['hero_primary_button_label'] ?? $homeSettings['hero_button_label'] }}</a>
                                    @endif
                                </div>
                                <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-xs font-semibold text-white/75">
                                    <span>✓ No stock required</span><span>✓ Track every sale</span><span>✓ Profit wallet</span>
                                </div>
                            </div>
                        </div>
                    @else
                    <div class="grid min-h-[0] h-full gap-4 px-4 py-6 sm:px-6 sm:py-7 md:grid-cols-[1.1fr_1fr] md:px-8">
                        <div class="flex flex-col justify-center">
                            <div class="mb-3 inline-flex w-fit max-w-full items-center gap-2 rounded-full border border-orange-200 bg-white/80 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-slate-900 shadow-sm sm:mb-4 sm:px-3 sm:text-[11px] sm:tracking-[0.22em]">
                                <span class="text-[var(--brand-primary)]">★</span> {{ $homeSettings['hero_badge'] ?? 'eflix Store' }}
                            </div>
                            <h1 class="max-w-xl break-words text-3xl font-black leading-[0.98] tracking-[-0.06em] text-slate-950 sm:text-4xl md:text-5xl xl:text-6xl">
                                {{ $homeSettings['hero_title'] ?? 'আপনার উদ্যোগ, আমাদের প্ল্যাটফর্ম' }}
                            </h1>
                            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-700 sm:mt-5 sm:text-base md:text-lg md:leading-8">
                                {{ $homeSettings['hero_subtitle'] ?? 'Quality products, transparent pricing এবং dependable delivery—সবকিছু এক trusted store-এ।' }}
                            </p>

                            <div class="mt-4 flex flex-wrap items-center gap-2 sm:mt-6 sm:gap-3">
                                @if ($homeSettings['hero_primary_button_enabled'] ?? true)
                                    <a href="{{ Str::startsWith($homeSettings['hero_primary_button_url'] ?? '/shop', ['http://', 'https://']) ? $homeSettings['hero_primary_button_url'] : url($homeSettings['hero_primary_button_url'] ?? '/shop') }}" class="inline-flex items-center justify-center rounded-xl bg-[var(--brand-primary)] px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-orange-200 hover:opacity-90 sm:px-5 sm:py-3 sm:text-sm">
                                        {{ $homeSettings['hero_primary_button_label'] ?? $homeSettings['hero_button_label'] }}
                                    </a>
                                @endif
                            </div>

                            <div class="mt-5 grid max-w-md gap-2 sm:mt-8 sm:gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-white/80 px-2 py-2 text-center ring-1 ring-slate-200 sm:px-3 sm:py-3">
                                    <div class="text-base font-black text-slate-900 sm:text-xl">Fast</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-500">Delivery</div>
                                </div>
                                <div class="rounded-2xl bg-white/80 px-2 py-2 text-center ring-1 ring-slate-200 sm:px-3 sm:py-3">
                                    <div class="text-base font-black text-slate-900 sm:text-xl">Secure</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-500">Payment</div>
                                </div>
                                <div class="rounded-2xl bg-white/80 px-2 py-2 text-center ring-1 ring-slate-200 sm:px-3 sm:py-3">
                                    <div class="text-base font-black text-slate-900 sm:text-xl">Easy</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-500">Returns</div>
                                </div>
                            </div>
                        </div>

                        <div class="relative hidden min-h-[360px] items-center justify-center md:flex">
                            <div class="absolute inset-x-8 bottom-4 h-16 rounded-full bg-white/70 blur-2xl"></div>
                            <div class="absolute right-2 top-4 flex h-24 w-24 items-center justify-center rounded-full bg-slate-950 text-center text-[10px] font-black leading-tight text-white shadow-xl shadow-slate-900/20">
                                RPC<br><span class="text-xl text-orange-300">PRO</span><br>PLATFORM
                            </div>

                            @if ($homeSettings['slider_image_1'])
                                <div class="relative z-10 flex h-[310px] w-full max-w-[410px] items-center justify-center overflow-hidden rounded-[30px] bg-white/40 shadow-[0_30px_60px_rgba(15,23,42,0.10)] ring-1 ring-slate-200">
                                    <img src="{{ $homeSettings['slider_image_1'] }}" alt="Featured promotion" class="h-full w-full object-contain">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/10 via-transparent to-white/10"></div>
                                </div>
                            @else
                                <div class="relative z-10 flex h-[310px] w-full max-w-[410px] items-end justify-center">
                                    <div class="absolute bottom-0 h-20 w-[78%] rounded-full bg-white/80 shadow-[0_30px_45px_rgba(15,23,42,0.08)] ring-1 ring-slate-200"></div>

                                    <div class="absolute bottom-16 left-[10%] h-28 w-24 rotate-[-12deg] rounded-[28px] bg-gradient-to-b from-[#f8f8f8] to-[#ecdec7] shadow-xl ring-1 ring-slate-200"></div>
                                    <div class="absolute bottom-20 right-[12%] h-32 w-28 rotate-[10deg] rounded-[26px] bg-gradient-to-b from-[#f4d7d5] to-[#f4d4ae] shadow-xl ring-1 ring-slate-200"></div>
                                    <div class="absolute bottom-24 left-[32%] h-12 w-20 rounded-[16px] bg-white/90 shadow-lg ring-1 ring-slate-200"></div>
                                    <div class="absolute bottom-24 right-[26%] h-16 w-20 rounded-[18px] bg-[#ffefd7] shadow-lg ring-1 ring-slate-200"></div>

                                    <div class="absolute bottom-14 left-[27%] h-28 w-28 rounded-[46%] bg-gradient-to-b from-[#f6f2ee] to-[#ead8c5] shadow-[0_18px_35px_rgba(15,23,42,0.12)] ring-1 ring-slate-200"></div>
                                    <div class="absolute bottom-14 left-[41%] h-24 w-24 rounded-[38%] bg-gradient-to-b from-[#f7f5f2] to-[#e1d1be] shadow-[0_18px_35px_rgba(15,23,42,0.12)] ring-1 ring-slate-200"></div>
                                    <div class="absolute bottom-12 left-[52%] h-20 w-16 rounded-[18px] bg-[#f5e8cf] shadow-lg ring-1 ring-slate-200"></div>
                                    <div class="absolute bottom-16 right-[18%] h-32 w-24 rounded-[26px] bg-gradient-to-b from-[#ecf0f4] to-[#d8dfe8] shadow-xl ring-1 ring-slate-200"></div>
                                    <div class="absolute bottom-5 left-[17%] h-12 w-20 rounded-[14px] bg-[#f7dcda] shadow-lg ring-1 ring-slate-200"></div>
                                    <div class="absolute bottom-5 right-[26%] h-12 w-16 rounded-[14px] bg-[#fff8dc] shadow-lg ring-1 ring-slate-200"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-5xl px-4 py-4 sm:px-6">
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-[0_14px_40px_rgba(15,23,42,0.08)]">
            <div class="pointer-events-none absolute -right-16 -top-20 h-40 w-40 rounded-full bg-orange-100/60 blur-3xl"></div>
            <div class="relative grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            @foreach ([
                ['value' => $homeStats['products'], 'label' => 'Products available', 'tone' => 'text-[var(--brand-primary)]', 'icon' => '✦'],
                ['value' => $homeStats['orders'], 'label' => 'Successful orders', 'tone' => 'text-emerald-600', 'icon' => '✓'],
                ['value' => $homeStats['categories'], 'label' => 'Product categories', 'tone' => 'text-slate-900', 'icon' => '⌘'],
            ] as $stat)
                <div class="group flex items-center gap-3 px-5 py-4 text-left transition hover:bg-orange-50/40 sm:justify-center sm:py-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-lg font-black {{ $stat['tone'] }} shadow-sm ring-1 ring-slate-100 transition group-hover:scale-105">{{ $stat['icon'] }}</span>
                    <div>
                        <p class="text-2xl font-black leading-none tracking-tight {{ $stat['tone'] }}">{{ number_format($stat['value']) }}{{ in_array($stat['label'], ['Products available', 'Successful orders'], true) ? '+' : '' }}</p>
                        <p class="mt-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500 sm:text-[11px]">{{ $stat['label'] }}</p>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
    </section>

    @if ($homeSettings['category_section_enabled'])
        <section class="mx-auto max-w-5xl px-4 py-5 sm:px-6">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-end justify-between gap-4 pb-4">
                <div><p class="text-[10px] font-bold uppercase tracking-widest text-[var(--brand-primary)]">Start shopping</p><h2 class="mt-1 text-lg font-black text-slate-800">Shop by category</h2></div>
                <a href="{{ route('shop') }}" class="text-xs font-semibold text-[var(--brand-primary)] hover:underline">View All ›</a>
            </div>
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                @foreach ($categoryTiles as $category)
                    @if ($category)
                    <a href="{{ route('shop', ['category' => $category->slug]) }}" class="group rounded-xl p-2 text-center transition hover:-translate-y-0.5 hover:bg-orange-50/50">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-slate-50 text-2xl shadow-inner ring-1 ring-slate-100 transition group-hover:scale-105 group-hover:ring-[var(--brand-primary)]">
                            @if($category->icon_path)
                                <img src="{{ asset('storage/media/'.$category->icon_path) }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                            @else
                                ✦
                            @endif
                        </div>
                        <h3 class="mt-2 truncate text-[11px] font-bold text-slate-800">{{ $category->name }}</h3>
                    </a>
                    @else
                        <div class="h-[100px] rounded-xl p-2" aria-hidden="true"></div>
                    @endif
                @endforeach
            </div>
            </div>
        </section>
    @endif

    @if ($mostSellingProducts->isNotEmpty())
        <section class="mx-auto max-w-5xl px-4 py-5 sm:px-6" data-category-carousel>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-4 pb-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-[var(--brand-primary)]">Customer favorites</p>
                        <h2 class="mt-1 text-lg font-black text-slate-800">Most Selling</h2>
                    </div>
                    <div class="flex overflow-hidden rounded-lg border border-slate-200">
                        <button type="button" data-carousel-prev aria-label="Previous most selling products" class="px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">←</button>
                        <button type="button" data-carousel-next aria-label="Next most selling products" class="border-l border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">→</button>
                    </div>
                </div>
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-out" data-carousel-track>
                        @foreach ($mostSellingProducts->chunk(5) as $products)
                            <div class="grid min-w-full grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                                @foreach ($products as $product)
                                    @include('storefront._product-card', ['product' => $product])
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($homeSettings['featured_section_enabled'])
        <section class="mx-auto max-w-5xl px-4 py-5 sm:px-6">
            <div class="grid gap-5 rounded-2xl bg-slate-50 p-3 sm:p-5 lg:grid-cols-[245px_minmax(0,1fr)]">
                <aside class="rounded-xl border border-orange-200 bg-orange-50/50 p-4">
                    <p class="text-center text-xs font-black uppercase tracking-wide text-[var(--brand-primary)]">Recommended product</p>
                    @if($recommendedProduct)
                        <a href="{{ route('products.show', $recommendedProduct->slug) }}" class="mt-5 block overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="flex aspect-square items-center justify-center bg-white">
                                @if($recommendedProduct->images->first())
                                    <img src="{{ $recommendedProduct->images->first()->url }}" alt="{{ $recommendedProduct->name }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-5xl font-black text-orange-100">{{ Str::upper(Str::substr($recommendedProduct->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="p-4 text-center"><h3 class="line-clamp-2 min-h-10 text-sm font-bold text-slate-800">{{ $recommendedProduct->name }}</h3><p class="mt-2 text-lg font-black text-slate-900">৳{{ number_format((float) $recommendedProduct->effective_price, 2) }}</p><span class="mt-4 inline-flex rounded-lg bg-[var(--brand-primary)] px-5 py-2.5 text-xs font-black text-white">BUY NOW</span></div>
                        </a>
                    @else
                        <div class="mt-5 flex min-h-72 items-center justify-center rounded-lg bg-white p-5 text-center text-sm text-slate-500">Mark a published product as Featured to show it here.</div>
                    @endif
                </aside>
                <div class="min-w-0">
                <div class="flex items-end justify-between pb-5">
                    <div><p class="text-[10px] font-bold uppercase tracking-widest text-[var(--brand-primary)]">Fresh arrivals</p><h2 class="mt-1 text-xl font-black text-slate-800">Latest Products</h2></div>
                    <a href="{{ route('shop') }}" class="text-xs font-semibold text-[var(--brand-primary)] hover:underline">View All <span class="ml-1 text-lg leading-none">›</span></a>
                </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($latestProducts as $product)
                    @include('storefront._product-card', ['product' => $product])
                @empty
                    <p class="col-span-full rounded-2xl bg-white p-8 text-slate-500 ring-1 ring-slate-200">Latest products will appear here soon.</p>
                @endforelse
            </div>
                </div>
            </div>
        </section>
    @endif

    @foreach ($featuredCategories as $featuredCategory)
        <section class="mx-auto max-w-5xl px-4 py-5 sm:px-6" data-category-carousel>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-4 pb-3">
                    <div>
                        <h2 class="text-lg font-black text-slate-800">{{ $featuredCategory->name }}</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex overflow-hidden rounded-lg border border-slate-200">
                            <button type="button" data-carousel-prev aria-label="Previous products" class="px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">←</button>
                            <button type="button" data-carousel-next aria-label="Next products" class="border-l border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">→</button>
                        </div>
                        <a href="{{ route('shop', ['category' => $featuredCategory->slug]) }}" class="text-[10px] font-semibold text-[var(--brand-primary)]">View All ›</a>
                    </div>
                </div>
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-out" data-carousel-track>
                        @foreach ($featuredCategory->products->take(10)->chunk(5) as $products)
                            <div class="grid min-w-full grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                                @foreach ($products as $product)
                                    @include('storefront._product-card', ['product' => $product])
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    <section class="mx-auto max-w-5xl px-4 py-5 sm:px-6">
        <div class="grid gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex gap-3 bg-white p-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-50 text-lg text-[var(--brand-primary)]">🚚</span><div><h2 class="text-sm font-bold">Fast delivery</h2><p class="mt-1 text-xs text-slate-500">Reliable delivery to your door.</p></div></div>
            <div class="flex gap-3 bg-white p-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-50 text-lg text-[var(--brand-primary)]">↺</span><div><h2 class="text-sm font-bold">Easy returns</h2><p class="mt-1 text-xs text-slate-500">Simple support when you need it.</p></div></div>
            <div class="flex gap-3 bg-white p-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-50 text-lg text-[var(--brand-primary)]">▣</span><div><h2 class="text-sm font-bold">Secure payment</h2><p class="mt-1 text-xs text-slate-500">Your payment details stay protected.</p></div></div>
            <div class="flex gap-3 bg-white p-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-50 text-lg text-[var(--brand-primary)]">✓</span><div><h2 class="text-sm font-bold">Quality checked</h2><p class="mt-1 text-xs text-slate-500">Products selected with care.</p></div></div>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-5 sm:px-6">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[var(--brand-primary)]">{{ $homeSettings['faq_eyebrow'] }}</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $homeSettings['faq_title'] }}</h2>
                <div class="mt-5 divide-y divide-slate-100">
                    @foreach($homeSettings['faq_items'] as $faq)
                        <details class="py-3">
                            <summary class="cursor-pointer text-sm font-bold text-slate-800">{{ $faq['title'] }}</summary>
                            <p class="mt-2 text-xs leading-5 text-slate-600">{{ $faq['text'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

@endsection
