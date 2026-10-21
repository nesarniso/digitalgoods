@extends('layouts.admin')
@section('title', 'Courier management')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">Courier management</h1>
            <p class="mt-2 text-slate-500">Track enabled courier gateways, statuses, and fulfilment activity.</p>
        </div>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <h2 class="border-b border-slate-200 px-6 py-4 font-bold">Configured couriers</h2>
            <div class="divide-y divide-slate-100">
                @forelse($drivers as $driver => $config)
                    <div class="flex items-center justify-between gap-4 px-6 py-4">
                        <div>
                            <p class="font-semibold">{{ Str::headline($driver) }}</p>
                            <p class="text-sm text-slate-500">{{ ($config['enabled'] ?? false) ? 'Enabled' : 'Disabled' }} · {{ $config['class'] ?? 'N/A' }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ ($config['enabled'] ?? false) ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ($config['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <p class="p-6 text-sm text-slate-500">No courier drivers configured yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <h2 class="border-b border-slate-200 px-6 py-4 font-bold">Latest shipments</h2>
            <div class="divide-y divide-slate-100">
                @forelse($shipments as $shipment)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold">{{ $shipment->shipment_number }}</p>
                                <p class="text-sm text-slate-500">Order {{ $shipment->order?->order_number ?? '#' . $shipment->order_id }}</p>
                            </div>
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ Str::headline(str_replace('_',' ', $shipment->status)) }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">Courier: {{ Str::headline($shipment->courier) }} · Tracking: {{ $shipment->tracking_number ?: 'Not assigned yet' }}</p>
                    </div>
                @empty
                    <p class="p-6 text-sm text-slate-500">No shipments have been created yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
