@extends('layouts.admin')

@section('title', 'Entrepreneur Report')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Management analytics</p>
    <h1 class="mt-2 text-3xl font-black">Entrepreneur Report</h1>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Total entrepreneurs</p><p class="mt-1 text-2xl font-black">{{ $totalCount }}</p><p class="mt-2 text-xs text-slate-500">{{ $activeCount }} active · {{ $pendingCount }} pending</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Attributed sales</p><p class="mt-1 text-2xl font-black">{{ $salesCount }}</p><p class="mt-2 text-xs text-emerald-600">{{ $deliveredCount }} delivered</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Attributed revenue</p><p class="mt-1 text-2xl font-black">₹{{ number_format((float) $revenue, 2) }}</p></div>
        <div class="rounded-2xl bg-emerald-50 p-5 text-emerald-800"><p class="text-sm">Tracked profit</p><p class="mt-1 text-2xl font-black">₹{{ number_format((float) $profit, 2) }}</p></div>
    </div>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Entrepreneur</th><th class="px-5 py-3">Code</th><th class="px-5 py-3">Area</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($entrepreneurs as $entrepreneur)<tr><td class="px-5 py-4"><a href="{{ route('admin.entrepreneurs.show', $entrepreneur) }}" class="font-semibold text-indigo-700 hover:underline">{{ $entrepreneur->user?->name }}</a><p class="text-xs text-slate-500">{{ $entrepreneur->user?->phone ?: $entrepreneur->user?->email }}</p></td><td class="px-5 py-4 font-mono text-xs">{{ $entrepreneur->entrepreneur_code }}</td><td class="px-5 py-4">{{ $entrepreneur->district ?: $entrepreneur->address ?: '—' }}</td><td class="px-5 py-4 capitalize">{{ $entrepreneur->status }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No entrepreneurs found.</td></tr>@endforelse
        </tbody></table>
    </div>
    <div class="mt-6">{{ $entrepreneurs->links() }}</div>
</section>
@endsection
