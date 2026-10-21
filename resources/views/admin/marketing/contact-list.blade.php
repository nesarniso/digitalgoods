@extends('layouts.admin')
@section('title', $contactList->name)
@section('content')
<section class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
    <a href="{{ route('admin.marketing.contacts') }}" class="text-sm font-bold text-indigo-600">← Back to contact lists</a>
    <div class="mt-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">Contact list</p><h1 class="mt-2 text-3xl font-black text-slate-900">{{ $contactList->name }}</h1><p class="mt-2 text-slate-500">{{ number_format($contactList->contact_count) }} contacts in this audience.</p></div><a href="{{ route('admin.marketing.campaigns') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-extrabold text-white">Use in campaign</a></div>
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-3">#</th><th class="px-6 py-3">Name</th><th class="px-6 py-3">Email</th><th class="px-6 py-3">Imported</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($contacts as $contact)<tr><td class="px-6 py-4 text-slate-400">{{ $contacts->firstItem() + $loop->index }}</td><td class="px-6 py-4 font-semibold">{{ $contact->name ?: '—' }}</td><td class="px-6 py-4 text-slate-600">{{ $contact->email }}</td><td class="px-6 py-4 text-slate-500">{{ $contact->created_at?->format('d M Y') }}</td></tr>@empty<tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">This list has no contacts.</td></tr>@endforelse</tbody></table></div></div>
    <div class="mt-6">{{ $contacts->links() }}</div>
</section>
@endsection
