@extends('layouts.admin')

@section('title', 'Entrepreneurs')

@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    @if(session('created_credentials'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900">
            <p class="font-bold">Entrepreneur account created and activated.</p>
            <p class="mt-2 text-sm">Share these credentials securely. The password will not be shown again.</p>
            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-4">
                <span><strong>ID:</strong> {{ session('created_credentials.login') }}</span>
                <span><strong>Password:</strong> {{ session('created_credentials.password') }}</span>
                <span><strong>Code:</strong> {{ session('created_credentials.entrepreneur_code') }}</span>
                <span><strong>Status:</strong> Active</span>
            </div>
        </div>
    @endif
    @if(session('success'))
        <div class="mb-6 rounded-xl bg-indigo-50 p-4 text-sm font-semibold text-indigo-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">eflix platform</p>
            <h1 class="mt-2 text-3xl font-black">Entrepreneurs</h1>
        </div>
        <form class="flex gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Search name, phone or email" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach(['pending', 'active', 'rejected', 'suspended'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
    </div>
    <details class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <summary class="cursor-pointer font-bold text-indigo-700">+ Add entrepreneur from admin</summary>
        <form method="POST" action="{{ route('admin.entrepreneurs.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            <input name="name" value="{{ old('name') }}" required placeholder="Full name" class="rounded-xl border border-slate-300 p-3">
            <input name="phone" value="{{ old('phone') }}" required placeholder="Mobile / login ID" class="rounded-xl border border-slate-300 p-3">
            <input name="email" value="{{ old('email') }}" type="email" placeholder="Email (optional)" class="rounded-xl border border-slate-300 p-3">
            <input name="address" value="{{ old('address') }}" required placeholder="Address" class="rounded-xl border border-slate-300 p-3">
            <input name="password" required type="password" placeholder="Login password (8+ characters)" class="rounded-xl border border-slate-300 p-3">
            <input name="password_confirmation" required type="password" placeholder="Confirm password" class="rounded-xl border border-slate-300 p-3">
            <button class="rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white sm:col-span-2">Create active entrepreneur</button>
        </form>
    </details>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500"><tr><th class="px-6 py-3">Entrepreneur</th><th class="px-6 py-3">Code</th><th class="px-6 py-3">Area</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Action</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($entrepreneurs as $entrepreneur)
                <tr>
                    <td class="px-6 py-4"><a href="{{ route('admin.entrepreneurs.show', $entrepreneur) }}" class="font-semibold text-indigo-700 hover:underline">{{ $entrepreneur->user?->name }}</a><p class="text-slate-500">{{ $entrepreneur->user?->phone ?: $entrepreneur->user?->email }}</p><div class="mt-2 flex gap-3 text-xs"><a href="{{ route('admin.entrepreneurs.show', $entrepreneur) }}" class="font-semibold text-indigo-700">View</a><form method="POST" action="{{ route('admin.entrepreneurs.destroy', $entrepreneur) }}" class="inline">@csrf @method('DELETE')<button type="submit" onclick="return confirm('Delete this entrepreneur account permanently?')" class="font-semibold text-red-600">Delete</button></form></div></td>
                    <td class="px-6 py-4 font-mono text-xs">{{ $entrepreneur->entrepreneur_code }}</td>
                    <td class="px-6 py-4">{{ $entrepreneur->district ?: $entrepreneur->division ?: '—' }}</td>
                    <td class="px-6 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold">{{ ucfirst($entrepreneur->status) }}</span></td>
                    <td class="px-6 py-4">
                        @if($entrepreneur->status === 'pending')
                            <form method="POST" action="{{ route('admin.entrepreneurs.status', $entrepreneur) }}" class="inline">@csrf @method('PUT')<input type="hidden" name="status" value="active"><button class="font-semibold text-emerald-600">Approve</button></form>
                        @elseif($entrepreneur->status === 'active')
                            <form method="POST" action="{{ route('admin.entrepreneurs.status', $entrepreneur) }}" class="inline">@csrf @method('PUT')<input type="hidden" name="status" value="suspended"><button class="font-semibold text-red-600">Suspend</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.entrepreneurs.status', $entrepreneur) }}" class="inline">@csrf @method('PUT')<input type="hidden" name="status" value="active"><button class="font-semibold text-indigo-600">Activate</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-10 text-center text-slate-500">No entrepreneurs found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $entrepreneurs->links() }}</div>
</section>
@endsection
