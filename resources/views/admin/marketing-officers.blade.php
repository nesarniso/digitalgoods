@extends('layouts.admin')

@section('title', 'Marketing Officers')

@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Management</p><h1 class="mt-2 text-3xl font-black">Marketing Officers</h1><p class="mt-2 text-slate-500">Manage field representatives, assigned areas, and entrepreneur registrations.</p></div>
        <form method="GET" class="flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Search officers..." class="rounded-xl border-slate-200 text-sm"><button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Search</button></form>
    </div>
    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        @foreach([['Total officers', $totalCount], ['Active officers', $activeCount], ['Referred entrepreneurs', $entrepreneurCount]] as [$label, $value])
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-black">{{ $value }}</p></div>
        @endforeach
    </div>
    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_340px]">
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Officer</th><th class="px-5 py-3">Area</th><th class="px-5 py-3">Entrepreneurs</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Action</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($officers as $officer)
                <tr><td class="px-5 py-4"><p class="font-bold">{{ $officer->name }}</p><p class="text-xs text-slate-500">{{ $officer->email }}</p><p class="text-xs text-slate-400">{{ $officer->employeeProfile?->employee_id }}</p></td><td class="px-5 py-4">{{ $officer->employeeProfile?->area ?: '—' }}</td><td class="px-5 py-4 font-semibold">{{ $officer->referred_entrepreneurs_count }}</td><td class="px-5 py-4">{{ $officer->employeeProfile?->is_active ? 'Active' : 'Inactive' }}</td><td class="px-5 py-4"><form method="POST" action="{{ route('admin.marketing-officers.update', $officer->employeeProfile) }}" class="flex gap-2">@csrf @method('PUT')<input type="hidden" name="area" value="{{ $officer->employeeProfile?->area }}"><input type="hidden" name="is_active" value="{{ $officer->employeeProfile?->is_active ? 0 : 1 }}"><button class="text-xs font-bold text-indigo-700">{{ $officer->employeeProfile?->is_active ? 'Deactivate' : 'Activate' }}</button></form></td></tr>
            @empty <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No marketing officers yet.</td></tr>@endforelse
            </tbody></table>
            <div class="p-5">{{ $officers->links() }}</div>
        </div>
        <form method="POST" action="{{ route('admin.marketing-officers.store') }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">@csrf<h2 class="text-lg font-black">Add officer</h2><div class="mt-4 space-y-3"><input name="name" required placeholder="Full name" class="w-full rounded-xl border-slate-200 text-sm"><input name="email" type="email" required placeholder="Email" class="w-full rounded-xl border-slate-200 text-sm"><input name="phone" placeholder="Phone" class="w-full rounded-xl border-slate-200 text-sm"><input name="area" placeholder="Assigned area" class="w-full rounded-xl border-slate-200 text-sm"><input name="password" type="password" required placeholder="Password" class="w-full rounded-xl border-slate-200 text-sm"><input name="password_confirmation" type="password" required placeholder="Confirm password" class="w-full rounded-xl border-slate-200 text-sm"><button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white">Create marketing officer</button></div></form>
    </div>
</section>
@endsection
