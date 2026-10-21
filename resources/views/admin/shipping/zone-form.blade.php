@extends('layouts.admin')
@section('title', $zone->exists ? 'Edit shipping zone' : 'Add shipping zone')
@section('content')
<section class="mx-auto max-w-3xl px-6 py-10 lg:px-10">
    <a href="{{ route('admin.shipping') }}" class="text-sm font-semibold text-indigo-700">← Back</a>
    <h1 class="mt-5 text-3xl font-black">{{ $zone->exists ? 'Edit shipping zone' : 'Add shipping zone' }}</h1>
    @if(session('success'))
        <div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
@php($selectedDistricts = old('district', $zone->district ? array_map('trim', explode(',', $zone->district)) : []))
@php($selectedDivision = old('division', $zone->division))
<form method="POST" action="{{ $zone->exists ? route('admin.shipping.zones.update', $zone) : route('admin.shipping.zones.store') }}" class="mt-8 grid gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf @if($zone->exists) @method('PUT') @endif
    <label class="text-sm font-semibold">Zone name<input name="name" required value="{{ old('name', $zone->name) }}" placeholder="Dhaka city" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
    <div class="grid gap-4 sm:grid-cols-2">
        <label class="text-sm font-semibold">Country<input name="country" value="{{ old('country', $zone->country) }}" placeholder="Bangladesh" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
        <label class="text-sm font-semibold">Division
            <select name="division" data-zone-division class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">
                <option value="">All divisions</option>
                @foreach($divisions as $division => $divisionDistricts)<option value="{{ $division }}" @selected($selectedDivision === $division)>{{ $division }}</option>@endforeach
            </select>
        </label>
        <label class="text-sm font-semibold sm:col-span-2">Districts
            <select name="district[]" data-zone-districts multiple size="8" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal">
                @foreach($districts as $district)<option value="{{ $district }}" data-division="{{ collect($divisions)->search(fn ($items) => in_array($district, $items, true)) }}" @selected(in_array($district, $selectedDistricts, true))>{{ $district }}</option>@endforeach
            </select>
            <span class="mt-1 block text-xs font-normal text-slate-500">আগে division নির্বাচন করুন, তারপর Ctrl/Cmd ধরে একসাথে একাধিক district নির্বাচন করুন।</span>
        </label>
        <label class="text-sm font-semibold">Area<input name="area" value="{{ old('area', $zone->area) }}" placeholder="Area (optional)" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
    </div>
    <label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $zone->exists ? $zone->is_active : true))> Active</label>
    <button class="w-fit rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">{{ $zone->exists ? 'Save changes' : 'Create zone' }}</button>
</form>
<script>
    (() => {
        const division = document.querySelector('[data-zone-division]');
        const districts = document.querySelector('[data-zone-districts]');
        if (!division || !districts) return;
        const divisions = @json($divisions);
        const selected = new Set(@json($selectedDistricts));
        const renderDistricts = () => {
            const selectedDivision = division.value;
            const allowed = selectedDivision ? (divisions[selectedDivision] || []) : @json($districts);
            Array.from(districts.options).forEach((option) => {
                const visible = allowed.includes(option.value);
                option.hidden = !visible;
                option.disabled = !visible;
                if (!visible) option.selected = false;
                else if (selected.has(option.value)) option.selected = true;
            });
        };
        division.addEventListener('change', () => {
            selected.clear();
            renderDistricts();
        });
        renderDistricts();
    })();
</script>
@if($zone->exists)
    <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-xl font-black">Delivery rates for this zone</h2>
        <p class="mt-1 text-sm text-slate-500">Set the delivery charge directly here for this zone.</p>
        <div class="mt-4 divide-y divide-slate-100">
            @foreach($methods as $method)
                @php($rate = $method->rates->firstWhere('shipping_zone_id', $zone->id))
                <form method="POST" action="{{ route('admin.shipping.rates.store', $method) }}" class="grid gap-3 py-4 sm:grid-cols-[1fr_180px_auto] sm:items-end">@csrf
                    <input type="hidden" name="shipping_zone_id" value="{{ $zone->id }}">
                    <label class="text-sm font-semibold">{{ $method->name }}<input name="base_rate" type="number" step="0.01" min="0" required value="{{ old('base_rate', $rate?->base_rate) }}" placeholder="50" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                    <label class="text-sm font-semibold">Per kg (optional)<input name="per_kg_rate" type="number" step="0.01" min="0" value="{{ old('per_kg_rate', $rate?->per_kg_rate) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"></label>
                    <button class="rounded-lg bg-slate-900 px-4 py-3 text-sm font-bold text-white">Save rate</button>
                </form>
            @endforeach
        </div>
    </div>
@endif
</section>
@endsection
