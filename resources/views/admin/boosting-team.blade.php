@extends('layouts.admin')

@section('title', 'Boosting Team')

@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Management</p><h1 class="mt-2 text-3xl font-black">Boosting Team</h1><p class="mt-2 text-slate-500">Manage campaign and advertisement team members.</p></div>
        <form method="GET" class="flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Search members..." class="rounded-xl border-slate-200 text-sm"><button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Search</button></form>
    </div>
    <div class="mt-8 grid gap-4 sm:grid-cols-2"><div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Total members</p><p class="mt-2 text-3xl font-black">{{ $totalCount }}</p></div><div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">Active members</p><p class="mt-2 text-3xl font-black">{{ $activeCount }}</p></div></div>
    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_340px]">
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Member</th><th class="px-5 py-3">Area</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Action</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($members as $member)<tr><td class="px-5 py-4"><p class="font-bold">{{ $member->name }}</p><p class="text-xs text-slate-500">{{ $member->email }}</p><p class="text-xs text-slate-400">{{ $member->employeeProfile?->employee_id }}</p></td><td class="px-5 py-4">{{ $member->employeeProfile?->area ?: '—' }}</td><td class="px-5 py-4">{{ $member->employeeProfile?->is_active ? 'Active' : 'Inactive' }}</td><td class="px-5 py-4"><form method="POST" action="{{ route('admin.boosting-team.update', $member->employeeProfile) }}">@csrf @method('PUT')<input type="hidden" name="area" value="{{ $member->employeeProfile?->area }}"><input type="hidden" name="is_active" value="{{ $member->employeeProfile?->is_active ? 0 : 1 }}"><button class="text-xs font-bold text-indigo-700">{{ $member->employeeProfile?->is_active ? 'Deactivate' : 'Activate' }}</button></form></td></tr>@empty<tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">No boosting team members yet.</td></tr>@endforelse
        </tbody></table><div class="p-5">{{ $members->links() }}</div></div>
        <form method="POST" action="{{ route('admin.boosting-team.store') }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf<h2 class="text-lg font-black">Add member</h2><div class="mt-4 space-y-3"><input name="name" required placeholder="Full name" class="w-full rounded-xl border-slate-200 text-sm"><input name="email" type="email" required placeholder="Email" class="w-full rounded-xl border-slate-200 text-sm"><input name="phone" placeholder="Phone" class="w-full rounded-xl border-slate-200 text-sm"><input name="area" placeholder="Assigned area" class="w-full rounded-xl border-slate-200 text-sm"><input name="password" type="password" required placeholder="Password" class="w-full rounded-xl border-slate-200 text-sm"><input name="password_confirmation" type="password" required placeholder="Confirm password" class="w-full rounded-xl border-slate-200 text-sm"><button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white">Create team member</button></div></form>
    </div>
</section>
@endsection
