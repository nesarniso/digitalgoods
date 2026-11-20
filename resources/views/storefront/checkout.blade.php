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
    $customerName = auth()->user()?->name ?? '';
@endphp
<section class="mx-auto max-w-7xl min-w-0 px-4 py-4 sm:px-6 sm:py-6">
    <nav class="flex flex-wrap items-center gap-3 text-base font-semibold text-slate-500" aria-label="Checkout progress">
        <a href="{{ route('cart') }}" class="hover:text-slate-900">Shopping Cart</a>
        <span aria-hidden="true">→</span>
        <span aria-current="step" class="border-b-2 border-orange-500 pb-1 text-slate-900">Checkout</span>
        <span aria-hidden="true">→</span>
        <span>Order Complete</span>
    </nav>

    <div data-checkout-added-notice class="mt-8 hidden items-center gap-4 rounded-2xl bg-[#43984b] px-5 py-4 text-sm font-medium text-white sm:px-6">
        <span class="text-xl" aria-hidden="true">✓</span>
        <a href="{{ route('shop') }}" class="font-bold underline underline-offset-2">Continue Shopping</a>
        <span data-checkout-added-message></span>
    </div>

    <div class="mt-5 space-y-4 text-sm font-semibold text-slate-800">
        @guest
            <p>Returning customer? <button type="button" data-login-toggle aria-expanded="false" class="font-bold text-orange-600 underline underline-offset-2">Click here to login</button></p>
        @endguest
        <p>Have a coupon? <button type="button" data-checkout-coupon-toggle aria-expanded="false" class="font-bold text-orange-600 underline underline-offset-2">Click here to enter your code</button></p>
        <form data-checkout-coupon-form class="hidden max-w-xl rounded-xl border border-slate-200 bg-white p-4">
            <label for="checkout-coupon-code" class="sr-only">Coupon code</label>
            <div class="flex flex-wrap gap-2">
                <input id="checkout-coupon-code" name="code" autocomplete="off" placeholder="Coupon code" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-normal">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 font-bold text-white hover:bg-slate-700">Apply coupon</button>
            </div>
            <p data-checkout-coupon-status class="mt-2 hidden text-sm font-medium" role="status"></p>
        </form>
    </div>

    <div class="mt-7 grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(360px,1fr)]">
        <div class="min-w-0 space-y-6">
            <form data-checkout-form data-authenticated="{{ auth()->check() ? '1' : '0' }}" class="min-w-0 space-y-6">
                <section class="min-w-0 rounded-2xl bg-[#f4f4f4] p-5 sm:p-7">
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Billing Details</h1>
                    <p data-digital-checkout-note class="mt-3 hidden rounded-lg bg-white px-3 py-2 text-sm text-slate-600">Digital order — no delivery address is needed.</p>

                    <div data-checkout-contact class="mt-6 grid min-w-0 grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 {{ auth()->check() && $savedAddresses->isNotEmpty() ? 'hidden' : '' }}">
                        <label class="min-w-0 text-sm font-medium text-slate-700">First name <span class="text-red-500">*</span>
                            <input name="first_name" value="{{ $customerName ? Str::before($customerName, ' ') : '' }}" autocomplete="given-name" required class="mt-2 w-full rounded-full border border-slate-200 bg-white px-4 py-3 font-normal">
                        </label>
                        <label class="min-w-0 text-sm font-medium text-slate-700">Last name <span class="text-red-500">*</span>
                            <input name="last_name" value="{{ $customerName ? Str::after($customerName, ' ') : '' }}" autocomplete="family-name" required class="mt-2 w-full rounded-full border border-slate-200 bg-white px-4 py-3 font-normal">
                        </label>
                        <label class="min-w-0 text-sm font-medium text-slate-700 sm:col-span-2">Phone <span class="text-red-500">*</span>
                            <input name="phone" value="{{ auth()->user()?->phone }}" autocomplete="tel" required class="mt-2 w-full rounded-full border border-slate-200 bg-white px-4 py-3 font-normal">
                        </label>
                        <label class="min-w-0 text-sm font-medium text-slate-700 sm:col-span-2">Email address <span class="text-red-500">*</span>
                            <input name="guest_email" type="email" value="{{ auth()->user()?->email }}" autocomplete="email" {{ auth()->check() ? 'readonly' : 'required' }} class="mt-2 w-full rounded-full border border-slate-200 bg-white px-4 py-3 font-normal">
                        </label>
                        <input type="hidden" name="name" value="{{ $customerName }}">
                    </div>

                    @guest
                        <label class="mt-5 inline-flex cursor-pointer items-center gap-2 text-sm font-bold text-slate-900">
                            <input type="checkbox" data-create-account class="h-4 w-4 rounded border-slate-300 accent-orange-500">
                            Create an account?
                        </label>
                        <div data-account-panel class="mt-4 hidden rounded-xl border border-slate-200 bg-white p-4">
                            <p data-account-panel-description class="text-sm text-slate-600">Enter your email to create an account for your orders and digital downloads.</p>
                            <label data-login-email-label class="mt-4 hidden block text-sm font-semibold text-slate-700">Email address
                                <input name="login_email" type="email" autocomplete="username" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-4 py-3 font-normal">
                            </label>
                            <button type="button" data-account-check class="mt-3 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-800 hover:bg-slate-50">Continue with email</button>
                            <p data-account-status class="mt-2 hidden text-sm font-semibold" role="status"></p>
                            <div data-account-fields class="hidden">
                                <label class="mt-4 block text-sm font-semibold text-slate-700" data-password-label>Create password
                                    <input name="password" type="password" minlength="4" autocomplete="new-password" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-4 py-3 font-normal">
                                </label>
                                <label data-confirm-password-label class="mt-4 block text-sm font-semibold text-slate-700">Confirm password
                                    <input name="password_confirmation" type="password" minlength="4" autocomplete="new-password" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-4 py-3 font-normal">
                                </label>
                                <p data-account-help class="mt-2 text-xs leading-5 text-slate-500"></p>
                            </div>
                            <button type="button" data-account-login class="mt-4 hidden rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Login and continue</button>
                            <a data-forgot-password href="{{ route('account.login') }}" class="mt-3 hidden text-sm font-semibold text-orange-600 underline underline-offset-2">Forgot password?</a>
                        </div>
                    @endguest

                    <div data-checkout-address class="mt-7 border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-bold text-slate-900">Delivery Details</h2>
                        @if($savedAddresses->isNotEmpty())
                            <label data-saved-address-field class="mt-4 block text-sm font-semibold text-slate-700">Use a saved address
                                <select name="address_id" data-saved-address class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal">
                                    <option value="">Enter a new delivery address</option>
                                    @foreach($savedAddresses as $savedAddress)
                                        <option value="{{ $savedAddress->id }}" @selected($savedAddress->is_default) data-address='@json($savedAddress)'>{{ $savedAddress->label ?: 'Address' }} — {{ $savedAddress->name }}, {{ $savedAddress->district ?: $savedAddress->division }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        <div data-checkout-fields class="mt-4 grid min-w-0 gap-4 sm:grid-cols-2 {{ auth()->check() && $savedAddresses->isNotEmpty() ? 'hidden' : '' }}">
                            <label class="text-sm font-medium text-slate-700">Division
                                <select name="division" data-checkout-division required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal"><option value="">Select your division</option>@foreach($divisions as $division => $divisionDistricts)<option value="{{ $division }}">{{ $division }}</option>@endforeach</select>
                            </label>
                            <label class="text-sm font-medium text-slate-700">District
                                <select name="district" data-checkout-district required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal"><option value="">Select division first</option>@foreach($districts as $district)<option value="{{ $district }}" data-division="{{ collect($divisions)->search(fn ($items) => in_array($district, $items, true)) }}" hidden disabled>{{ $district }}</option>@endforeach</select>
                                <span class="mt-1 block text-xs font-normal text-slate-500">Division select করলে ওই division-এর district দেখাবে। Dhaka district হলে Inside Dhaka rate, অন্য জেলা হলে Outside Dhaka rate প্রযোজ্য হবে।</span>
                            </label>
                            <label class="text-sm font-medium text-slate-700">Upazila / Thana
                                <select name="upazila" data-checkout-upazila required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal"><option value="">Select district first</option></select>
                            </label>
                            <label class="text-sm font-medium text-slate-700 sm:col-span-2">Address / Village / Road
                                <input name="address_line" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal" placeholder="Village, road name, house details">
                            </label>
                        </div>
                    </div>

                    <div data-checkout-delivery>
                        @if ($shippingMethods->isNotEmpty())
                            <input type="hidden" name="shipping_method_id" value="{{ $shippingMethods->first()->id }}">
                        @endif
                    </div>
                </section>

                <section class="min-w-0 rounded-2xl bg-[#f4f4f4] p-5 sm:p-7">
                    <h2 class="text-xl font-extrabold text-slate-900">Payment Information</h2>
                    <div class="mt-4 grid min-w-0 grid-cols-1 gap-3 sm:flex sm:flex-wrap">
                        @forelse ($paymentMethods as $key => $method)
                            <label data-payment-method="{{ $key }}" class="flex min-h-14 w-full cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm transition hover:border-indigo-400 sm:w-auto">
                                <input class="h-4 w-4 shrink-0 accent-[var(--brand-primary)]" type="radio" name="payment_method" value="{{ $key }}" @checked($loop->first) required>
                                @if ($key === 'cash_on_delivery')
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7zM6 20h.01M18 20h.01M6 17h.01M18 17h.01"/></svg></span>
                                @elseif (in_array($key, ['bkash', 'nagad', 'rocket'], true))
                                    <img src="{{ asset("payment-icons/{$key}.png") }}" alt="{{ $method['label'] }} logo" class="h-9 w-9 rounded-lg object-cover">
                                @else
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-100 text-sky-700" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18M5 10v9M9 10v9M15 10v9M19 10v9M3 19h18M4 10l8-6 8 6"/></svg></span>
                                @endif
                                <span class="break-words">{{ $method['label'] }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-amber-700">No payment methods are configured yet.</p>
                        @endforelse
                    </div>
                    <div data-manual-payment data-payment-config='@json($paymentConfig)' class="mt-5 hidden rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                        <p data-payment-instructions></p>
                        <div data-payment-bank-details class="mt-4 hidden overflow-hidden rounded-xl border border-amber-200 bg-white text-sm"></div>
                        <p data-payment-merchant class="mt-3 font-bold"></p>
                        <div data-payment-proof-fields class="mt-3 grid gap-3 sm:grid-cols-2">
                            <label class="font-semibold text-slate-800">Payer mobile<input name="payer_mobile" placeholder="Enter the mobile number used to pay" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"></label>
                            <label class="font-semibold text-slate-800">Transaction ID<input name="transaction_id" placeholder="Enter your transaction ID" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"></label>
                        </div>
                    </div>
                    <button data-checkout-place-order type="submit" class="mt-7 w-full rounded-xl bg-orange-500 px-5 py-3.5 font-black text-white hover:bg-orange-600">Place order</button>
                </section>
            </form>
        </div>

        <aside data-checkout-summary class="h-fit min-w-0 overflow-hidden rounded-2xl bg-[#f4f4f4] p-5 text-slate-900 sm:p-7"></aside>
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
        if (address?.name) {
            const [firstName = '', ...lastName] = address.name.trim().split(/\s+/);
            const firstNameField = form.querySelector('[name="first_name"]');
            const lastNameField = form.querySelector('[name="last_name"]');
            if (firstNameField) firstNameField.value = firstName;
            if (lastNameField) lastNameField.value = lastName.join(' ');
        }
        addressFields?.classList.toggle('hidden', Boolean(address));
    };
    select.addEventListener('change', applyAddress);
    applyAddress();
})();
</script>
@endsection
