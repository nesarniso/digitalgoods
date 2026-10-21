@extends('layouts.admin')
@section('title', 'Contact messages')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-10 lg:px-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><h1 class="text-3xl font-black">Contact messages</h1><p class="mt-2 text-slate-500">Messages submitted from the public Contact Us form.</p></div>
        <div class="rounded-xl bg-indigo-50 px-4 py-3 text-sm font-bold text-indigo-700">{{ $messages->total() }} total</div>
    </div>
    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="divide-y divide-slate-100">
            @forelse($messages as $message)
                <a href="{{ route('admin.contact-messages.show', $message) }}" class="flex flex-wrap items-center justify-between gap-4 px-6 py-5 transition hover:bg-slate-50">
                    <div class="min-w-0"><div class="flex items-center gap-2"><span class="font-bold {{ $message->read_at ? 'text-slate-700' : 'text-slate-950' }}">{{ $message->name }}</span>@unless($message->read_at)<span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold uppercase text-indigo-700">New</span>@endunless</div><p class="mt-1 truncate text-sm text-slate-500">{{ $message->topic }} · {{ $message->email }}</p></div>
                    <time class="text-xs text-slate-400">{{ $message->created_at->format('d M Y, h:i A') }}</time>
                </a>
            @empty
                <p class="px-6 py-16 text-center text-slate-500">No contact messages yet.</p>
            @endforelse
        </div>
    </div>
    <div class="mt-6">{{ $messages->links() }}</div>
</section>
@endsection
