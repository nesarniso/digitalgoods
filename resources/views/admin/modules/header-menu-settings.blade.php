@extends('layouts.admin')

@section('title', 'Header menu')

@section('content')
<section class="mx-auto max-w-5xl px-6 py-10 lg:px-10">
    <h1 class="text-3xl font-black">Header menu</h1>
    <p class="mt-2 text-slate-500">Change menu names, URLs and visibility for the storefront header.</p>
    @if(session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.settings.header-menu.save') }}" class="mt-8 space-y-4">
        @csrf
        @foreach($headerMenus as $index => $menu)
            <div class="grid gap-3 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:grid-cols-[140px_1fr_1fr_auto] sm:items-end">
                <input type="hidden" name="menus[{{ $index }}][key]" value="{{ $menu['key'] }}">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $menu['key'] }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-700">Menu {{ $index + 1 }}</p>
                </div>
                <label class="text-sm font-semibold">Menu name
                    <input name="menus[{{ $index }}][label]" value="{{ old("menus.{$index}.label", $menu['label']) }}" required maxlength="50" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal">
                </label>
                <label class="text-sm font-semibold">URL
                    <input name="menus[{{ $index }}][url]" value="{{ old("menus.{$index}.url", $menu['url']) }}" required maxlength="255" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal">
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">
                    <input type="checkbox" name="menus[{{ $index }}][enabled]" value="1" @checked($menu['enabled']) class="h-4 w-4 rounded text-indigo-600">
                    Show
                </label>
            </div>
        @endforeach
        <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-500">Save header menu</button>
    </form>
</section>
@endsection
