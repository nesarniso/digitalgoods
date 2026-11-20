@extends('layouts.storefront')
@section('title', 'My account | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6"><div class="grid items-start gap-8 lg:grid-cols-[220px_1fr]">
    <aside class="h-fit rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200 lg:sticky lg:top-6">
        <div class="border-b border-slate-100 px-3 pb-4"><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">My account</p><a href="{{ route('account.profile') }}" class="mt-2 block truncate font-black text-slate-900 hover:text-indigo-700">{{ auth()->user()->name }}</a></div>
        <nav class="mt-3 space-y-1 text-sm font-semibold" aria-label="Customer account navigation">
            @php($accountLinks = [
                ['account.index', 'Dashboard', 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z'],
                ['account.index', 'My orders', 'M4 5h16v14H4zM8 9h8M8 13h5'],
                ['account.index', 'Digital downloads', 'M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3'],
                ['account.wishlist', 'Wishlist', 'M12 20S4 15.5 4 9.5A4.5 4.5 0 0 1 12 7a4.5 4.5 0 0 1 8 2.5C20 15.5 12 20 12 20Z'],
                ['account.compare', 'Compare products', 'M6 3v15m0 0-3-3m3 3 3-3M18 21V6m0 0-3 3m3-3 3 3'],
                ['account.coupons', 'Coupons', 'M4 7a2 2 0 0 0 0 4v2a2 2 0 0 0 0 4h16v-4a2 2 0 0 0 0-4V7H4Z'],
                ['account.index', 'Wallet & loyalty', 'M5 7h14v12H5zM5 10H3V5h14a2 2 0 0 1 2 2v2M15 13h2'],
                ['account.index', 'Returns & refunds', 'M4 12a8 8 0 1 0 2.3-5.6M4 5v5h5'],
                ['account.notifications', 'Notifications', 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4'],
                ['account.support', 'Help & support', 'M4 5h16v12H8l-4 4V5Zm4 5h8m-8 3h5'],
                ['account.index', 'Saved addresses', 'M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
                ['account.profile', 'Profile & security', 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0'],
            ])
            @foreach($accountLinks as [$routeName, $label, $path])
                <a href="{{ route($routeName) }}{{ $label === 'My orders' || $label === 'Saved addresses' ? '#account-orders' : ($label === 'Digital downloads' ? '#account-downloads' : ($label === 'Wallet & loyalty' ? '#account-wallet' : '')) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ ($routeName !== 'account.index' && request()->routeIs($routeName)) || ($routeName === 'account.index' && request()->routeIs('account.index') && $label === 'Dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $path }}"/></svg><span>{{ $label }}</span>
                </a>
            @endforeach
            <form method="POST" action="{{ route('account.logout') }}" class="border-t border-slate-100 pt-2">@csrf<button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-red-600 hover:bg-red-50"><svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5"/></svg><span>Sign out</span></button></form>
        </nav>
    </aside>
    <div class="min-w-0"><div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Customer dashboard</p><h1 class="mt-2 text-4xl font-black">Hello, {{ auth()->user()->name }}</h1></div><a href="{{ route('shop') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">Continue shopping</a></div>
    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">@foreach([['Total orders', $orderStats['total'], 'account.index'], ['Pending', $orderStats['pending'], 'account.index'], ['Delivered', $orderStats['delivered'], 'account.index'], ['Returns', $orderStats['returns'], 'account.index'], ['Wishlist', $orderStats['wishlist'], 'account.wishlist']] as $stat)<a href="{{ route($stat[2]) }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5"><p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $stat[0] }}</p><p class="mt-3 text-3xl font-black text-slate-900">{{ $stat[1] }}</p></a>@endforeach</div>
        <div id="account-downloads" class="mt-8 rounded-2xl border border-indigo-200 bg-indigo-50 p-6">
            <h2 class="text-xl font-black text-indigo-900">Your digital downloads</h2>
            <p class="mt-2 text-sm text-indigo-800">Downloads appear here after the order is delivered and payment is complete.</p>
            @if(($digitalDownloads ?? collect())->isNotEmpty())
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach($digitalDownloads as $download)
                    <a href="{{ $download['url'] }}" @if($download['external']) target="_blank" rel="noopener noreferrer" @endif class="flex items-center justify-between gap-3 rounded-xl bg-white p-4 font-semibold text-indigo-700 ring-1 ring-indigo-100 hover:bg-indigo-100">
                        <span class="min-w-0"><span class="block truncate text-xs font-medium text-slate-500">{{ $download['product_name'] }} · Order {{ $download['order_number'] }}</span><span class="block truncate">{{ $download['file_name'] }}</span></span>
                        <span aria-hidden="true">↓</span>
                    </a>
                @endforeach
            </div>
            @else
                <p class="mt-4 rounded-xl bg-white p-4 text-sm text-slate-600">No downloadable digital products yet. Completed digital orders will appear here.</p>
            @endif
        </div>
    @if(($digitalLicenses ?? collect())->isNotEmpty())<div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6"><h2 class="text-xl font-black text-emerald-900">Your digital products</h2><p class="mt-2 text-sm text-emerald-800">These keys are available only while you are signed in to your account.</p><div class="mt-4 grid gap-4 md:grid-cols-2">@foreach($digitalLicenses as $license)<div class="rounded-xl bg-white p-4 ring-1 ring-emerald-200"><p class="font-bold text-slate-900">{{ $license['product_name'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $license['reference'] }}</p><p class="mt-3 break-all rounded-lg bg-slate-900 p-3 font-mono text-sm text-white">{{ $license['secret'] }}</p>@if($license['instructions'])<p class="mt-3 whitespace-pre-wrap text-sm text-slate-600">{{ $license['instructions'] }}</p>@endif</div>@endforeach</div></div>@endif
    <div id="account-orders" class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div>
            <div class="mb-6 flex items-center justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div>
                    <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Quick links</p>
                    <h2 class="mt-1 text-xl font-black">My account</h2>
                </div>
                <div class="flex flex-wrap justify-end gap-2"><a href="{{ route('account.notifications') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Notifications @if(auth()->user()->unreadNotifications()->count())<span class="rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ auth()->user()->unreadNotifications()->count() }}</span>@endif</a><a href="{{ route('account.coupons') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Coupons</a><a href="{{ route('account.support') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Support</a></div>
            </div>
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><div class="border-b border-slate-200 px-6 py-5"><h2 class="text-xl font-black">Order history</h2><p class="mt-1 text-sm text-slate-500">You can review items after their order is delivered.</p></div><div class="divide-y divide-slate-100">@forelse($orders as $order)<div class="px-6 py-5"><a href="{{ route('account.orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-4 hover:text-indigo-700"><div><p class="font-bold">{{ $order->order_number }}</p><p class="mt-1 text-sm text-slate-500">{{ $order->placed_at?->format('d M Y, h:i A') ?: 'Pending placement' }}</p></div><div class="text-right"><p class="font-bold text-slate-900">₹{{ number_format((float) $order->grand_total, 2) }}</p><span class="text-sm text-indigo-700">{{ Str::headline($order->status) }} · View details →</span></div></a>
                    @if($order->status === 'delivered')
                        <div class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                            @foreach($order->items as $item)
                                @if($item->product)
                                    @php($review = $reviewedPurchases->get($order->id.':'.$item->product_id))
                                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                                        <a href="{{ route('products.show', $item->product->slug) }}" class="font-semibold text-slate-700 hover:text-indigo-700">{{ $item->product_name }}</a>
                                        @if($review)
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $review->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($review->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ $review->status === 'approved' ? 'Review published' : ($review->status === 'rejected' ? 'Review not approved' : 'Review awaiting approval') }}</span>
                                        @else
                                            <a href="{{ route('products.show', ['slug' => $item->product->slug, 'review_order' => $order->id]) }}#write-review" class="rounded-lg bg-indigo-600 px-3 py-2 font-bold text-white hover:bg-indigo-700">Write a review</a>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>@empty<div class="px-6 py-12 text-center text-slate-500">You have not placed any orders yet. <a href="{{ route('shop') }}" class="font-semibold text-indigo-700">Start shopping</a></div>@endforelse</div></div><div class="mt-6">{{ $orders->links() }}</div>
        </div>
        <aside class="h-fit space-y-6">
            <div id="account-wallet" class="rounded-2xl bg-slate-950 p-6 text-white"><p class="text-xs font-bold uppercase tracking-widest text-indigo-300">Wallet & loyalty</p><p class="mt-3 text-3xl font-black">₹0.00</p><p class="mt-2 text-sm text-slate-300">{{ number_format($loyaltyPoints) }} loyalty points available.</p></div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-3"><h2 class="text-xl font-black">{{ isset($editingAddress) ? 'Edit address' : 'Saved addresses' }}</h2>@if(isset($editingAddress))<a href="{{ route('account.index') }}" class="text-sm font-semibold text-indigo-700">Cancel</a>@endif</div>
            <div class="mt-5 space-y-3">
                @forelse($addresses as $address)
                    <div class="rounded-xl border {{ $address->is_default ? 'border-indigo-300 bg-indigo-50/40' : 'border-slate-200' }} p-4">
                        <div class="flex items-start justify-between gap-3"><div><p class="font-bold">{{ $address->label ?: 'Address' }} @if($address->is_default)<span class="ml-1 rounded-full bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">Default</span>@endif</p><p class="mt-1 text-sm text-slate-600">{{ $address->name }} · {{ $address->phone }}</p><p class="mt-1 text-sm text-slate-600">{{ collect([$address->address_line, $address->area, $address->upazila, $address->district, $address->division])->filter()->implode(', ') }}</p></div></div>
                        <div class="mt-3 flex flex-wrap gap-3 text-xs font-semibold"><a href="{{ route('account.addresses.edit', $address) }}" class="text-indigo-700">Edit</a>@unless($address->is_default)<form method="POST" action="{{ route('account.addresses.default', $address) }}">@csrf<button class="text-indigo-700">Make default</button></form>@endunless<form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Delete this address?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form></div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No saved addresses yet.</p>
                @endforelse
            </div>
            @php($address = $editingAddress ?? null)
            <form method="POST" action="{{ $address ? route('account.addresses.update', $address) : route('account.addresses.store') }}" class="mt-6 space-y-3 border-t border-slate-200 pt-5">
                @csrf @if($address) @method('PUT') @endif
                <input name="name" required value="{{ old('name', $address?->name ?: auth()->user()->name) }}" placeholder="Recipient name" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <input name="phone" required value="{{ old('phone', $address?->phone) }}" placeholder="Phone number" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <div class="grid grid-cols-2 gap-3"><select name="division" data-account-division class="rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Division</option>@foreach(config('shipping.bangladesh_divisions', []) as $division => $divisionDistricts)<option value="{{ $division }}" @selected(old('division', $address?->division) === $division)>{{ $division }}</option>@endforeach</select><select name="district" data-account-district class="rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">District</option></select></div>
                <select name="upazila" data-account-upazila class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Upazila / Thana</option></select>
                <textarea name="address_line" required rows="2" placeholder="Full address" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">{{ old('address_line', $address?->address_line) }}</textarea>
                <button class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white">{{ $address ? 'Save changes' : 'Add address' }}</button>
            </form>
        </aside>
    </div></div>
</section>
<script>
(() => {
    const division = document.querySelector('[data-account-division]');
    const district = document.querySelector('[data-account-district]');
    const upazila = document.querySelector('[data-account-upazila]');
    if (!division || !district || !upazila) return;
    const divisions = @json(config('shipping.bangladesh_divisions', []));
    const upazilas = @json(config('shipping.bangladesh_upazilas', []));
    const selectedDistrict = @json(old('district', $address?->district));
    const selectedUpazila = @json(old('upazila', $address?->upazila));
    const refreshDistricts = () => {
        district.innerHTML = '<option value="">District</option>';
        (divisions[division.value] || []).forEach((item) => {
            district.insertAdjacentHTML('beforeend', `<option value="${item}" ${item === selectedDistrict ? 'selected' : ''}>${item}</option>`);
        });
        refreshUpazilas();
    };
    const refreshUpazilas = () => {
        upazila.innerHTML = '<option value="">Upazila / Thana</option>';
        (upazilas[district.value] || []).forEach((item) => {
            upazila.insertAdjacentHTML('beforeend', `<option value="${item}" ${item === selectedUpazila ? 'selected' : ''}>${item}</option>`);
        });
    };
    division.addEventListener('change', refreshDistricts);
    district.addEventListener('change', refreshUpazilas);
    refreshDistricts();
})();
</script>
@endsection
