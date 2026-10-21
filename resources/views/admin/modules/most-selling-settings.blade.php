@extends('layouts.admin')
@section('title', 'Most Selling settings')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-10 lg:px-10">
    <div>
        <p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">System settings</p>
        <h1 class="mt-2 text-3xl font-black">Most Selling products</h1>
        <p class="mt-2 text-slate-500">Choose up to 5 products for the Most Selling carousel below the homepage categories.</p>
    </div>

    @if(session('success'))
        <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.most-selling.save') }}" class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div>
                <h2 class="font-bold text-slate-800">Show Most Selling section</h2>
                <p class="mt-1 text-xs text-slate-500">Turn this off to hide the section from the homepage.</p>
            </div>
            <label class="relative inline-flex cursor-pointer items-center" data-most-selling-toggle>
                <input type="hidden" name="most_selling_enabled" value="0">
                <input type="checkbox" name="most_selling_enabled" value="1" @checked(old('most_selling_enabled', $mostSellingEnabled)) data-most-selling-toggle-input class="sr-only">
                <span data-most-selling-toggle-track class="h-7 w-12 rounded-full bg-slate-400 transition-colors"></span>
                <span data-most-selling-toggle-knob class="pointer-events-none absolute left-1 h-5 w-5 rounded-full bg-white shadow transition-transform"></span>
                <span class="sr-only">Toggle Most Selling section</span>
            </label>
        </div>
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-800">Select products</h2>
                <p class="mt-1 text-xs text-slate-500">The selection order controls the carousel order.</p>
            </div>
            <span data-most-selling-count class="shrink-0 rounded-full bg-orange-100 px-2.5 py-1 text-xs font-bold text-orange-700">0 / 5 selected</span>
        </div>
        <div class="mt-4 grid max-h-[32rem] gap-2 overflow-y-auto pr-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($products as $product)
                @php($thumbnail = $product->images->first())
                <label class="group relative flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-2.5 transition hover:border-orange-300 hover:bg-orange-50/40 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                    <input type="checkbox" name="most_selling_product_ids[]" value="{{ $product->id }}" @checked(in_array($product->id, old('most_selling_product_ids', $mostSellingProductIds))) data-most-selling-option class="peer sr-only">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-lg font-black text-orange-200 ring-1 ring-slate-200">
                        @if($thumbnail)
                            <img src="{{ $thumbnail->url }}" alt="" class="h-full w-full object-cover">
                        @else
                            {{ Str::upper(Str::substr($product->name, 0, 1)) }}
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="block line-clamp-2 text-sm font-semibold text-slate-800">{{ $product->name }}</span>
                        <span class="mt-1 block truncate text-xs text-slate-500">{{ $product->category?->name }} · ৳{{ number_format((float) $product->effective_price, 2) }}</span>
                    </span>
                    <span class="ml-auto flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-slate-300 text-xs text-transparent peer-checked:border-orange-500 peer-checked:bg-orange-500 peer-checked:text-white">✓</span>
                </label>
            @endforeach
        </div>
        <button type="submit" class="mt-6 w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-500">Save Most Selling products</button>
    </form>
</section>
<script>
    (() => {
        const options = [...document.querySelectorAll('[data-most-selling-option]')];
        const counter = document.querySelector('[data-most-selling-count]');
        const toggle = document.querySelector('[data-most-selling-toggle-input]');
        const toggleTrack = document.querySelector('[data-most-selling-toggle-track]');
        const toggleKnob = document.querySelector('[data-most-selling-toggle-knob]');
        const updateToggle = () => {
            if (!toggle || !toggleTrack || !toggleKnob) return;
            toggleTrack.style.backgroundColor = toggle.checked ? 'var(--brand-primary)' : '#475569';
            toggleKnob.style.transform = toggle.checked ? 'translateX(1.25rem)' : 'translateX(0)';
            toggle.setAttribute('aria-checked', String(toggle.checked));
        };
        const updateMostSellingState = () => {
            const selected = options.filter((option) => option.checked).length;
            if (counter) counter.textContent = `${selected} / 5 selected`;
            options.forEach((option) => {
                option.disabled = !option.checked && selected >= 5;
            });
        };
        options.forEach((option) => option.addEventListener('change', updateMostSellingState));
        toggle?.addEventListener('change', updateToggle);
        updateToggle();
        updateMostSellingState();
    })();
</script>
@endsection
