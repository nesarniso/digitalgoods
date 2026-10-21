@extends('layouts.admin')
@section('title', 'Marketing contacts')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">Marketing</p><h1 class="mt-2 text-3xl font-black text-slate-900">Contact lists</h1><p class="mt-2 text-slate-500">Each imported audience is stored separately in batches of up to 1,000 contacts.</p></div>
        <a href="{{ route('admin.marketing.campaigns') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-extrabold text-white">Create campaign</a>
    </div>
    @if(session('success'))<div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Contact lists</p><p class="mt-2 text-3xl font-black text-slate-900">{{ $lists->count() }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Imported contacts</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($totalContacts) }}</p></div>
        <div class="rounded-2xl bg-indigo-600 p-5 text-white shadow-lg shadow-indigo-600/20"><p class="text-xs font-bold uppercase tracking-wide text-indigo-200">Reusable audience</p><p class="mt-2 text-sm font-semibold">Choose any list when creating a campaign.</p></div>
    </div>
    <section class="mt-8 rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
            <div><p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-300">Bulk importer</p><h2 class="mt-2 text-2xl font-black">Upload subscriber CSV</h2><p class="mt-2 max-w-xl text-sm leading-6 text-slate-300">The CSV is split into Contact 1, Contact 2, Contact 3 lists with 1,000 contacts each.</p></div>
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.marketing.contact-lists.import') }}" class="w-full rounded-2xl bg-white/10 p-4 lg:max-w-md">@csrf<input name="list_name" required placeholder="September subscribers" class="w-full rounded-xl border-0 px-4 py-3 text-sm text-slate-900"><input type="file" name="contact_csv" required accept=".csv,text/csv" class="mt-3 block w-full rounded-xl bg-white px-3 py-3 text-xs text-slate-700"><button class="mt-3 w-full rounded-xl bg-indigo-500 px-4 py-3 text-sm font-extrabold">Upload and split contacts</button></form>
        </div>
    </section>
    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($lists as $list)
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-3"><div><p class="text-lg font-extrabold text-slate-900">{{ $list->name }}</p><p class="mt-1 text-xs text-slate-400">Imported {{ $list->created_at?->format('d M Y') }}</p></div><span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ number_format($list->contact_count) }}</span></div>
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4"><a href="{{ route('admin.marketing.contacts.list', $list) }}" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-extrabold text-white">View contacts</a><form method="POST" action="{{ route('admin.marketing.contacts.delete', $list) }}" onsubmit="return confirm('Delete this entire contact list?')">@csrf @method('DELETE')<button class="text-xs font-bold text-red-600">Delete list</button></form></div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl bg-white px-6 py-16 text-center text-slate-500 shadow-sm ring-1 ring-slate-200">No contact lists imported yet.</div>
        @endforelse
    </div>
</section>
@endsection
