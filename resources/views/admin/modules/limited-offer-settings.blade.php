@extends('layouts.admin')
@section('title', 'Limited time offer settings')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-10 lg:px-10">
    <div>
        <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">System settings</p>
        <h1 class="mt-2 text-3xl font-black">Limited time offer</h1>
        <p class="mt-2 text-slate-500">Choose up to 7 published products and set when the homepage offer ends. The first selected product gets the large image.</p>
    </div>

    @if(session('success'))
        <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.limited-offer.save') }}" class="mt-8 space-y-6">
        @csrf
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <label class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <span>
                    <span class="block font-bold text-slate-800">Show Limited Offers on homepage</span>
                    <span class="mt-1 block text-xs text-slate-500">The section automatically hides when the countdown reaches zero.</span>
                </span>
                <input type="checkbox" name="limited_offer_enabled" value="1" @checked(old('limited_offer_enabled', $limitedOfferEnabled)) class="h-5 w-5 accent-indigo-600">
            </label>

            <label class="mt-5 block text-sm font-semibold text-slate-800">
                Offer end date and time ({{ config('app.timezone') }})
                <input type="datetime-local" name="limited_offer_ends_at" value="{{ old('limited_offer_ends_at', $limitedOfferEndsAt) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">
                <span class="mt-1 block text-xs font-normal text-slate-500">Set a future time. The offer will not appear after it expires.</span>
            </label>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-bold text-slate-800">Choose offer products</h2>
                    <p class="mt-1 text-xs text-slate-500">Select 1–7 published products. Their current sale prices are shown.</p>
                </div>
                <span data-limited-offer-count class="shrink-0 rounded-full bg-orange-100 px-2.5 py-1 text-xs font-bold text-orange-700">0 / 7 selected</span>
            </div>
            <div class="mt-4 grid max-h-[34rem] gap-2 overflow-y-auto pr-1 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($products as $product)
                    @php($thumbnail = $product->images->first())
                    <label class="group relative flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-2.5 transition hover:border-orange-300 hover:bg-orange-50/40 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                        <input type="checkbox" name="limited_offer_product_ids[]" value="{{ $product->id }}" @checked(in_array($product->id, old('limited_offer_product_ids', $limitedOfferProductIds))) data-limited-offer-option class="peer sr-only">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-lg font-black text-orange-300 ring-1 ring-slate-200">
                            @if($thumbnail)
                                <img src="{{ $thumbnail->url }}" alt="" class="h-full w-full object-cover">
                            @else
                                {{ Str::upper(Str::substr($product->name, 0, 1)) }}
                            @endif
                        </span>
                        <span class="min-w-0">
                            <span class="block line-clamp-2 text-sm font-semibold text-slate-800">{{ $product->name }}</span>
                            <span class="mt-1 block truncate text-xs text-slate-500">{{ $product->category?->name }} · @if($product->sale_price && (float) $product->sale_price < (float) $product->regular_price)<s class="text-slate-400">₹{{ number_format((float) $product->regular_price, 2) }}</s> @endif<span class="font-bold text-orange-600">₹{{ number_format((float) $product->effective_price, 2) }}</span></span>
                        </span>
                        <span class="ml-auto flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-slate-300 text-xs text-transparent peer-checked:border-orange-500 peer-checked:bg-orange-500 peer-checked:text-white">✓</span>
                    </label>
                @endforeach
            </div>
            @if($products->isEmpty())
                <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No published products are available to select.</p>
            @endif
            <button type="submit" class="mt-6 w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-500">Save limited offer</button>
        </div>
    </form>
</section>
<script>
    (() => {
        const options = [...document.querySelectorAll('[data-limited-offer-option]')];
        const counter = document.querySelector('[data-limited-offer-count]');
        const updateSelection = () => {
            const selected = options.filter((option) => option.checked).length;
            if (counter) counter.textContent = `${selected} / 7 selected`;
            options.forEach((option) => {
                option.disabled = !option.checked && selected >= 7;
            });
        };
        options.forEach((option) => option.addEventListener('change', updateSelection));
        updateSelection();
    })();
</script>
@endsection
