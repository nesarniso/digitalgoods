@extends('layouts.storefront')
@section('title', 'Checkout | '.config('app.name'))
@section('content')
@php
    $paymentConfig = $paymentMethods->mapWithKeys(function ($method, $key) {
        return [$key => [
            'merchant_number' => $method['merchant_number'] ?? '',
            'account_type' => $method['account_type'] ?? '',
            'instructions' => $method['instructions'] ?? '',
            'qr_code' => $method['qr_code'] ?? '',
            'bank_details' => $method['bank_details'] ?? null,
        ]];
    })->all();
@endphp
<section class="mx-auto max-w-5xl min-w-0 px-4 py-8 sm:px-6 sm:py-12">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--brand-primary)]">Almost there</p><h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Secure checkout</h1></div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-500"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--brand-primary)] text-white">1</span><span>Details</span><span class="h-px w-6 bg-slate-300"></span><span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200">2</span><span>Confirm</span></div>
    </div>
    <div class="mt-5 grid gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-sm text-emerald-900 sm:grid-cols-3">
        <div class="flex items-center gap-2 font-semibold"><span class="text-lg">✓</span> Secure checkout</div><div class="flex items-center gap-2 font-semibold"><span class="text-lg">↺</span> Easy support</div><div class="flex items-center gap-2 font-semibold"><span class="text-lg">⌖</span> Reliable delivery</div>
    </div>
    <div class="mt-6 grid min-w-0 gap-6 lg:mt-8 lg:grid-cols-[minmax(0,1fr)_380px]">
        <form data-checkout-form data-authenticated="{{ auth()->check() ? '1' : '0' }}" class="min-w-0 overflow-hidden rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <div data-checkout-address>
            <h2 class="text-xl font-bold">Delivery details</h2>
            @if($savedAddresses->isNotEmpty())
                <label class="mt-5 block text-sm font-semibold">Use a saved address
                    <select name="address_id" data-saved-address class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">
                        <option value="">Enter a new delivery address</option>
                        @foreach($savedAddresses as $savedAddress)
                            <option value="{{ $savedAddress->id }}" @selected($savedAddress->is_default) data-address='@json($savedAddress)'>{{ $savedAddress->label ?: 'Address' }} — {{ $savedAddress->name }}, {{ $savedAddress->district ?: $savedAddress->division }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            @guest
                <div data-checkout-account class="mt-5 rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
                    <p class="font-bold text-slate-900">Your customer account</p>
                    <p class="mt-1 text-sm text-slate-600">Enter your email first. We will sign you in or create your account before payment.</p>
                    <label class="mt-3 block text-sm font-semibold">Email<input name="guest_email" type="email" autocomplete="email" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal"></label>
                    <button type="button" data-account-check class="mt-3 rounded-xl border border-indigo-600 px-4 py-3 text-sm font-bold text-indigo-700 hover:bg-indigo-50">Continue with email</button>
                    <p data-account-status class="mt-2 hidden text-sm font-semibold" role="status"></p>
                    <button type="button" data-account-login class="mt-3 hidden rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-700">Login and continue</button>
                    <a data-forgot-password href="{{ route('account.login') }}" class="mt-2 hidden text-sm font-semibold text-indigo-700 hover:underline">Forgot password?</a>
                    <div data-account-fields class="hidden">
                        <label class="mt-5 block text-sm font-semibold" data-password-label>Password<input name="password" type="password" minlength="4" autocomplete="current-password" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal"></label>
                        <label data-confirm-password-label class="mt-4 block text-sm font-semibold">Confirm password<input name="password_confirmation" type="password" minlength="4" autocomplete="new-password" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal"></label>
                        <p data-account-help class="mt-1 text-xs leading-5 text-slate-500"></p>
                    </div>
                </div>
            @endguest
            <div data-checkout-contact class="mt-5 grid min-w-0 gap-4 sm:grid-cols-2 {{ auth()->check() && $savedAddresses->isNotEmpty() ? 'hidden' : '' }}">
                <label class="text-sm font-semibold">Full name <span class="font-normal text-slate-500">(optional)</span><input name="name" value="{{ auth()->user()?->name }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                <label class="text-sm font-semibold">Phone <span class="font-normal text-slate-500">(optional)</span><input name="phone" value="{{ auth()->user()?->phone }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
            </div>
            @guest
                <p class="mt-2 text-xs leading-5 text-slate-500">Your contact details are used for order updates and account access.</p>
            @endguest
            <div data-checkout-fields class="mt-5 grid gap-4 sm:grid-cols-2 {{ auth()->check() && $savedAddresses->isNotEmpty() ? 'hidden' : '' }}">
                <label class="text-sm font-semibold">Division<select name="division" data-checkout-division required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><option value="">Select your division</option>@foreach($divisions as $division => $divisionDistricts)<option value="{{ $division }}">{{ $division }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold">District<select name="district" data-checkout-district required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><option value="">Select division first</option>@foreach($districts as $district)<option value="{{ $district }}" data-division="{{ collect($divisions)->search(fn ($items) => in_array($district, $items, true)) }}" hidden disabled>{{ $district }}</option>@endforeach</select><span class="mt-1 block text-xs font-normal text-slate-500">Division select করলে ওই division-এর district দেখাবে। Dhaka district হলে Inside Dhaka rate, অন্য জেলা হলে Outside Dhaka rate প্রযোজ্য হবে।</span></label>
                <label class="text-sm font-semibold">Upazila / Thana<select name="upazila" data-checkout-upazila required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"><option value="">Select district first</option></select></label>
                <label class="text-sm font-semibold sm:col-span-2">Address / Village / Road<input name="address_line" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal" placeholder="Village, road name, house details"></label>
            </div>
            </div>
            <div data-checkout-delivery>
                @if ($shippingMethods->isNotEmpty())
                    <input type="hidden" name="shipping_method_id" value="{{ $shippingMethods->first()->id }}">
                @endif
            </div>
            <h2 class="mt-8 text-xl font-bold">Payment</h2>
            <div class="mt-4 grid min-w-0 grid-cols-1 gap-3 sm:flex sm:flex-wrap">@forelse ($paymentMethods as $key => $method)<label data-payment-method="{{ $key }}" class="flex min-h-14 w-full cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm transition hover:border-indigo-400 sm:w-auto"><input class="h-4 w-4 shrink-0 accent-[var(--brand-primary)]" type="radio" name="payment_method" value="{{ $key }}" @checked($loop->first) required>
                    @if ($key === 'cash_on_delivery')
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7zM6 20h.01M18 20h.01M6 17h.01M18 17h.01"/></svg></span>
                    @elseif (in_array($key, ['bkash', 'nagad', 'rocket'], true))
                        <img src="{{ asset("payment-icons/{$key}.png") }}" alt="{{ $method['label'] }} logo" class="h-9 w-9 rounded-lg object-cover">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-100 text-sky-700" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18M5 10v9M9 10v9M15 10v9M19 10v9M3 19h18M4 10l8-6 8 6"/></svg></span>
                    @endif
                    <span class="break-words">{{ $method['label'] }}</span></label>@empty<p class="text-sm text-amber-700">No payment methods are configured yet.</p>@endforelse</div>
            <div data-manual-payment data-payment-config='@json($paymentConfig)' class="mt-5 hidden rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                <p data-payment-instructions></p>
                <div data-payment-bank-details class="mt-4 hidden overflow-hidden rounded-xl border border-amber-200 bg-white text-sm"></div>
                <p data-payment-merchant class="mt-3 font-bold"></p>
                <div data-payment-proof-fields class="mt-3 grid gap-3 sm:grid-cols-2">
                    <label class="font-semibold text-slate-800">Payer mobile<input name="payer_mobile" placeholder="Enter the mobile number used to pay" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"></label>
                    <label class="font-semibold text-slate-800">Transaction ID<input name="transaction_id" placeholder="Enter your transaction ID" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"></label>
                </div>
            </div>
            <button class="mt-8 w-full rounded-xl bg-indigo-600 px-5 py-3.5 font-black text-white hover:bg-indigo-700 sm:py-3">Place order securely</button>
        </form>
        <aside data-checkout-summary class="h-fit min-w-0 overflow-hidden rounded-2xl bg-slate-900 p-5 text-white sm:p-6"></aside>
    </div>
</section>
<script>
    (() => {
        const division = document.querySelector('[data-checkout-division]');
        const district = document.querySelector('[data-checkout-district]');
        const upazila = document.querySelector('[data-checkout-upazila]');
        if (!division || !district || !upazila) return;
        const divisions = @json($divisions);
        const upazilas = @json($upazilas);
        const renderDistricts = () => {
            const allowed = divisions[division.value] || [];
            district.querySelector('option[value=""]').textContent = division.value ? 'Select your district' : 'Select division first';
            Array.from(district.options).forEach((option) => {
                if (!option.value) return;
                const visible = allowed.includes(option.value);
                option.hidden = !visible;
                option.disabled = !visible;
                if (!visible) option.selected = false;
            });
            if (!allowed.includes(district.value)) district.value = '';
            upazila.innerHTML = '<option value="">Select district first</option>';
            (upazilas[district.value] || ['Other Upazila / Thana']).forEach((item) => {
                upazila.insertAdjacentHTML('beforeend', `<option value="${item}">${item}</option>`);
            });
        };
        division.addEventListener('change', renderDistricts);
        district.addEventListener('change', renderDistricts);
        renderDistricts();
    })();
</script>
<script>
(() => {
    const select = document.querySelector('[data-saved-address]');
    const form = document.querySelector('[data-checkout-form]');
    if (!select || !form) return;
    const fields = ['name', 'phone', 'division', 'district', 'upazila', 'address_line'];
    const addressFields = form.querySelector('[data-checkout-address] .grid');
    const applyAddress = () => {
        const option = select.selectedOptions[0];
        const address = option?.dataset.address ? JSON.parse(option.dataset.address) : null;
        form.querySelector('[name="address_id"]')?.toggleAttribute('disabled', !address);
        fields.forEach((name) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (!field) return;
            if (address) field.value = address[name] || '';
            field.required = !address;
        });
        addressFields?.classList.toggle('hidden', Boolean(address));
    };
    select.addEventListener('change', applyAddress);
    applyAddress();
})();
</script>
@endsection
