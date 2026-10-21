@extends('layouts.admin')
@section('title', 'Support tickets')
@section('content')
<section class="mx-auto max-w-6xl px-6 py-10 lg:px-10">
    <div><h1 class="text-3xl font-black">Support tickets</h1><p class="mt-2 text-slate-500">Customer requests submitted from the account Help & Support form.</p></div>
    @if(session('success'))<div class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="divide-y divide-slate-100">
            @forelse($tickets as $ticket)
                <div class="px-6 py-5"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="font-bold">{{ $ticket->subject }}</p><p class="mt-1 text-sm text-slate-500">{{ $ticket->user?->name }} · {{ $ticket->user?->email }} · {{ $ticket->category }}</p></div><time class="text-xs text-slate-400">{{ $ticket->created_at->format('d M Y, h:i A') }}</time></div><p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700">{!! nl2br(e($ticket->message)) !!}</p><form method="POST" action="{{ route('admin.support-tickets.update', $ticket) }}" class="mt-4 flex flex-wrap items-center gap-3">@csrf @method('PUT')<label for="ticket-status-{{ $ticket->id }}" class="text-xs font-bold uppercase tracking-wider text-slate-500">Status</label><select id="ticket-status-{{ $ticket->id }}" name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">@foreach(['open','in_progress','resolved','closed'] as $status)<option value="{{ $status }}" @selected($ticket->status === $status)>{{ Str::headline($status) }}</option>@endforeach</select><button class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white">Update</button><a href="mailto:{{ $ticket->user?->email }}" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700">Reply by email</a></form></div>
            @empty
                <p class="px-6 py-16 text-center text-slate-500">No support tickets yet.</p>
            @endforelse
        </div>
    </div>
    <div class="mt-6">{{ $tickets->links() }}</div>
</section>
@endsection
