@extends('layouts.admin')

@section('title', 'Change password')

@section('content')
<section class="mx-auto max-w-3xl px-6 py-12">
    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
        <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Account security</p>
        <h1 class="mt-2 text-3xl font-black text-slate-950">Change admin password</h1>
        <p class="mt-2 text-sm text-slate-500">Use your current password to set a new password for this admin account.</p>

        @if(session('success'))
            <div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}" class="mt-8 space-y-5">
            @csrf
            @method('PUT')
            <label class="block text-sm font-semibold text-slate-700">
                Current password
                <input name="current_password" type="password" required autocomplete="current-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
            </label>
            <label class="block text-sm font-semibold text-slate-700">
                New password
                <input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
            </label>
            <label class="block text-sm font-semibold text-slate-700">
                Confirm new password
                <input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
            </label>
            <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700">Update password</button>
        </form>
    </div>
</section>
@endsection
