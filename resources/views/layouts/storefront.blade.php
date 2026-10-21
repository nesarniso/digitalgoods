<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ $systemSettings['website_icon_url'] ?? asset('icons/icon.svg') }}">
    <meta name="description" content="@yield('meta_description', 'Shop quality products from '.config('app.name'))">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', config('app.name'))))">
    <meta property="og:description" content="@yield('meta_description', 'Shop quality products from '.config('app.name'))">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:site_name" content="{{ $systemSettings['site_name'] ?? config('app.name') }}">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
    @yield('structured_data')
    <title>@yield('title', config('app.name'))</title>
    @if(file_exists(public_path('build/manifest.json'))) @vite(['resources/css/app.css', 'resources/js/app.js']) @endif
</head>
<body data-authenticated="{{ auth()->check() ? '1' : '0' }}" class="storefront-shell min-h-screen overflow-x-hidden bg-[#f7f8fa] text-slate-900 antialiased" style="--brand-primary: {{ $systemSettings['brand_color'] ?? '#f97316' }};">
    <header data-storefront-header class="relative z-40 border-b border-slate-200 bg-white text-slate-900 shadow-sm">
        <div data-header-topbar class="mx-auto max-w-5xl px-4 py-1.5 text-[11px] text-slate-600 sm:px-6">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-slate-700">☎</span>
                    <span>{{ $systemSettings['phone'] ?? '' }}</span>
                </div>
            </div>
        </div>

        <div data-header-main class="mx-auto max-w-5xl px-4 pb-3 sm:px-6">
            <div class="flex flex-wrap items-center gap-3 sm:flex-nowrap sm:gap-5">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="max-w-[190px] truncate whitespace-nowrap text-lg font-black tracking-tight text-[var(--brand-primary)] sm:max-w-none sm:text-xl">
                        @if (!empty($systemSettings['logo_url']))
                            <img src="{{ $systemSettings['logo_url'] }}" alt="{{ $systemSettings['site_name'] }}" class="h-10 max-w-[150px] object-contain">
                        @else
                            {{ $systemSettings['site_name'] ?? config('app.name') }}
                        @endif
                    </a>
                </div>

                <form action="{{ route('shop') }}" class="order-3 flex w-full min-w-0 flex-1 items-center overflow-hidden rounded-md border border-slate-200 bg-white sm:order-none">
                    <label class="sr-only" for="site-search">Search products</label>
                    <input id="site-search" name="q" value="{{ request('q') }}" placeholder="Search for items..." class="w-full bg-transparent px-4 py-2.5 text-xs text-slate-700 placeholder:text-slate-400 outline-none">
                    <button class="flex h-9 w-11 items-center justify-center bg-[var(--brand-primary)] text-base font-bold text-white hover:opacity-90">⌕</button>
                </form>

                <div class="ml-auto flex shrink-0 items-center gap-2 text-xs text-slate-600 sm:gap-3">
                    @auth
                        <a href="{{ route('account.wishlist') }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-lg text-[var(--brand-primary)]" aria-label="Wishlist">♡</a>
                        <a href="{{ route('account.notifications') }}" class="relative flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-sm text-[var(--brand-primary)]" aria-label="Notifications">🔔@if(auth()->user()->unreadNotifications()->count())<span class="absolute -right-1 -top-1 rounded-full bg-red-600 px-1.5 text-[9px] font-bold text-white">{{ auth()->user()->unreadNotifications()->count() }}</span>@endif</a>
                    @endauth
                    <a href="{{ auth()->check() ? route('account.index') : route('account.login') }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[var(--brand-primary)]" style="border-radius: 9999px; background-color: #f1f5f9;" aria-label="Customer account">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4.5 21a7.5 7.5 0 0 1 15 0H4.5Z"></path>
                        </svg>
                    </a>
                    <a href="{{ route('cart') }}" class="flex items-center gap-1.5 sm:gap-2">
                        <span class="text-lg text-[var(--brand-primary)]">🛒</span>
                        <span class="hidden sm:inline">My cart<br><strong><span data-cart-count>0</span> item(s) · <span data-cart-total>৳0.00</span>⌄</strong></span>
                    </a>
                </div>
            </div>
        </div>

        <nav data-header-nav class="bg-[var(--brand-primary)] text-white">
            <div class="mx-auto flex max-w-5xl items-center gap-2 overflow-x-auto px-4 py-2 sm:gap-5 sm:px-6">
                <button type="button" data-category-drawer-toggle class="flex min-w-[145px] items-center justify-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-bold text-[var(--brand-primary)] shadow-sm sm:min-w-[180px] lg:pointer-events-none">
                    <span>▦</span>
                    <span>Categories</span>
                    <span class="ml-auto">⌄</span>
                </button>
                <div class="hidden items-center gap-7 text-xs font-medium md:flex">
                    <a href="{{ route('home') }}" class="hover:text-white/80">Home</a>
                    <a href="{{ route('shop') }}" class="hover:text-white/80">Brand</a>
                    <a href="{{ route('contact') }}" class="hover:text-white/80">Contact</a>
                </div>
                @guest
                    <div class="ml-auto hidden items-center gap-3 text-sm md:flex">
                        <a href="{{ route('account.login') }}" class="hover:text-white/80">Login</a>
                    </div>
                @endguest
            </div>
        </nav>
    </header>
    <div data-notice class="hidden border-b border-emerald-200 bg-emerald-50 px-6 py-3 text-center text-sm font-medium text-emerald-800"></div>
    @if (!request()->routeIs('home') || empty($homeSettings['hero_enabled']))
        @include('components.storefront.mobile-category-drawer')
    @endif
    <div data-quick-view-modal class="fixed inset-0 z-[90] hidden items-center justify-center overflow-y-auto bg-slate-950/55 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="quick-view-title">
        <div class="relative my-auto w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl">
            <button type="button" data-quick-view-close class="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-xl text-slate-500 shadow-sm hover:text-slate-900" aria-label="Close quick view">×</button>
            <div class="grid sm:grid-cols-2">
                <div class="flex aspect-square items-center justify-center bg-gradient-to-br from-indigo-100 to-slate-100 p-5">
                    <img data-quick-view-image src="" alt="" class="h-full w-full object-contain">
                </div>
                <div class="flex flex-col justify-center p-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Quick view</p>
                    <h2 id="quick-view-title" data-quick-view-name class="mt-2 text-2xl font-black text-slate-900"></h2>
                    <div class="mt-3 flex items-center gap-2 text-sm"><span data-quick-view-rating class="font-bold tracking-widest text-amber-500"></span><span data-quick-view-review-count class="text-slate-500"></span></div>
                    <p data-quick-view-price class="mt-4 text-xl font-black text-slate-900"></p>
                    <p data-quick-view-description class="mt-4 line-clamp-4 text-sm leading-6 text-slate-600"></p>
                    <div data-quick-view-variants class="mt-5 hidden rounded-xl border border-indigo-100 bg-indigo-50/50 p-3">
                        <p class="text-sm font-bold text-slate-800">Choose options</p>
                        <div data-quick-view-variant-fields class="mt-3 space-y-3"></div>
                        <p data-quick-view-variant-message class="mt-2 hidden text-xs font-semibold text-red-600">Please choose an available combination.</p>
                        <input type="hidden" data-selected-variant value="">
                    </div>
                    <div class="mt-5 flex items-center gap-3"><span class="text-sm font-bold text-slate-700">Quantity</span><div class="flex h-10 overflow-hidden rounded-xl border border-slate-200"><button type="button" data-quantity-decrease class="w-9 text-lg text-[var(--brand-primary)]">−</button><input data-product-quantity value="1" min="1" type="number" class="w-12 border-x border-slate-200 text-center outline-none"><button type="button" data-quantity-increase class="w-9 text-lg text-[var(--brand-primary)]">+</button></div></div>
                    <div class="mt-5 grid grid-cols-2 gap-2"><button type="button" data-add-to-cart class="rounded-xl border border-[var(--brand-primary)] px-3 py-3 text-sm font-bold text-[var(--brand-primary)] hover:bg-orange-50">Add to cart</button><button type="button" data-buy-now class="rounded-xl bg-[var(--brand-primary)] px-3 py-3 text-sm font-bold text-white hover:opacity-90">Buy now</button></div>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <button type="button" data-share-product data-share-title="" data-share-text="" data-share-url="" class="rounded-xl border border-indigo-200 px-3 py-3 text-sm font-bold text-indigo-700 hover:bg-indigo-50">Share</button>
                    </div>
                    <a data-quick-view-link href="#" class="mt-3 inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:border-[var(--brand-primary)] hover:text-[var(--brand-primary)]">View full product</a>
                </div>
            </div>
        </div>
    </div>
    <main class="pb-20 sm:pb-0">@yield('content')</main>
    @include('components.storefront.footer')
    @php
        $whatsappNumber = preg_replace('/\D+/', '', (string) ($systemSettings['whatsapp'] ?? ''));
        if (str_starts_with($whatsappNumber, '0')) {
            $whatsappNumber = '880'.ltrim($whatsappNumber, '0');
        }
    @endphp
    @if ($whatsappNumber !== '')
        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode('Hello, I need help with my order.') }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp" class="whatsapp-floating fixed z-[70] flex h-14 w-14 items-center justify-center rounded-full text-white transition hover:scale-105">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.6 4.1 1.6 5.8L.2 23.9l6.5-1.7a11.8 11.8 0 0 0 5.4 1.3h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.1-1.2-6-3.5-8.2Zm-8.4 18c-1.7 0-3.4-.5-4.8-1.3l-.3-.2-3.9 1 1-3.8-.2-.4a9.7 9.7 0 1 1 8.2 4.7Zm5.3-7.3c-.3-.2-1.7-.8-2-.9-.3-.1-.5-.2-.7.2-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1-1.7-.8-2.8-1.4-3.9-3.2-.3-.5.3-.5.8-1.6.1-.2 0-.4 0-.5l-.9-2.1c-.2-.5-.5-.4-.7-.4h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.1 3c.1.2 2 3.1 4.8 4.3 1.8.8 2.4.9 3.3.8.5-.1 1.7-.7 1.9-1.4.2-.7.2-1.3.1-1.4Z"/>
            </svg>
        </a>
    @endif
    <nav class="fixed inset-x-0 bottom-0 z-[65] border-t border-slate-200/80 bg-white/95 px-3 pb-[max(env(safe-area-inset-bottom),0.35rem)] pt-2 shadow-[0_-10px_30px_rgba(15,23,42,0.12)] backdrop-blur-xl sm:hidden" aria-label="Mobile navigation">
        <div class="mx-auto grid max-w-lg grid-cols-5 gap-1">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-bold transition {{ request()->routeIs('home') ? 'bg-orange-50 text-[var(--brand-primary)]' : 'text-slate-500 hover:bg-slate-50' }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-xl {{ request()->routeIs('home') ? 'bg-[var(--brand-primary)] text-white shadow-md shadow-orange-200' : 'bg-slate-100' }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Zm6 11v-6h6v6"/></svg></span><span>Home</span>
            </a>
            <a href="{{ auth()->check() ? route('account.wishlist') : route('account.login') }}" class="flex flex-col items-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-bold transition {{ request()->routeIs('account.wishlist') ? 'bg-orange-50 text-[var(--brand-primary)]' : 'text-slate-500 hover:bg-slate-50' }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-xl {{ request()->routeIs('account.wishlist') ? 'bg-[var(--brand-primary)] text-white shadow-md shadow-orange-200' : 'bg-slate-100' }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.8 8.8c0 5.2-8.8 10.2-8.8 10.2S3.2 14 3.2 8.8A4.8 4.8 0 0 1 12 6.2a4.8 4.8 0 0 1 8.8 2.6Z"/></svg></span><span>Wishlist</span>
            </a>
            <a href="{{ route('cart') }}" class="relative flex flex-col items-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-bold text-slate-500 transition hover:bg-slate-50">
                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-slate-100"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h2l2 12h10l3-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg></span><span>Cart</span><span data-mobile-cart-count class="absolute right-3 top-0 hidden min-w-4 rounded-full bg-red-600 px-1 text-center text-[9px] font-bold text-white"></span>
            </a>
            <button type="button" data-category-drawer-toggle class="mobile-bottom-categories flex flex-col items-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-bold text-slate-500 transition hover:bg-slate-50">
                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-slate-100"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg></span><span>Categories</span>
            </button>
        </div>
    </nav>
    <script>
        (() => {
            const whatsappButton = document.querySelector('.whatsapp-floating');
            if (whatsappButton) {
                document.documentElement.appendChild(whatsappButton);
            }
            const updateHeaderState = () => {
                document.body.classList.toggle('storefront-scrolled', window.scrollY > 80);
            };
            window.addEventListener('scroll', updateHeaderState, { passive: true });
            updateHeaderState();
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) window.location.reload();
            });
        })();
    </script>
</body>
</html>
