<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Entrepreneur Dashboard') | {{ $systemSettings['site_name'] ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased" style="--brand-primary: {{ $systemSettings['brand_color'] ?? '#f97316' }};">
    <div class="flex min-h-screen">
        <div data-entrepreneur-overlay class="fixed inset-0 z-40 hidden bg-slate-950/40 lg:hidden"></div>
        <aside data-entrepreneur-drawer class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white shadow-2xl transition-transform duration-200 lg:static lg:translate-x-0 lg:shadow-none">
            <div class="border-b border-slate-200 px-6 py-6">
                <a href="{{ route('entrepreneur.dashboard') }}" class="text-xl font-black text-[var(--brand-primary)]">{{ $systemSettings['site_name'] ?? config('app.name') }}</a>
                <p class="mt-1 text-xs font-bold uppercase tracking-widest text-slate-500">Entrepreneur workspace</p>
            </div>
            <nav class="flex-1 overflow-y-auto px-4 py-6 text-sm">
                <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-500">Overview</p>
                <a href="{{ route('entrepreneur.dashboard') }}" class="mt-2 flex rounded-xl px-3 py-2.5 font-semibold {{ request()->routeIs('entrepreneur.dashboard') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
                <a href="{{ route('entrepreneur.sales') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.sales') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">My Sales & Orders</a>
                <a href="{{ route('entrepreneur.reports') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.reports') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Sales Reports</a>
                <a href="{{ route('entrepreneur.notifications') }}" class="mt-1 flex items-center justify-between rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.notifications*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}"><span>Notifications</span>@if(auth()->user()->unreadNotifications()->count())<span class="rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ auth()->user()->unreadNotifications()->count() }}</span>@endif</a>
                <a href="{{ route('entrepreneur.customers') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.customers*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">My Customers</a>
                <a href="{{ route('entrepreneur.team') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.team') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">My Referral Team</a>

                <p class="mt-7 px-3 text-xs font-bold uppercase tracking-widest text-slate-500">Selling tools</p>
                <a href="{{ route('entrepreneur.marketplace') }}" class="mt-2 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.marketplace*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Marketplace</a>
                <a href="{{ route('entrepreneur.orders.create') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.orders.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Create Customer Order</a>
                <a href="{{ route('entrepreneur.products') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.products') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">My Products & Links</a>
                <a href="{{ route('entrepreneur.marketing') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.marketing') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Marketing Center</a>

                <p class="mt-7 px-3 text-xs font-bold uppercase tracking-widest text-slate-500">Finance</p>
                <a href="{{ route('entrepreneur.finance') }}" class="mt-2 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.finance') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Wallet Ledger</a>
                <a href="{{ route('entrepreneur.withdrawals') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.withdrawals*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Withdraw Profit</a>
                <a href="{{ route('entrepreneur.support') }}" class="mt-1 flex rounded-xl px-3 py-2.5 {{ request()->routeIs('entrepreneur.support*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-slate-700 hover:bg-slate-100' }}">Training & Support</a>
            </nav>
            <div class="border-t border-slate-200 p-4">
                <a href="{{ route('entrepreneur.profile') }}" class="flex rounded-xl px-3 py-2.5 text-sm {{ request()->routeIs('entrepreneur.profile*', 'account.entrepreneur.password.update') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">Profile & Security</a>
                <a href="{{ route('home') }}" class="flex rounded-xl px-3 py-2.5 text-sm text-slate-600 hover:bg-slate-100">← View Storefront</a>
                <form method="POST" action="{{ route('entrepreneur.logout') }}" class="mt-1">@csrf<button class="w-full rounded-xl px-3 py-2.5 text-left text-sm text-slate-600 hover:bg-slate-100">Sign out</button></form>
            </div>
        </aside>
        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 shadow-sm backdrop-blur sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" data-entrepreneur-menu class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold lg:hidden">☰ Menu</button>
                    <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">eflix platform</p><p class="font-semibold">@yield('header_title', 'Entrepreneur Dashboard')</p></div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">View Website</a>
                    <a href="{{ route('entrepreneur.dashboard') }}" class="rounded-xl bg-[var(--brand-primary)] px-3 py-2 text-xs font-bold text-white">My Dashboard</a>
                </div>
            </header>
            <main>@yield('content')</main>
        </div>
    </div>
    <script>
        const drawer = document.querySelector('[data-entrepreneur-drawer]');
        const overlay = document.querySelector('[data-entrepreneur-overlay]');
        const toggleDrawer = (open) => {
            drawer?.classList.toggle('-translate-x-full', !open);
            overlay?.classList.toggle('hidden', !open);
        };
        document.querySelector('[data-entrepreneur-menu]')?.addEventListener('click', () => toggleDrawer(true));
        overlay?.addEventListener('click', () => toggleDrawer(false));
        drawer?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => toggleDrawer(false)));
    </script>
</body>
</html>
