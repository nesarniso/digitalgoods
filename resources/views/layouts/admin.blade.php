<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | {{ config('app.name') }}</title>
    @if(file_exists(public_path('build/manifest.json'))) @vite(['resources/css/app.css', 'resources/js/app.js']) @endif
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-100 text-slate-900 antialiased" style="--brand-primary: {{ $systemSettings['brand_color'] ?? '#f97316' }};">
    <div class="flex min-h-screen">
        <aside data-admin-drawer class="fixed inset-y-0 left-0 z-[70] w-72 -translate-x-full border-r border-slate-200 bg-white text-slate-900 shadow-2xl transition-transform duration-200 lg:static lg:z-auto lg:block lg:w-72 lg:translate-x-0 lg:shadow-none">
            <div class="sticky top-0 flex h-screen flex-col">
                <div class="border-b border-slate-200 px-6 py-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-xl font-black text-slate-900">
                        @if (!empty($systemSettings['logo_url']))
                            <img src="{{ $systemSettings['logo_url'] }}" alt="{{ $systemSettings['site_name'] }}" class="h-10 max-w-[170px] object-contain">
                        @else
                            {{ $systemSettings['site_name'] ?? config('app.name') }}
                        @endif
                    </a>
                    <p class="mt-1 text-xs uppercase tracking-widest text-slate-500">Admin control center</p>
                </div>
                <nav data-admin-sidebar class="flex-1 overflow-y-auto px-4 py-6 text-sm">
                    <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-500">Overview</p>
                    <a href="{{ route('admin.dashboard') }}" class="mt-2 flex rounded-xl px-3 py-2.5 font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
                    <a href="{{ route('admin.contact-messages.index') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.contact-messages.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Inbox</a>
                    <a href="{{ route('admin.support-tickets.index') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.support-tickets.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Support tickets</a>

                    <p class="mt-7 px-3 text-xs font-bold uppercase tracking-widest text-slate-500">Store management</p>
                    <a href="{{ route('admin.catalog') }}" class="mt-2 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.catalog') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Products & catalog</a>
                    <a href="{{ route('admin.gallery') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.gallery*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Gallery</a>
                    <a href="{{ route('admin.orders') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.orders') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Orders</a>
                    <a href="{{ route('admin.reviews') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.reviews*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Product reviews</a>
                    <a href="{{ route('admin.digital-products') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.digital-products*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Digital products</a>
                    <a href="{{ route('admin.email.logs') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.email.logs') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Email logs</a>
                    <details class="mt-1 group" @if(request()->routeIs('admin.categories*')) open @endif>
                        <summary class="flex cursor-pointer list-none rounded-xl px-3 py-2.5 font-medium {{ request()->routeIs('admin.categories*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>Categories</span>
                            <span class="ml-auto transition-transform group-open:rotate-180">⌄</span>
                        </summary>
                        <div class="ml-4 mt-1 border-l border-slate-200 pl-3 text-xs">
                            <a href="{{ route('admin.categories') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.categories') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Category</a>
                            <a href="{{ route('admin.categories.subcategories') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.categories.subcategories') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Subcategory</a>
                            <a href="{{ route('admin.categories.sub-subcategories') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.categories.sub-subcategories') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Sub-subcategory</a>
                        </div>
                    </details>
                    <a href="{{ route('admin.brands') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.brands') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Brands</a>
                    <a href="{{ route('admin.attributes') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.attributes') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Attributes</a>
                    <a href="{{ route('admin.coupons') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.coupons') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Coupons & promotions</a>
                    <details class="mt-1 group" @if(request()->routeIs('admin.marketing.*')) open @endif>
                        <summary class="flex cursor-pointer list-none rounded-xl px-3 py-2.5 font-medium {{ request()->routeIs('admin.marketing.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}"><span>Marketing</span><span class="ml-auto transition-transform group-open:rotate-180">⌄</span></summary>
                        <div class="ml-4 mt-1 border-l border-slate-200 pl-3 text-xs">
                            <a href="{{ route('admin.marketing.campaigns') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.marketing.campaigns') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Campaigns</a>
                            <a href="{{ route('admin.marketing.contacts') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.marketing.contacts') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Contacts</a>
                        </div>
                    </details>
                    <details class="mt-1 group" @if(request()->routeIs('admin.entrepreneurs*', 'admin.withdrawals*')) open @endif>
                        <summary class="flex cursor-pointer list-none rounded-xl px-3 py-2.5 font-medium {{ request()->routeIs('admin.entrepreneurs*', 'admin.withdrawals*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>Entrepreneur Platform</span>
                            <span class="ml-auto transition-transform group-open:rotate-180">⌄</span>
                        </summary>
                        <div class="ml-4 mt-1 border-l border-slate-200 pl-3 text-xs">
                            <a href="{{ route('admin.entrepreneurs') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.entrepreneurs') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Entrepreneurs</a>
                            <a href="{{ route('admin.entrepreneurs.report') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.entrepreneurs.report') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Entrepreneur report</a>
                            <a href="{{ route('admin.entrepreneurs.products') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.entrepreneurs.products') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Product assignments</a>
                            <a href="{{ route('admin.withdrawals') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.withdrawals*') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Withdrawals</a>
                            <a href="{{ route('admin.support-tickets.index') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.support-tickets.*') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Entrepreneur support</a>
                            <a href="{{ route('admin.notifications.index') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.notifications.*') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Platform notifications</a>
                            <a href="{{ route('admin.marketing-officers') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.marketing-officers*') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Marketing officers</a>
                            <a href="{{ route('admin.boosting-team') }}" class="block rounded-lg px-2 py-1.5 {{ request()->routeIs('admin.boosting-team*') ? 'font-semibold text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Boosting team</a>
                        </div>
                    </details>

                    <p class="mt-7 px-3 text-xs font-bold uppercase tracking-widest text-slate-500">Operations</p>
                    <a href="{{ route('admin.inventory') }}" class="mt-2 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.inventory') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Inventory</a>
                    <a href="{{ route('admin.warehouses') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.warehouses') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Warehouses</a>
                    <a href="{{ route('admin.shipping') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.shipping') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Shipping</a>
                    <a href="{{ route('admin.couriers') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.couriers') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Courier management</a>
                    <a href="{{ route('admin.checkout-abandonment') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.checkout-abandonment') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Checkout abandonment</a>
                    <a href="{{ route('admin.payments') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.payments') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Payments</a>
                    <a href="{{ route('admin.restocks') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.restocks') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Restock requests</a>

                    <p class="mt-7 px-3 text-xs font-bold uppercase tracking-widest text-slate-500">People & insights</p>
                    <a href="{{ route('admin.customers') }}" class="mt-2 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.customers') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Customers</a>
                    <a href="{{ route('admin.entrepreneurs') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.entrepreneurs*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Entrepreneurs</a>
                    <a href="{{ route('admin.entrepreneurs.report') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.entrepreneurs.report') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Entrepreneur Report</a>
                    <a href="{{ route('admin.withdrawals') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.withdrawals*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Withdrawals</a>
                    <a href="{{ route('admin.employees') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.employees') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Employees & roles</a>
                    <a href="{{ route('admin.reports') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.reports') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Reports</a>
                    <a href="{{ route('admin.notifications.index') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.notifications.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Push notifications</a>
                    <a href="{{ route('admin.seo') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.seo') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">SEO & sitemap</a>
                    <a href="{{ route('admin.audit') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.audit') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Audit log</a>

                    <p class="mt-7 px-3 text-xs font-bold uppercase tracking-widest text-slate-500">System settings</p>
                    <a href="{{ route('admin.settings') }}" class="mt-2 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings') && request()->get('section') !== 'hero' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">General settings</a>
                    <a href="{{ route('admin.settings', ['section' => 'hero']) }}#hero-customization" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings') && request()->get('section') === 'hero' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Hero customization</a>
                    <a href="{{ route('admin.settings', ['section' => 'home']) }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings') && request()->get('section') === 'home' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Home page</a>
                    <a href="{{ route('admin.settings.header-menu') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings.header-menu') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Header menu</a>
                    <a href="{{ route('admin.settings.payments') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings.payments') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Payment methods</a>
                    <a href="{{ route('admin.settings.most-selling') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings.most-selling') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Most Selling</a>
                    <a href="{{ route('admin.settings.recommended-product') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings.recommended-product') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Recommended product</a>
                    <a href="{{ route('admin.settings.footer') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings.footer') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Footer settings</a>
                    <a href="{{ route('admin.settings.contact') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings.contact*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Contact page</a>
                    <a href="{{ route('admin.email.smtp') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.email.smtp*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">SMTP server</a>
                    <a href="{{ route('admin.email.templates') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.email.templates*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Email templates</a>
                    <a href="{{ route('admin.settings.pages') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings.pages*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Website pages</a>
                    <a href="{{ route('admin.password') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.password*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Change password</a>
                </nav>
                <div class="border-t border-slate-200 p-4">
                    <a href="{{ route('home') }}" class="block rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-100">View storefront</a>
                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-1">@csrf<button class="w-full rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 hover:bg-red-50 hover:text-red-600">Sign out</button></form>
                </div>
            </div>
        </aside>
        <div class="min-w-0 flex-1">
            <header class="border-b border-slate-200 bg-white px-4 py-3 sm:px-6 lg:px-10">
                <div class="mx-auto flex max-w-7xl items-center justify-between">
                    <div class="flex items-center gap-3"><button type="button" data-admin-menu class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 lg:hidden" aria-label="Open admin menu" aria-expanded="false"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button><div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Admin panel</p><p class="mt-1 text-sm text-slate-500">{{ auth()->user()->name }}</p></div></div>
                    <a href="{{ route('home') }}" class="text-sm font-semibold text-indigo-700">Storefront →</a>
                </div>
            </header>
            <main>@yield('content')</main>
        </div>
    </div>
    <div data-admin-overlay class="fixed inset-0 z-[60] hidden bg-slate-950/40 lg:hidden"></div>
    <script>
        (() => {
            const sidebar = document.querySelector('[data-admin-sidebar]');
            const drawer = document.querySelector('[data-admin-drawer]');
            const menuButton = document.querySelector('[data-admin-menu]');
            const overlay = document.querySelector('[data-admin-overlay]');
            const setDrawer = (open) => {
                if (!drawer || !menuButton || !overlay) return;
                drawer.classList.toggle('-translate-x-full', !open);
                drawer.classList.toggle('translate-x-0', open);
                overlay.classList.toggle('hidden', !open);
                menuButton.setAttribute('aria-expanded', String(open));
                document.body.classList.toggle('overflow-hidden', open);
            };
            menuButton?.addEventListener('click', () => setDrawer(drawer.classList.contains('-translate-x-full')));
            overlay?.addEventListener('click', () => setDrawer(false));
            drawer?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setDrawer(false)));
            if (!sidebar) return;

            const storageKey = 'admin-sidebar-scroll-top';
            const savedPosition = Number(sessionStorage.getItem(storageKey));
            if (Number.isFinite(savedPosition)) {
                sidebar.scrollTop = savedPosition;
            }

            document.querySelectorAll('[data-admin-sidebar] a').forEach((link) => {
                link.addEventListener('click', () => {
                    sessionStorage.setItem(storageKey, String(sidebar.scrollTop));
                });
            });

            window.addEventListener('pageshow', () => {
                const position = Number(sessionStorage.getItem(storageKey));
                if (Number.isFinite(position)) sidebar.scrollTop = position;
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
