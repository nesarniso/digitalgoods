@extends('layouts.entrepreneur')

@section('title', 'My Referral Team')
@section('header_title', 'My Referral Team')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Network management</p><h1 class="mt-2 text-3xl font-black">My Referral Team</h1><p class="mt-2 text-slate-600">Track the entrepreneurs who joined through your referral link.</p></div>
        <div class="flex gap-3"><div class="rounded-2xl bg-white px-5 py-4 text-center shadow-sm ring-1 ring-slate-200"><p class="text-xs text-slate-500">Total referrals</p><p class="mt-1 text-2xl font-black">{{ $totalCount }}</p></div><div class="rounded-2xl bg-emerald-50 px-5 py-4 text-center text-emerald-800"><p class="text-xs">Active</p><p class="mt-1 text-2xl font-black">{{ $activeCount }}</p></div></div>
    </div>
    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><h2 class="font-bold">Your general referral link</h2><p class="mt-1 text-sm text-slate-500">Share this link to invite new entrepreneurs. Their registration will be connected to your account.</p><div class="mt-4 flex max-w-3xl gap-2"><input readonly value="{{ $referralLink }}" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm"><button type="button" data-copy-link="{{ $referralLink }}" class="rounded-xl bg-[var(--brand-primary)] px-4 py-3 text-sm font-bold text-white">Copy link</button></div><span data-copy-feedback class="mt-2 hidden text-xs font-semibold text-emerald-600">Referral link copied</span></section>
    <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Entrepreneur</th><th class="px-5 py-3">Contact</th><th class="px-5 py-3">Code</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Joined</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($referrals as $referral)<tr><td class="px-5 py-4 font-semibold">{{ $referral->user?->name ?: '—' }}</td><td class="px-5 py-4">{{ $referral->user?->phone ?: $referral->user?->email ?: '—' }}</td><td class="px-5 py-4 font-mono text-xs">{{ $referral->entrepreneur_code }}</td><td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs capitalize">{{ $referral->status }}</span></td><td class="px-5 py-4 text-slate-500">{{ $referral->created_at?->format('d M Y') }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No entrepreneurs have joined through your link yet.</td></tr>@endforelse</tbody></table></section>
    <div class="mt-6">{{ $referrals->links() }}</div>
</main>
@endsection
