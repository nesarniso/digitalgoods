@extends('layouts.admin')
@section('title', 'Email templates')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-800 px-7 py-8 text-white shadow-xl shadow-indigo-950/15 sm:px-10">
        <div class="absolute -right-14 -top-20 h-56 w-56 rounded-full bg-indigo-400/20 blur-3xl"></div>
        <div class="relative flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-indigo-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Communication center
                </div>
                <h1 class="text-3xl font-black tracking-tight sm:text-4xl">Email templates</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-indigo-100/80">Create polished, consistent messages for every customer touchpoint from one place.</p>
            </div>
            <a href="{{ route('admin.email.templates.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-indigo-800 shadow-lg transition hover:bg-indigo-50">
                <span class="text-lg leading-none">+</span> New template
            </a>
        </div>
    </div>
    @if(session('success'))<div class="mt-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-white">✓</span>{{ session('success') }}</div>@endif
    <div class="mt-8 grid gap-6">
        @forelse($templates as $template)
            <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                <form method="POST" action="{{ route('admin.email.templates.update', $template) }}" class="p-6 sm:p-8">
                    @csrf @method('PUT')
                    <input type="hidden" name="key" value="{{ $template->key }}">
                    <input type="hidden" name="name" value="{{ $template->name }}">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                        <div>
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5v10.5H3.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7.5 7.5 5.25 7.5-5.25"/></svg>
                                </div>
                                <div><h2 class="text-xl font-extrabold text-slate-900">{{ $template->name }}</h2><code class="text-xs text-slate-400">{{ $template->key }}</code></div>
                            </div>
                            <p class="mt-3 text-sm text-slate-500">Edit this transactional message and send a live test before publishing.</p>
                        </div>
                        <details class="group relative">
                            <summary class="cursor-pointer list-none rounded-full border border-slate-200 px-4 py-2 text-center text-xs font-bold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700">Preview</summary>
                            <div class="absolute z-10 mt-2 max-w-lg rounded-2xl border border-slate-200 bg-white p-5 text-sm shadow-2xl"><p class="font-bold text-slate-900">{{ $template->subject }}</p><div class="mt-3 whitespace-pre-wrap text-slate-600">{{ $template->body }}</div></div>
                        </details>
                    </div>
                    <label class="mt-7 block text-xs font-extrabold uppercase tracking-wide text-slate-500">Subject<input name="subject" required value="{{ $template->subject }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"></label>
                    <label class="mt-5 block text-xs font-extrabold uppercase tracking-wide text-slate-500">HTML body<textarea name="body" required rows="9" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-xs leading-5 text-slate-700 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100">{{ $template->body }}</textarea></label>
                    <div class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-5 lg:flex-row lg:items-center lg:justify-between">
                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700"><input type="checkbox" name="is_active" value="1" @checked($template->is_active) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Enable automatic email</label>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">Save template</button>
                            <a href="{{ route('admin.email.templates.edit', $template) }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-extrabold text-slate-700">Edit page</a>
                            <button form="delete-template-{{ $template->id }}" onclick="return confirm('Delete this email template?')" class="rounded-xl border border-red-200 px-5 py-3 text-sm font-extrabold text-red-600">Delete</button>
                            <div class="flex gap-2">
                                <input type="email" name="recipient" form="test-template-{{ $template->id }}" required placeholder="test@example.com" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-xs outline-none focus:border-indigo-500 sm:w-48">
                                <button form="test-template-{{ $template->id }}" class="whitespace-nowrap rounded-xl border border-indigo-200 px-4 py-3 text-sm font-extrabold text-indigo-700 transition hover:bg-indigo-50">Test send</button>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="test-template-{{ $template->id }}" method="POST" action="{{ route('admin.email.templates.test', $template) }}" class="hidden">@csrf</form>
                <form id="delete-template-{{ $template->id }}" method="POST" action="{{ route('admin.email.templates.destroy', $template) }}" class="hidden">@csrf @method('DELETE')</form>
            </article>
        @empty
            <div class="col-span-full rounded-2xl bg-white px-6 py-16 text-center text-slate-500 shadow-sm ring-1 ring-slate-200">No email templates yet.</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $templates->links() }}</div>
</section>
@endsection
