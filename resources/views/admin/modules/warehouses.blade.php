@extends('layouts.admin')
@section('title', 'Warehouses')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Operations</p><h1 class="mt-2 text-3xl font-black">Warehouses</h1><p class="mt-2 text-slate-500">Manage fulfillment locations and stock ownership.</p></div>
        <div class="rounded-xl bg-indigo-50 px-4 py-3 text-sm text-indigo-800">Warehouse API connected</div>
    </div>
    <div class="mt-8 grid gap-6 sm:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Locations</p><p class="mt-2 text-2xl font-black">{{ $warehouses->total() }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Inventory records</p><p class="mt-2 text-2xl font-black">{{ number_format((float) $warehouses->sum('inventories_count')) }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 border-indigo-100 ring-slate-200"><p class="text-sm text-slate-500">Active locations</p><p class="mt-2 text-2xl font-black">{{ $warehouses->where('is_active', true)->count() }}</p></div>
    </div>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.warehouses.store') }}" class="mt-8 grid gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:grid-cols-4">
        @csrf
        <input name="name" required placeholder="Warehouse name" class="rounded-xl border border-slate-300 px-4 py-3">
        <input name="code" required placeholder="Code (e.g. DHAKA-01)" class="rounded-xl border border-slate-300 px-4 py-3">
        <input name="district" placeholder="District" class="rounded-xl border border-slate-300 px-4 py-3">
        <input name="address" placeholder="Address" class="rounded-xl border border-slate-300 px-4 py-3">
        <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white md:col-span-4">Create warehouse</button>
    </form>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500"><tr><th class="px-6 py-3">Warehouse</th><th class="px-6 py-3">Location</th><th class="px-6 py-3">Inventory</th><th class="px-6 py-3">Physical stock</th><th class="px-6 py-3">Reserved</th><th class="px-6 py-3">Status</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($warehouses as $warehouse)
                    <tr>
                        <td class="px-6 py-4"><p class="font-semibold">{{ $warehouse->name }} @if($warehouse->is_default)<span class="ml-1 rounded-full bg-indigo-50 px-2 py-1 text-xs text-indigo-700">Default</span>@endif</p><p class="text-xs text-slate-400">{{ $warehouse->code }}</p></td>
                        <td class="px-6 py-4">{{ collect([$warehouse->district, $warehouse->division, $warehouse->country])->filter()->join(', ') ?: '—' }}<p class="text-xs text-slate-400">{{ $warehouse->address ?: 'No address' }}</p></td>
                        <td class="px-6 py-4">{{ $warehouse->inventories_count }}</td>
                        <td class="px-6 py-4 font-semibold">{{ $warehouse->inventories_sum_physical_stock ?? 0 }}</td>
                        <td class="px-6 py-4">{{ $warehouse->inventories_sum_reserved_stock ?? 0 }}</td>
                        <td class="px-6 py-4"><span class="rounded-full px-3 py-1 text-xs {{ $warehouse->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No warehouses configured yet. Create one through the Warehouse API to see it here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $warehouses->links() }}</div>
</section>
@endsection
