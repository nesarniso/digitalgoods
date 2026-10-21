@extends('layouts.entrepreneur')

@section('title', 'Profile & Security')
@section('header_title', 'Profile & Security')
@section('content')
<main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-10">
    <div><p class="text-xs font-bold uppercase tracking-widest text-[var(--brand-primary)]">Account settings</p><h1 class="mt-2 text-3xl font-black">Profile & Security</h1><p class="mt-2 text-slate-600">Keep your contact and delivery-area information up to date.</p></div>
    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('entrepreneur.profile.update') }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            @csrf @method('PUT')<h2 class="text-xl font-bold">Profile details</h2>
            <div class="mt-5 space-y-3"><input name="name" required value="{{ old('name', $user->name) }}" placeholder="Full name" class="w-full rounded-xl border border-slate-300 p-3"><input name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="Email" class="w-full rounded-xl border border-slate-300 p-3"><input name="phone" required value="{{ old('phone', $user->phone) }}" placeholder="Mobile" class="w-full rounded-xl border border-slate-300 p-3"><textarea name="address" required placeholder="Full address" class="w-full rounded-xl border border-slate-300 p-3">{{ old('address', $profile?->address) }}</textarea><div class="grid gap-3 sm:grid-cols-2"><input name="division" value="{{ old('division', $profile?->division) }}" placeholder="Division" class="rounded-xl border border-slate-300 p-3"><input name="district" value="{{ old('district', $profile?->district) }}" placeholder="District" class="rounded-xl border border-slate-300 p-3"><input name="upazila" value="{{ old('upazila', $profile?->upazila) }}" placeholder="Upazila" class="rounded-xl border border-slate-300 p-3"><input name="union_or_ward" value="{{ old('union_or_ward', $profile?->union_or_ward) }}" placeholder="Union / Ward" class="rounded-xl border border-slate-300 p-3"></div></div>
            <button class="mt-5 rounded-xl bg-[var(--brand-primary)] px-5 py-3 font-bold text-white">Save profile</button>
        </form>
        <form method="POST" action="{{ route('entrepreneur.password.update') }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            @csrf @method('PUT')<h2 class="text-xl font-bold">Change password</h2><div class="mt-5 space-y-3"><input name="current_password" type="password" required placeholder="Current password" class="w-full rounded-xl border border-slate-300 p-3"><input name="password" type="password" minlength="8" required placeholder="New password" class="w-full rounded-xl border border-slate-300 p-3"><input name="password_confirmation" type="password" required placeholder="Confirm new password" class="w-full rounded-xl border border-slate-300 p-3"></div><button class="mt-5 rounded-xl border border-slate-300 px-5 py-3 font-bold">Update password</button>
        </form>
    </div>
</main>
@endsection
